<?php

namespace Modules\User\Repositories\Role;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleModelRepository implements RoleRepository
{
    public function permissions(): Collection
    {
        return Permission::all();
    }

    public function all(): Collection
    {
        return Role::all();
    }

    public function store(string $name): ?Role
    {
        return Role::create(['name' => $name]);
    }

    public function update(Role $role, string $name): ?Role
    {
        if (! $role->update(['name' => $name])) {
            return null;
        }

        return $role;
    }

    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }

    public function findById(int $id): Role
    {
        return Role::findOrFail($id);
    }

    public function delete(int $id): bool
    {
        $role = $this->findById($id);
        return (bool) $role->delete();
    }

    public function assignUsersToRole(int $id, array $userIds): bool
    {
        $role = $this->findById($id);
        $users = User::whereIn('id', $userIds)->get();
        $role->users()->syncWithoutDetaching($users);

        return true;
    }

    public function removeUsersFromRole(int $id, array $userIds): bool
    {
        $role = $this->findById($id);
        $users = User::whereIn('id', $userIds)->get();
        $role->users()->detach($users);

        return true;
    }

    public function removeUserFromRole(int $id, int $userId): bool
    {
        $user = User::findOrFail($userId);
        $role = $this->findById($id);
        $user->roles()->detach($role);

        return true;
    }
}
