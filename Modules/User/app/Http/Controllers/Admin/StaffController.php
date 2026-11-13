<?php

namespace Modules\User\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Application\Role\RoleManagementApplicationService;
use Modules\User\Application\User\UserManagementApplicationService;
use Modules\User\app\Data\UserData;
use Modules\User\Http\Requests\StoreUserRequest;

class StaffController extends Controller
{
    public function __construct(
        protected UserManagementApplicationService $userService,
        protected RoleManagementApplicationService $roleService
    )
    {
        $this->setActive('hr');
        $this->setActive('staffs');
    }

    public function index()
    {
        $roles = $this->roleService->all();
        $model = $this->userService->paginate('staff');

        return view('user::.admin.staff.index', compact('model', 'roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $userData = UserData::validateAndCreate(array_merge($request->all(), ['type' => 'staff']));
        $user = $this->userService->store($userData);
        $this->roleService->assignUsersToRole($request->input('role_id'), [$user->id]);

        return redirect()->route('admin.staffs.index');
    }

    public function update(Request $request, $id)
    {
        $userData = UserData::validateAndCreate(array_merge($request->all(), ['type' => 'staff']));
        $user = \App\Models\User::query()->where('type', 'staff')->findOrFail($id);
        $this->userService->update($userData, $user);

        return redirect()->route('admin.staffs.index');
    }

    public function destroy($id)
    {
        $user = \App\Models\User::query()->where('type', 'staff')->findOrFail($id);
        $this->userService->delete($user);

        return response()->json([
            'success' => true,
        ]);
    }
}
