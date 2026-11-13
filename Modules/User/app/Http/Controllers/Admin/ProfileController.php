<?php

namespace Modules\User\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Modules\Core\Traits\FileTrait;
use Modules\Core\Application\Files\FileCleanupResult;
use Modules\Core\Application\Files\PendingFileDeletionService;
use Modules\User\Http\Requests\UpdateProfileRequest;
use Throwable;

class ProfileController extends Controller
{
    use FileTrait;

    public function edit()
    {
        $user = auth()->user();

        return view('user::admin.profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request, PendingFileDeletionService $fileDeletions): RedirectResponse
    {
        $user = auth()->user();

        $data = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->string('password')->toString());
        }

        $stagedPath = null;
        if ($request->hasFile('img')) {
            $stagedPath = $this->upload(
                file: $request->file('img'),
                dir: 'users',
                name: 'avatar-'.$user->id,
            );
            $data['img'] = $stagedPath;
        } elseif ($request->filled('img_media_path')) {
            $data['img'] = (string) $request->input('img_media_path');
        }

        $oldPath = $user->img;
        $newPath = $data['img'] ?? $oldPath;

        try {
            DB::transaction(function () use ($user, $data, $oldPath, $newPath, $fileDeletions): void {
                if (! $user->update($data)) {
                    throw new \RuntimeException('The profile update did not persist.');
                }

                if (is_string($oldPath) && $oldPath !== '' && $oldPath !== $newPath) {
                    $fileDeletions->queueUnreferenced([$oldPath]);
                }
            });
        } catch (Throwable $exception) {
            if ($stagedPath !== null) {
                try {
                    $fileDeletions->queueUnreferenced([$stagedPath]);
                    $fileDeletions->processAndReport([$stagedPath]);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        $cleanup = is_string($oldPath) && $oldPath !== '' && $oldPath !== $newPath
            ? $fileDeletions->processAndReport([$oldPath])
            : FileCleanupResult::Complete;

        if ($cleanup === FileCleanupResult::Complete) {
            session()->flushMessage(true, __('Profile updated successfully'));
        } else {
            session()->flushMessage(false, __('Profile updated; old image cleanup is pending and will be retried.'));
        }

        return redirect()->route('admin.profile.edit');
    }
}
