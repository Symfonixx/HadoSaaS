<?php

namespace Modules\Base\Application\Settings;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Modules\Base\Repositories\Settings\SettingsRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Traits\FileTrait;

class SettingsApplicationService
{
    use FileTrait;

    public function __construct(
        private readonly SettingsRepository $settingsRepository,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function allKeyValue(): Collection
    {
        return $this->settingsRepository->allKeyValue();
    }

    /**
     * @param  array<string, UploadedFile>  $images
     * @param  array<string, mixed>  $data
     * @param  array<string, string|null>  $mediaPaths
     */
    public function update(array $images = [], array $data = [], array $mediaPaths = []): void
    {
        foreach ($images as $key => $file) {
            $oldFile = $this->settingsRepository->get($key);
            $path = $this->upload($file, 'settings', $key, $oldFile ?: null);
            $this->settingsRepository->set($key, $path);
        }

        foreach ($mediaPaths as $key => $path) {
            if (is_string($path) && trim($path) !== '') {
                $this->settingsRepository->set((string) $key, trim($path));
            }
        }

        foreach ($data as $key => $value) {
            $this->settingsRepository->set((string) $key, is_scalar($value) ? (string) $value : null);
        }

        cache()->forget('settings');
        $this->flashMessenger->success();
    }
}
