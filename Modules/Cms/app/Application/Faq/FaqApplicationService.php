<?php

namespace Modules\Cms\Application\Faq;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Application\Faq\Commands\UpsertFaqCommand;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Cms\Models\Faq;
use Modules\Cms\Repositories\Faq\FaqRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class FaqApplicationService
{
    public function __construct(
        private readonly FaqRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertFaqCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'faqs',
            translatableFields: (new Faq)->translatable,
            imageFields: [],
            updateTranslations: true
        );

        $this->repository->store($payload);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(Faq $faq, UpsertFaqCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'faqs',
            translatableFields: $faq->translatable,
            imageFields: [],
            entity: $faq,
            updateTranslations: $command->updateTranslations
        );

        $this->repository->update($payload, $faq, $command->updateTranslations);
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
        cache()->forget('faqs');
    }
}
