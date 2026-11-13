<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\User\Application\Role\RoleManagementApplicationService;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\TestCase;

class RoleMemberRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_detail_form_submits_the_validated_bulk_removal_field(): void
    {
        [$actor, $role, $member] = $this->createScenario();

        $this->actingAs($actor)
            ->get(route('admin.roles.show', $role->id))
            ->assertOk()
            ->assertSee('name="user_ids[]"', false);

        $this->actingAs($actor)
            ->post(route('admin.roles.remove_users_from_role'), [
                'role_id' => $role->id,
                'user_ids' => [$member->id],
            ])
            ->assertSessionHasNoErrors();

        $this->assertFalse($member->fresh()->hasRole($role));
    }

    public function test_bulk_role_removal_rejects_duplicate_or_missing_user_ids(): void
    {
        [$actor, $role, $member] = $this->createScenario();

        $this->actingAs($actor)
            ->post(route('admin.roles.remove_users_from_role'), [
                'role_id' => $role->id,
                'user_ids' => [$member->id, $member->id],
            ])
            ->assertSessionHasErrors('user_ids.1');

        $this->actingAs($actor)
            ->post(route('admin.roles.remove_users_from_role'), [
                'role_id' => $role->id,
                'user_ids' => [999999],
            ])
            ->assertSessionHasErrors('user_ids.0');

        $this->assertTrue($member->fresh()->hasRole($role));
    }

    public function test_super_admin_role_cannot_be_modified_deleted_or_assigned(): void
    {
        $superAdminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $member = $this->createUser('protected-member@example.test', '0905000000103');
        $service = app(RoleManagementApplicationService::class);

        try {
            $service->update($superAdminRole->id, 'Renamed', []);
            $this->fail('Expected super-admin role updates to be blocked.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('roles', ['id' => $superAdminRole->id, 'name' => 'Admin']);
        }

        try {
            $service->delete($superAdminRole->id);
            $this->fail('Expected super-admin role deletion to be blocked.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('roles', ['id' => $superAdminRole->id, 'name' => 'Admin']);
        }

        try {
            $service->assignUsersToRole($superAdminRole->id, [$member->id]);
            $this->fail('Expected assigning users to the super-admin role to be blocked.');
        } catch (AuthorizationException) {
            $this->assertDatabaseMissing('model_has_roles', ['model_id' => $member->id, 'role_id' => $superAdminRole->id]);
        }

        $this->assertSame('Admin', $superAdminRole->fresh()->name);
        $this->assertFalse($member->fresh()->hasRole($superAdminRole));
    }

    private function createScenario(): array
    {
        $permission = Permission::findOrCreate('Hr Management', 'web');
        $hrRole = Role::create(['name' => 'HR Test', 'guard_name' => 'web']);
        $hrRole->givePermissionTo($permission);

        $actor = $this->createUser('hr@example.test', '0905000000101', 'admin');
        $actor->assignRole($hrRole);

        $role = Role::create(['name' => 'Content Test', 'guard_name' => 'web']);
        $member = $this->createUser('member@example.test', '0905000000102');
        $member->assignRole($role);

        return [$actor, $role, $member];
    }

    private function createUser(string $email, string $mobile, string $type = 'user'): User
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => $email,
            'mobile' => $mobile,
            'password' => Hash::make('Test-password-42!'),
        ]);

        return $user->forceFill(['type' => $type])->save() ? $user->fresh() : $user;
    }
}
