<?php

namespace Modules\User\app\Repositories\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Modules\User\app\Data\UserData;

class UserModelRepository implements UserRepository
{
    public function all(string $type): LengthAwarePaginator
    {
        return User::query()
            ->where('type', $type)
            ->when($type === 'admin', fn ($query) => $query->whereDoesntHave('roles', fn ($roles) => $roles->where('name', 'Admin')))
            ->latest()
            ->paginate(config('core.page_size'));
    }

    public function find(int $id): ?User
    {
        return User::findOrFail($id);
    }

    public function store(UserData $userData): ?User
    {
        if (! in_array($userData->type, ['user', 'admin'], true)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only user or staff accounts may be created through this workflow.');
        }

        return User::query()->forceCreate([
            'name' => $userData->name,
            'email' => $userData->email,
            'mobile' => $userData->mobile,
            'password' => Hash::make($userData->password),
            'type' => $userData->type,
        ]);
    }

    public function update(UserData $userData, User $user): ?User
    {
        if ($user->hasRole('Admin')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('The super-admin account cannot be modified through this workflow.');
        }

            $updateData = [
                'name' => $userData->name,
                'email' => $userData->email,
                'mobile' => $userData->mobile,
            ];
            if (! is_null($userData->password)) {
                $updateData['password'] = Hash::make($userData->password);
            }

        if (! $user->update($updateData)) {
            return null;
        }

        return $user;
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }
}
