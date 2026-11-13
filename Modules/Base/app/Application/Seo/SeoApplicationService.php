<?php

namespace Modules\Base\Application\Seo;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Base\Repositories\Seo\SeoRepository;
use Modules\Base\Repositories\Settings\SettingsRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Contracts\Translation\TranslatorInterface;

class SeoApplicationService
{
    public function __construct(
        private readonly SeoRepository $seoRepository,
        private readonly FlashMessengerInterface $flashMessenger,
        private readonly TranslatorInterface $translator,
        private readonly SettingsRepository $settingsRepository,
    ) {}

    public function allKeyValue(): Collection
    {
        return $this->seoRepository->allKeyValue();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, bool $updateTranslations, ?string $robotsTxt = null): void
    {
        $data = array_filter(
            $data,
            static fn ($value) => $value !== null
        );

        // Validate all inputs first
        foreach ($data as $key => $value) {
            if (trim((string) $key) === '' || ! is_string($value)) {
                throw new \InvalidArgumentException('SEO values must be strings and have a key.');
            }
        }

        // Step 1: Fetch existing translations from the database in a single query (outside transaction)
        $keys = array_map(fn ($key) => (string) $key, array_keys($data));
        $existingTranslations = $this->seoRepository->translationsForBatch($keys);

        // Step 2: Perform all translation API calls completely outside any database transaction
        $translatedValues = [];
        if ($updateTranslations) {
            $otherLanguages = $this->translator->otherLanguages();
            foreach ($data as $key => $value) {
                $translatedValues[(string) $key] = [];
                foreach ($otherLanguages as $language) {
                    $translatedValues[(string) $key][$language] = $this->translator->translate($language, (string) $value);
                }
            }
        }

        // Step 3: Build the prepared translation arrays
        $prepared = [];
        foreach ($data as $key => $value) {
            $key = (string) $key;
            $translations = $existingTranslations[$key] ?? [];
            $translations[app()->getLocale()] = $value;
            if ($updateTranslations && isset($translatedValues[$key])) {
                $translations = array_merge($translations, $translatedValues[$key]);
            }
            $prepared[$key] = $translations;
        }

        // Step 4: Persist all data within a database transaction (no external API calls here)
        DB::transaction(function () use ($prepared, $robotsTxt): void {
            foreach ($prepared as $key => $translations) {
                if (! $this->seoRepository->setTranslations($key, $translations)) {
                    throw new \RuntimeException("SEO value [{$key}] was not persisted.");
                }
            }
            if ($robotsTxt !== null && ! $this->settingsRepository->set('robots_txt', $robotsTxt)) {
                throw new \RuntimeException('Robots text was not persisted.');
            }
        });

        // Step 5: Clear caches
        cache()->forget('seo_entries');
        if ($robotsTxt !== null) {
            $this->settingsRepository->resetCache();
            cache()->forget('settings');
        }

        $this->flashMessenger->success();
    }
}
