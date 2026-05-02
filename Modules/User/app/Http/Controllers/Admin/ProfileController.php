<?php

namespace Modules\User\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Traits\FileTrait;
use Modules\User\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    use FileTrait;

    public function edit()
    {
        $user = auth()->user();

        return view('user::admin.profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $data = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->string('password')->toString());
        }

        if ($request->hasFile('img')) {
            $data['img'] = $this->upload(
                file: $request->file('img'),
                dir: 'users',
                name: 'avatar-'.$user->id,
                old: $user->img
            );
        } elseif ($request->filled('img_media_path')) {
            $data['img'] = (string) $request->input('img_media_path');
        }

        $user->update($data);
        session()->flushMessage(true, __('Profile updated successfully'));

        return redirect()->route('admin.profile.edit');
    }
}
