<?php

namespace Modules\Cms\Application\Page;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Application\Page\Commands\UpsertPageCommand;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Cms\Models\Page;
use Modules\Cms\Repositories\Page\PageRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class PageApplicationService
{
    public function __construct(
        private readonly PageRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
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

        $this->repository->store($payload);
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

        $this->repository->update($payload, $page, $command->updateTranslations);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): void
    {
        $this->repository->deleteMulti($ids);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    private function clearCache(): void
    {
        cache()->forget('pages');
    }
}
