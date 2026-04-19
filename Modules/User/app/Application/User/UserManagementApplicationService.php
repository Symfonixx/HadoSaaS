<?php

namespace Modules\User\Application\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\User\app\Data\UserData;
use Modules\User\app\Repositories\User\UserRepository;

class UserManagementApplicationService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FlashMessengerInterface $flashMessenger
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
        $this->flashMessenger->success();

        return $user;
    }

    public function update(UserData $userData, User $user): ?User
    {
        $updated = $this->userRepository->update($userData, $user);
        $this->flashMessenger->success();

        return $updated;
    }

    public function delete(User $user): bool
    {
        $deleted = $this->userRepository->delete($user);
        $this->flashMessenger->success();

        return $deleted;
    }
}
