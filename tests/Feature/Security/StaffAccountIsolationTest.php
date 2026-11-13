<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\User\app\Data\UserData;
use Modules\User\app\Repositories\User\UserModelRepository;
use Modules\User\Application\User\UserManagementApplicationService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffAccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_accounts_use_the_existing_admin_type_and_remain_listed(): void
    {
        $staffRole = Role::create(['name' => 'Staff Test', 'guard_name' => 'web']);
        $staff = $this->createUser('staff@example.test', '0905000000201', 'admin');
        $staff->assignRole($staffRole);

        $superAdminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $superAdmin = $this->createUser('root@example.test', '0905000000202', 'admin');
        $superAdmin->assignRole($superAdminRole);

        $listed = app(UserModelRepository::class)->all('admin')->getCollection();

        $this->assertTrue($listed->contains('id', $staff->id));
        $this->assertFalse($listed->contains('id', $superAdmin->id));
    }

    public function test_staff_creation_rejects_the_super_admin_role_and_accepts_a_regular_role(): void
    {
        Permission::findOrCreate('Hr Management', 'web');
        $hrRole = Role::create(['name' => 'HR Staff Test', 'guard_name' => 'web']);
        $hrRole->givePermissionTo('Hr Management');
        $actor = $this->createUser('hrstaff@example.test', '0905000000203', 'admin');
        $actor->assignRole($hrRole);

        $regularRole = Role::create(['name' => 'Editor Test', 'guard_name' => 'web']);
        $superAdminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);

        $this->actingAs($actor)->post(route('admin.staffs.store'), [
            'name' => 'New Staff',
            'email' => 'newstaff@example.test',
            'mobile' => '0905000000204',
            'password' => 'Strong-password-42!',
            'type' => 'user',
            'role_id' => $superAdminRole->id,
        ])->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => 'newstaff@example.test']);

        $this->actingAs($actor)->post(route('admin.staffs.store'), [
            'name' => 'New Staff',
            'email' => 'newstaff@example.test',
            'mobile' => '0905000000204',
            'password' => 'Strong-password-42!',
            'type' => 'user',
            'role_id' => $regularRole->id,
        ])->assertRedirect(route('admin.staffs.index'));

        $staff = User::query()->where('email', 'newstaff@example.test')->firstOrFail();
        $this->assertSame('admin', $staff->type);
        $this->assertTrue($staff->hasRole($regularRole));
    }

    public function test_user_creation_ignores_a_request_supplied_admin_account_type(): void
    {
        $permission = Permission::findOrCreate('Hr Management', 'web');
        $hrRole = Role::create(['name' => 'HR User Store Test', 'guard_name' => 'web']);
        $hrRole->givePermissionTo($permission);
        $actor = $this->createUser('hruserstore@example.test', '0905000000208', 'admin');
        $actor->assignRole($hrRole);

        $this->actingAs($actor)->post(route('admin.users.store'), [
            'name' => 'Ordinary User',
            'email' => 'ordinary@example.test',
            'mobile' => '0905000000209',
            'password' => 'Strong-password-42!',
            'type' => 'admin',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'ordinary@example.test', 'type' => 'user']);
    }

    public function test_staff_workflow_cannot_modify_or_delete_the_super_admin_account(): void
    {
        $permission = Permission::findOrCreate('Hr Management', 'web');
        $hrRole = Role::create(['name' => 'HR Isolate Test', 'guard_name' => 'web']);
        $hrRole->givePermissionTo($permission);
        $actor = $this->createUser('hrisolate@example.test', '0905000000205', 'admin');
        $actor->assignRole($hrRole);

        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $superAdmin = $this->createUser('protected@example.test', '0905000000206', 'admin');
        $superAdmin->assignRole($adminRole);
        $oldName = $superAdmin->name;

        $this->actingAs($actor)->put(route('admin.staffs.update', $superAdmin->id), [
            'name' => 'Changed',
            'email' => 'protected@example.test',
            'mobile' => '0905000000206',
            'password' => '',
        ])->assertNotFound();

        $this->actingAs($actor)->delete(route('admin.staffs.destroy', $superAdmin->id))->assertNotFound();
        $this->assertSame($oldName, $superAdmin->fresh()->name);
    }

    public function test_last_admin_account_cannot_be_deleted(): void
    {
        $admin = $this->createUser('lastadmin@example.test', '0905000000207', 'admin');
        $data = UserData::validateAndCreate([
            'name' => 'Last Admin',
            'email' => $admin->email,
            'mobile' => $admin->mobile,
            'password' => null,
            'type' => 'admin',
        ]);

        try {
            app(UserManagementApplicationService::class)->delete($admin);
            $this->fail('Expected the last administrator deletion to be rejected.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('users', ['id' => $admin->id]);
        }
    }

    private function createUser(string $email, string $mobile, string $type = 'user'): User
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => $email,
            'mobile' => $mobile,
            'password' => Hash::make('Test-password-42!'),
        ]);

        $user->forceFill(['type' => $type])->save();

        return $user->fresh();
    }
}
