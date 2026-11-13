<?php

namespace Modules\User\app\Repositories\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Traits\FileTrait;
use Modules\User\app\Data\UserData;

class UserModelRepository implements UserRepository
{
    use FileTrait;

    public function all(string $type): LengthAwarePaginator
    {
        return User::where('type', $type)->latest()->paginate(config('core.page_size'));
    }

    public function find(int $id): ?User
    {
        return User::findOrFail($id);
    }

    public function store(UserData $userData): ?User
    {
        if (! in_array($userData->type, ['user', 'staff'], true)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Only standard user accounts may be created through this workflow.');
        }

        $user = User::create([
                'name' => $userData->name,
                'email' => $userData->email,
                'mobile' => $userData->mobile,
                'password' => Hash::make($userData->password),
            ]);

        $user->forceFill(['type' => $userData->type])->save();
        return $user;
    }

    public function update(UserData $userData, User $user): ?User
    {
        if ($user->type === 'admin') {
            throw new \Illuminate\Auth\Access\AuthorizationException('This workflow cannot modify this account type.');
        }

            $updateData = [
                'name' => $userData->name,
                'email' => $userData->email,
                'mobile' => $userData->mobile,
            ];
            if (! is_null($userData->password)) {
                $updateData['password'] = Hash::make($userData->password);
            }

        $user->update($updateData);

        return $user;
    }

    public function delete(User $user): bool
    {
        if ($user->type === 'admin') {
            throw new \Illuminate\Auth\Access\AuthorizationException('This workflow cannot delete this account type.');
        }

        if ($user->img) {
            $this->deleteFile($user->img);
        }
        return (bool) $user->delete();

    }
}
