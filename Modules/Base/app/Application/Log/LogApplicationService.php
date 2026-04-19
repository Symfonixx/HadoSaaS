<?php

namespace Modules\Base\Application\Log;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Base\Models\LogDb;
use Modules\Base\Repositories\Log\LogRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class LogApplicationService
{
    public function __construct(
        private readonly LogRepository $logRepository,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->logRepository->paginate($filters);
    }

    public function find(int $id): ?LogDb
    {
        return $this->logRepository->find($id);
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): void
    {
        $this->logRepository->deleteMulti($ids);
        $this->flashMessenger->success();
    }
}
