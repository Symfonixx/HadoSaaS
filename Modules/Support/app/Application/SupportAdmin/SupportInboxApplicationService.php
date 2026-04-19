<?php

namespace Modules\Support\Application\SupportAdmin;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Support\Repositories\ContactForm\ContactFormRepository;
use Modules\Support\Repositories\Subscriber\SubscriberRepository;

class SupportInboxApplicationService
{
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository,
        private readonly ContactFormRepository $contactFormRepository,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginateSubscribers(): LengthAwarePaginator
    {
        return $this->subscriberRepository->paginate();
    }

    public function paginateContactForms(): LengthAwarePaginator
    {
        return $this->contactFormRepository->paginate();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteSubscribers(array $ids): void
    {
        $this->subscriberRepository->deleteMulti($ids);
        $this->flashMessenger->success();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteContactForms(array $ids): void
    {
        $this->contactFormRepository->deleteMulti($ids);
        $this->flashMessenger->success();
    }
}
