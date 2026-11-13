<?php

namespace Modules\User\Application\Role;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
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
        $role = DB::transaction(function () use ($name, $permissions) {
            $role = $this->roleRepository->store($name);
            $this->roleRepository->syncPermissions($role, $permissions);

            return $role;
        });
        if ($role) {
            $this->flashMessenger->success();
        }

        return $role;
    }

    public function update(int $id, string $name, array $permissions): ?Role
    {
        $role = DB::transaction(function () use ($id, $name, $permissions) {
            $role = $this->roleRepository->findById($id);
            $this->assertNotSuperAdmin($role, 'The super-admin role cannot be modified.');
            $role = $this->roleRepository->update($role, $name);
            if (! $role) {
                throw new \RuntimeException('The role update did not persist.');
            }
            $this->roleRepository->syncPermissions($role, $permissions);

            return $role;
        });
        if ($role) {
            $this->flashMessenger->success();
        }

        return $role;
    }

    public function delete(int $id): bool
    {
        $deleted = DB::transaction(function () use ($id): bool {
            $role = $this->roleRepository->findById($id);
            $this->assertNotSuperAdmin($role, 'The super-admin role cannot be deleted.');

            return (bool) $this->roleRepository->delete($id);
        });
        if ($deleted) {
            $this->flashMessenger->success();
        }

        return $deleted;
    }

    public function assignUsersToRole(int $id, array $userIds): bool
    {
        $assigned = DB::transaction(function () use ($id, $userIds): bool {
            $this->assertNotSuperAdmin($this->roleRepository->findById($id), 'Users cannot be assigned through this workflow to the super-admin role.');

            return $this->roleRepository->assignUsersToRole($id, $userIds);
        });
        if ($assigned) {
            $this->flashMessenger->success();
        }

        return $assigned;
    }

    public function removeUsersFromRole(int $id, array $userIds): bool
    {
        $removed = DB::transaction(function () use ($id, $userIds): bool {
            $this->assertNotSuperAdmin($this->roleRepository->findById($id), 'Users cannot be removed through this workflow from the super-admin role.');

            return $this->roleRepository->removeUsersFromRole($id, $userIds);
        });
        if ($removed) {
            $this->flashMessenger->success();
        }

        return $removed;
    }

    public function removeUserFromRole(int $id, int $userId): bool
    {
        $removed = DB::transaction(function () use ($id, $userId): bool {
            $this->assertNotSuperAdmin($this->roleRepository->findById($id), 'Users cannot be removed through this workflow from the super-admin role.');

            return $this->roleRepository->removeUserFromRole($id, $userId);
        });
        if ($removed) {
            $this->flashMessenger->success();
        }

        return $removed;
    }

    public function availableAdminsWithoutRole(int $roleId): Collection
    {
        return User::type('admin')
            ->whereDoesntHave('roles', fn ($query) => $query->where('id', $roleId))
            ->get();
    }

    private function assertNotSuperAdmin(Role $role, string $message): void
    {
        if ($role->name === 'Admin') {
            throw new AuthorizationException($message);
        }
    }
}
