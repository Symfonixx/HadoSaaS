<?php

namespace Modules\User\Repositories\Role;

use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleModelRepository implements RoleRepository
{
    use ExceptionHandlerTrait;

    public function permissions(): Collection
    {
        return Permission::all();
    }

    public function all(): Collection
    {
        return Role::all();
    }

    public function store(string $name, array $permissions): ?Role
    {
        return $this->execute(function () use ($name, $permissions) {
            $role = Role::create(['name' => $name]);
            $role->syncPermissions($permissions);

            return $role;
        });
    }

    public function update(int $id, string $name, array $permissions): ?Role
    {
        return $this->execute(function () use ($id, $name, $permissions) {
            $role = $this->findById($id);
            $role->update(['name' => $name]);
            $role->syncPermissions($permissions);

            return $role;
        });
    }

    public function findById(int $id): Role
    {
        return Role::findOrFail($id);
    }

    public function delete(int $id): bool
    {
        return $this->execute(function () use ($id) {
            return $this->findById($id)->delete();
        });
    }

    public function assignUsersToRole(int $id, array $userIds): bool
    {
        return $this->execute(function () use ($id, $userIds) {
            $role = $this->findById($id);
            $users = User::whereIn('id', $userIds)->get();
            $role->users()->syncWithoutDetaching($users);

            return true;
        });
    }

    public function removeUsersFromRole(int $id, array $userIds): bool
    {
        return $this->execute(function () use ($id, $userIds) {
            $role = $this->findById($id);
            $users = User::whereIn('id', $userIds)->get();
            $role->users()->detach($users);

            return true;
        });
    }

    public function removeUserFromRole(int $id, int $userId): bool
    {
        return $this->execute(function () use ($id, $userId) {
            $user = User::findOrFail($userId);
            $role = $this->findById($id);
            $user->roles()->detach($role);

            return true;
        });
    }
}
