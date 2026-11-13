<?php

namespace Modules\Cms\Application\Page;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Application\Page\Commands\UpsertPageCommand;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Cms\Models\Page;
use Modules\Cms\Repositories\Page\PageRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Application\Files\FileCleanupResult;
use Modules\Core\Application\Files\PendingFileDeletionService;

class PageApplicationService
{
    public function __construct(
        private readonly PageRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger,
        private readonly PendingFileDeletionService $fileDeletions,
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertPageCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'pages',
            translatableFields: (new Page)->translatable,
            imageFields: ['image', 'meta_image'],
            updateTranslations: true
        );

        if (! $this->repository->store($payload)) {
            throw new \RuntimeException('The page could not be created.');
        }
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(Page $page, UpsertPageCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'pages',
            translatableFields: $page->translatable,
            imageFields: ['image', 'meta_image'],
            existingMedia: [
                'image' => $page->image,
                'meta_image' => $page->meta_image,
            ],
            entity: $page,
            updateTranslations: $command->updateTranslations
        );

        $oldPaths = [];
        $newPaths = [];
        foreach (['image', 'meta_image'] as $field) {
            $old = $page->{$field};
            $new = $payload[$field] ?? null;
            if (is_string($old) && $old !== '' && $old !== $new) {
                $oldPaths[] = $old;
            }
            if (($command->payload[$field] ?? null) instanceof UploadedFile) {
                if (is_string($new) && $new !== '' && $new !== $old) {
                    $newPaths[] = $new;
                }
            }
        }

        try {
            DB::transaction(function () use ($payload, $page, $command, $oldPaths): void {
                if (! $this->repository->update($payload, $page, $command->updateTranslations)) {
                    throw new \RuntimeException('The page update did not persist.');
                }
                $this->fileDeletions->queueUnreferenced($oldPaths);
            });
        } catch (\Throwable $exception) {
            $this->removeUncommittedUploads($newPaths);
            throw $exception;
        }
        $this->clearCache();
        $cleanup = $oldPaths === [] ? FileCleanupResult::Complete : $this->fileDeletions->processAndReport($oldPaths);
        if ($cleanup === FileCleanupResult::Complete) {
            $this->flashMessenger->success();
        } else {
            $this->flashMessenger->error('Page was updated; replaced files remain queued for cleanup.');
        }
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): void
    {
        $ids = $this->fileDeletions->normalizeIds($ids);
        $paths = DB::transaction(function () use ($ids): array {
            $pages = $this->repository->findManyForDeletion($ids);
            if ($pages->count() !== count($ids)) {
                throw (new ModelNotFoundException)->setModel(Page::class, array_values(array_diff($ids, $pages->modelKeys())));
            }

            $paths = $pages->flatMap(fn (Page $page) => [$page->image, $page->meta_image])
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->unique()->values()->all();
            if ($this->repository->deleteMany($ids) !== count($ids)) {
                throw new \RuntimeException('The selected pages could not all be deleted.');
            }
            $this->fileDeletions->queueUnreferenced($paths);

            return $paths;
        }, 3);

        $this->clearCache();
        $cleanup = $this->fileDeletions->processAndReport($paths);
        if ($cleanup === FileCleanupResult::Complete) {
            $this->flashMessenger->success();
        } else {
            $this->flashMessenger->error('Page records were deleted; file cleanup is pending and will be retried.');
        }
    }

    private function clearCache(): void
    {
        cache()->forget('pages');
    }

    /** @param array<int, string> $paths */
    private function removeUncommittedUploads(array $paths): void
    {
        if ($paths === []) {
            return;
        }

        try {
            $this->fileDeletions->queueUnreferenced($paths);
            $this->fileDeletions->processAndReport($paths);
        } catch (\Throwable $cleanupException) {
            report($cleanupException);
        }
    }
}
