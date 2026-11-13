<?php

namespace Modules\Base\Application\Settings;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Files\FileCleanupResult;
use Modules\Core\Application\Files\PendingFileDeletionService;
use Modules\Base\Repositories\Settings\SettingsRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Traits\FileTrait;

class SettingsApplicationService
{
    use FileTrait;

    public function __construct(
        private readonly SettingsRepository $settingsRepository,
        private readonly FlashMessengerInterface $flashMessenger,
        private readonly PendingFileDeletionService $fileDeletions,
    ) {}

    public function allKeyValue(): Collection
    {
        return $this->settingsRepository->allKeyValue();
    }

    /**
     * @param  array<string, UploadedFile>  $images
     * @param  array<string, mixed>  $data
     * @param  array<string, string|null>  $mediaPaths
     * @throws \Throwable
     */
    public function update(array $images = [], array $data = [], array $mediaPaths = []): void
    {
        $data = array_filter(
            $data,
            static fn ($value) => $value !== null
        );
        foreach ($data as $key => $value) {
            if (! is_scalar($value) || trim((string) $key) === '') {
                throw new \InvalidArgumentException('Setting values must be scalar and have a key.');
            }
        }
        $mediaPaths = array_filter(
            $mediaPaths,
            static fn ($value) => $value !== null
        );
        foreach ($mediaPaths as $key => $path) {
            if (! is_string($path) || trim($path) === '' || trim((string) $key) === '') {
                throw new \InvalidArgumentException('Media setting paths must be non-empty strings.');
            }
        }

        $staged = [];
        $oldPaths = [];
        try {
            foreach ($images as $key => $file) {
                if (! $file instanceof UploadedFile || trim((string) $key) === '') {
                    throw new \InvalidArgumentException('Setting image uploads must be valid files with a key.');
                }
                $old = $this->settingsRepository->get((string) $key);
                $oldPaths[] = is_string($old) ? $old : null;
                $staged[(string) $key] = $this->upload($file, 'settings', (string) $key);
            }

            DB::transaction(function () use ($staged, $mediaPaths, $data, $oldPaths): void {
                foreach ($staged as $key => $path) {
                    $this->assertSaved($key, $path);
                }
                foreach ($mediaPaths as $key => $path) {
                    $this->assertSaved((string) $key, trim($path));
                }
                foreach ($data as $key => $value) {
                    $this->assertSaved((string) $key, (string) $value);
                }

                $this->fileDeletions->queueUnreferenced(array_filter($oldPaths));
            });
        } catch (\Throwable $exception) {
            $this->settingsRepository->resetCache();
            if ($staged !== []) {
                try {
                    $this->fileDeletions->queueUnreferenced(array_values($staged));
                    $this->fileDeletions->processAndReport(array_values($staged));
                } catch (\Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        $this->settingsRepository->resetCache();
        cache()->forget('settings');
        $oldPaths = array_values(array_filter($oldPaths));
        $cleanup = $oldPaths === []
            ? FileCleanupResult::Complete
            : $this->fileDeletions->processAndReport($oldPaths);
        if ($cleanup === FileCleanupResult::Complete) {
            $this->flashMessenger->success();
        } else {
            $this->flashMessenger->error('Settings were saved; replaced files remain queued for cleanup.');
        }
    }

    private function assertSaved(string $key, string $value): void
    {
        if (! $this->settingsRepository->set($key, $value)) {
            throw new \RuntimeException("Setting [{$key}] was not persisted.");
        }
    }
}
