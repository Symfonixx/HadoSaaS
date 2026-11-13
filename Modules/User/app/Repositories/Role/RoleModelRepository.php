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

    public function store(string $name, array $permissions): ?Role
    {
        $role = Role::create(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role;
    }

    public function update(int $id, string $name, array $permissions): ?Role
    {
        $role = $this->findById($id);
        abort_if($role->name === 'Admin', 403, 'The super-admin role cannot be modified.');
        $role->update(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role;
    }

    public function findById(int $id): Role
    {
        return Role::findOrFail($id);
    }

    public function delete(int $id): bool
    {
        $role = $this->findById($id);
        abort_if($role->name === 'Admin', 403, 'The super-admin role cannot be deleted.');
        return (bool) $role->delete();
    }

    public function assignUsersToRole(int $id, array $userIds): bool
    {
        $role = $this->findById($id);
        abort_if($role->name === 'Admin', 403, 'Users cannot be assigned through this workflow to the super-admin role.');
            $users = User::whereIn('id', $userIds)->get();
            $role->users()->syncWithoutDetaching($users);

        return true;
    }

    public function removeUsersFromRole(int $id, array $userIds): bool
    {
        $role = $this->findById($id);
        abort_if($role->name === 'Admin', 403, 'Users cannot be removed through this workflow from the super-admin role.');
        $users = User::whereIn('id', $userIds)->get();
        $role->users()->detach($users);

        return true;
    }

    public function removeUserFromRole(int $id, int $userId): bool
    {
        $user = User::findOrFail($userId);
        $role = $this->findById($id);
        abort_if($role->name === 'Admin', 403, 'Users cannot be removed through this workflow from the super-admin role.');
        $user->roles()->detach($role);

        return true;
    }
}
