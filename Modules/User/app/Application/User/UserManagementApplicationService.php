<?php

namespace Modules\User\Application\User;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Files\FileCleanupResult;
use Modules\Core\Application\Files\PendingFileDeletionService;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\User\app\Data\UserData;
use Modules\User\app\Repositories\User\UserRepository;

class UserManagementApplicationService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FlashMessengerInterface $flashMessenger,
        private readonly PendingFileDeletionService $fileDeletions,
    ) {}

    public function paginate(string $type): LengthAwarePaginator
    {
        return $this->userRepository->all($type);
    }

    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function store(UserData $userData): ?User
    {
        $user = $this->userRepository->store($userData);
        if ($user) {
            $this->flashMessenger->success();
        }

        return $user;
    }

    public function update(UserData $userData, User $user): ?User
    {
        $updated = $this->userRepository->update($userData, $user);
        if ($updated) {
            $this->flashMessenger->success();
        }

        return $updated;
    }

    public function delete(User $user): bool
    {
        $deletion = DB::transaction(function () use ($user): array {
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            if ($lockedUser->hasRole('Admin')) {
                throw new AuthorizationException('The super-admin account cannot be deleted through this workflow.');
            }
            if ($lockedUser->type === 'admin' && User::query()->where('type', 'admin')->lockForUpdate()->get(['id'])->count() <= 1) {
                throw new AuthorizationException('The last administrator account cannot be deleted.');
            }

            $path = $lockedUser->img;
            if (! $this->userRepository->delete($lockedUser)) {
                return ['deleted' => false, 'path' => null];
            }
            if ($path) {
                $this->fileDeletions->queueUnreferenced([$path]);
            }

            return ['deleted' => true, 'path' => $path];
        });

        if (! $deletion['deleted']) {
            return false;
        }

        $path = $deletion['path'];
        $cleanup = $path ? $this->fileDeletions->processAndReport([$path]) : FileCleanupResult::Complete;
        if ($cleanup === FileCleanupResult::Complete) {
            $this->flashMessenger->success();
        } else {
            $this->flashMessenger->error('User was deleted; file cleanup is pending and will be retried.');
        }

        return true;
    }
}
