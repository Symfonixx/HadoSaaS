<?php

namespace Modules\User\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Application\Role\RoleManagementApplicationService;
use Modules\User\Http\Requests\RoleRequest;
use Modules\User\Http\Requests\RoleUsersRequest;

class RoleController extends Controller
{
    public function __construct(protected RoleManagementApplicationService $roleService)
    {
        $this->setActive('hr');
        $this->setActive('roles');
    }

    public function index()
    {
        $roles = $this->roleService->all();
        $permissions = $this->roleService->permissions();

        return view('user::admin.role.index', compact('roles', 'permissions'));
    }

    public function store(RoleRequest $request)
    {
        $this->roleService->store($request->input('role_name'), $request->input('permissions'));

        return back();
    }

    public function show($id)
    {
        $permissions = $this->roleService->permissions();
        $role = $this->roleService->findById($id);
        $users = $this->roleService->availableAdminsWithoutRole($id);

        return view('user::admin.role.show', compact('role', 'users', 'permissions'));
    }

    public function update(RoleRequest $request, $id)
    {
        $this->roleService->update($id, $request->input('role_name'), $request->input('permissions'));

        return back();
    }

    public function delete_role($id)
    {
        $this->roleService->delete($id);

        return redirect()->route('admin.roles.index');
    }

    public function assignUsersToRole(RoleUsersRequest $request)
    {
        $this->roleService->assignUsersToRole($request->input('role_id'), $request->input('user_ids'));

        return back();
    }

    public function removeUsersFromRole(RoleUsersRequest $request)
    {
        $this->roleService->removeUsersFromRole($request->input('role_id'), $request->input('user_ids'));

        return back();
    }

    public function removeUserFromRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required',
            'user_id' => 'required',
        ]);
        $this->roleService->removeUserFromRole($request->input('role_id'), $request->input('user_id'));

        return response()->json(['success' => __('The Operation Done Successfully')]);
    }
}
