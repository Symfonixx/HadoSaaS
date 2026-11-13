<?php

namespace Modules\Cms\Application\Blog;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Application\Blog\Commands\UpsertBlogCommand;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Repositories\Blog\BlogRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Application\Files\FileCleanupResult;
use Modules\Core\Application\Files\PendingFileDeletionService;

class BlogApplicationService
{
    public function __construct(
        private readonly BlogRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger,
        private readonly PendingFileDeletionService $fileDeletions,
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertBlogCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'blogs',
            translatableFields: (new Blog)->translatable,
            imageFields: ['image', 'meta_image'],
            updateTranslations: true
        );

        if (! $this->repository->store($payload)) {
            throw new \RuntimeException('The blog could not be created.');
        }
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(Blog $blog, UpsertBlogCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'blogs',
            translatableFields: $blog->translatable,
            imageFields: ['image', 'meta_image'],
            existingMedia: [
                'image' => $blog->image,
                'meta_image' => $blog->meta_image,
            ],
            entity: $blog,
            updateTranslations: $command->updateTranslations
        );

        $oldPaths = [];
        $newPaths = [];
        foreach (['image', 'meta_image'] as $field) {
            $old = $blog->{$field};
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
            DB::transaction(function () use ($payload, $blog, $command, $oldPaths): void {
                if (! $this->repository->update($payload, $blog, $command->updateTranslations)) {
                    throw new \RuntimeException('The blog update did not persist.');
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
            $this->flashMessenger->error('Blog was updated; replaced files remain queued for cleanup.');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, BlogCategory>
     */
    public function categories()
    {
        return BlogCategory::query()->get();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): void
    {
        $ids = $this->fileDeletions->normalizeIds($ids);
        $paths = DB::transaction(function () use ($ids): array {
            $blogs = $this->repository->findManyForDeletion($ids);
            if ($blogs->count() !== count($ids)) {
                throw (new ModelNotFoundException)->setModel(Blog::class, array_values(array_diff($ids, $blogs->modelKeys())));
            }

            $paths = $blogs->flatMap(fn (Blog $blog) => [$blog->image, $blog->meta_image])
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->unique()->values()->all();
            if ($this->repository->deleteMany($ids) !== count($ids)) {
                throw new \RuntimeException('The selected blogs could not all be deleted.');
            }
            $this->fileDeletions->queueUnreferenced($paths);

            return $paths;
        }, 3);

        $this->clearCache();
        $cleanup = $this->fileDeletions->processAndReport($paths);
        if ($cleanup === FileCleanupResult::Complete) {
            $this->flashMessenger->success();
        } else {
            $this->flashMessenger->error('Blog records were deleted; file cleanup is pending and will be retried.');
        }
    }

    private function clearCache(): void
    {
        cache()->forget('blogs');
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
