<?php

namespace Modules\Cms\Application\Shared\Support;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\Translation\TranslatorInterface;
use Modules\Core\Traits\FileTrait;

class ContentPayloadBuilder
{
    use FileTrait;

    /**
     * Fields that hold a comma-separated keyword list and need to be normalised
     * before being persisted. They are still translatable.
     */
    private const KEYWORD_FIELDS = ['meta_keywords'];

    public function __construct(private readonly TranslatorInterface $translator) {}

    /**
     * Build a payload ready for persistence.
     *
     * @param  array<string, mixed>  $data            Raw request data.
     * @param  string                $uploadPath      Folder used to store uploaded media.
     * @param  array<int, string>    $translatableFields  List of attributes that should be translated.
     * @param  array<int, string>    $imageFields     File inputs that should be uploaded as images.
     * @param  array<string, ?string> $existingMedia  Existing media paths keyed by attribute name.
     * @return array<string, mixed>
     */
    public function build(
        array $data,
        string $uploadPath,
        array $translatableFields,
        array $imageFields = ['image'],
        array $existingMedia = [],
        ?Model $entity = null,
        bool $updateTranslations = true
    ): array {
        $locale = app()->getLocale();
        $payload = $data;

        foreach ($translatableFields as $field) {
            $rawValue = $data[$field] ?? null;
            $value = $this->normaliseFieldValue($field, $rawValue);

            $translations = $entity?->getTranslations($field) ?? [];
            $translations[$locale] = $value;

            if ($updateTranslations && $value !== '') {
                foreach ($this->translator->otherLanguages() as $language) {
                    try {
                        $translations[$language] = $this->translator->translate($language, $value);
                    } catch (Exception $exception) {
                        Log::error($exception->getMessage());
                    }
                }
            }

            $payload[$field] = $translations;
        }

        foreach ($imageFields as $field) {
            $payload[$field] = $this->resolveImagePath(
                $data[$field] ?? null,
                $uploadPath,
                (string) ($data['slug'] ?? uniqid('media_', true)),
                $existingMedia[$field] ?? null
            );
        }

        $payload['featured'] = (int) ($data['featured'] ?? false);

        if (isset($data['status'])) {
            $payload['status'] = $data['status'];
        }

        return $payload;
    }

    private function normaliseFieldValue(string $field, mixed $rawValue): string
    {
        if (in_array($field, self::KEYWORD_FIELDS, true)) {
            return $this->parseKeywords($rawValue);
        }

        return (string) ($rawValue ?? '');
    }

    private function resolveImagePath(UploadedFile|string|null $image, string $uploadPath, string $slug, ?string $existingImage): ?string
    {
        if (is_string($image) && trim($image) !== '') {
            return $image;
        }

        if (! $image) {
            return $existingImage;
        }

        return $this->upload($image, $uploadPath, $slug, $existingImage);
    }

    private function parseKeywords(null|string|array $keywordsInput): string
    {
        if (! $keywordsInput) {
            return '';
        }

        if (is_array($keywordsInput)) {
            return implode(', ', array_filter($keywordsInput));
        }

        $decoded = json_decode($keywordsInput, true);
        if (! is_array($decoded)) {
            return $keywordsInput;
        }

        return implode(', ', array_column($decoded, 'value'));
    }
}
