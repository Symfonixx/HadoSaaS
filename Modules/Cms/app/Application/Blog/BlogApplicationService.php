<?php

namespace Modules\Cms\Application\Blog;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Application\Blog\Commands\UpsertBlogCommand;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Cms\Models\Blog;
use Modules\Cms\Models\BlogCategory;
use Modules\Cms\Repositories\Blog\BlogRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class BlogApplicationService
{
    public function __construct(
        private readonly BlogRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
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

        $this->repository->store($payload);
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

        if (! $this->repository->update($payload, $blog, $command->updateTranslations)) {
            throw new \RuntimeException('The blog update did not persist.');
        }
        $this->clearCache();
        $this->flashMessenger->success();
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
        if (! $this->repository->deleteMulti($ids)) {
            throw new \RuntimeException('The selected blogs could not all be deleted.');
        }
        $this->clearCache();
        $this->flashMessenger->success();
    }

    private function clearCache(): void
    {
        cache()->forget('blogs');
    }
}
