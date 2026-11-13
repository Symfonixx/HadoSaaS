<?php

namespace Modules\User\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Application\User\UserManagementApplicationService;
use Modules\User\app\Data\UserData;
use Modules\User\Http\Requests\StoreUserRequest;

class UserController extends Controller
{
    public function __construct(protected UserManagementApplicationService $userService)
    {
        $this->setActive('hr');
        $this->setActive('users');
    }

    public function index()
    {
        $model = $this->userService->paginate('user');

        return view('user::.admin.user.index', compact('model'));
    }

    public function store(StoreUserRequest $request)
    {
        $userData = UserData::validateAndCreate(array_merge($request->all(), ['type' => 'user']));
        $this->userService->store($userData);

        return redirect()->route('admin.users.index');
    }

    public function update(Request $request, $id)
    {
        $user = \App\Models\User::query()->where('type', 'user')->findOrFail($id);
        $userData = UserData::validateAndCreate(array_merge($request->all(), ['type' => 'user']));
        $this->userService->update($userData, $user);

        return redirect()->route('admin.users.index');
    }

    public function destroy($id)
    {
        $user = \App\Models\User::query()->where('type', 'user')->findOrFail($id);
        $this->userService->delete($user);

        return response()->json([
            'success' => true,
        ]);
    }
}
