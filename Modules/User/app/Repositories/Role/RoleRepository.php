<?php

namespace Modules\User\Repositories\Role;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepository
{
    public function all(): Collection;

    public function findById(int $id): Role;

    public function permissions(): Collection;

    public function store(string $name): ?Role;

    public function update(Role $role, string $name): ?Role;

    public function syncPermissions(Role $role, array $permissions): void;

    public function delete(int $id): ?bool;

    public function assignUsersToRole(int $id, array $userIds): bool;

    public function removeUsersFromRole(int $id, array $userIds): bool;

    public function removeUserFromRole(int $id, int $userId): bool;
}
