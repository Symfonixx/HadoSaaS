<?php

namespace Modules\User\app\Repositories\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Traits\ExceptionHandlerTrait;
use Modules\Core\Traits\FileTrait;
use Modules\User\app\Data\UserData;

class UserModelRepository implements UserRepository
{
    use ExceptionHandlerTrait, FileTrait;

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
        return $this->execute(function () use ($userData) {
            $user = User::create([
                'name' => $userData->name,
                'email' => $userData->email,
                'mobile' => $userData->mobile,
                'password' => Hash::make($userData->password),
                'type' => $userData->type,
            ]);

            return $user;
        });
    }

    public function update(UserData $userData, User $user): ?User
    {
        return $this->execute(function () use ($userData, $user) {
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
        });
    }

    public function delete(User $user): bool
    {
        return $this->execute(function () use ($user) {
            if ($user->img) {
                $this->deleteFile($user->img);
            }
            $user->delete();

            return true;
        });

    }
}
