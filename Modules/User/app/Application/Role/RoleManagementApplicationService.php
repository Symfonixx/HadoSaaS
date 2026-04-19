<?php

namespace Modules\User\Application\Role;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\User\Repositories\Role\RoleRepository;
use Spatie\Permission\Models\Role;

class RoleManagementApplicationService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function all(): Collection
    {
        return $this->roleRepository->all();
    }

    public function permissions(): Collection
    {
        return $this->roleRepository->permissions();
    }

    public function findById(int $id): Role
    {
        return $this->roleRepository->findById($id);
    }

    public function store(string $name, array $permissions): ?Role
    {
        $role = $this->roleRepository->store($name, $permissions);
        $this->flashMessenger->success();

        return $role;
    }

    public function update(int $id, string $name, array $permissions): ?Role
    {
        $role = $this->roleRepository->update($id, $name, $permissions);
        $this->flashMessenger->success();

        return $role;
    }

    public function delete(int $id): bool
    {
        $deleted = (bool) $this->roleRepository->delete($id);
        $this->flashMessenger->success();

        return $deleted;
    }

    public function assignUsersToRole(int $id, array $userIds): bool
    {
        $assigned = $this->roleRepository->assignUsersToRole($id, $userIds);
        $this->flashMessenger->success();

        return $assigned;
    }

    public function removeUsersFromRole(int $id, array $userIds): bool
    {
        $removed = $this->roleRepository->removeUsersFromRole($id, $userIds);
        $this->flashMessenger->success();

        return $removed;
    }

    public function removeUserFromRole(int $id, int $userId): bool
    {
        $removed = $this->roleRepository->removeUserFromRole($id, $userId);
        $this->flashMessenger->success();

        return $removed;
    }

    public function availableAdminsWithoutRole(int $roleId): Collection
    {
        return User::type('admin')
            ->whereDoesntHave('roles', fn ($query) => $query->where('id', $roleId))
            ->get();
    }
}
