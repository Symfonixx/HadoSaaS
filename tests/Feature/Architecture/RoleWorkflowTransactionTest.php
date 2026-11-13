<?php

namespace Tests\Feature\Architecture;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\User\Application\Role\RoleManagementApplicationService;
use Modules\User\Repositories\Role\RoleRepository;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleWorkflowTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_create_rolls_back_when_permission_sync_fails(): void
    {
        $repository = Mockery::mock(RoleRepository::class);
        $repository->shouldReceive('store')->once()->andReturnUsing(fn (string $name) => Role::query()->create(['name' => $name]));
        $repository->shouldReceive('syncPermissions')->once()->andThrow(new \RuntimeException('sync failed'));
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        try {
            (new RoleManagementApplicationService($repository, $flash))->store('Editors', []);
            $this->fail('Expected permission sync failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('sync failed', $exception->getMessage());
        }

        $this->assertDatabaseMissing('roles', ['name' => 'Editors']);
    }

    public function test_role_update_rolls_back_when_permission_sync_fails(): void
    {
        $role = Role::query()->create(['name' => 'Editors']);
        $repository = Mockery::mock(RoleRepository::class);
        $repository->shouldReceive('findById')->once()->with($role->id)->andReturn($role);
        $repository->shouldReceive('update')->once()->andReturnUsing(function (Role $role, string $name) {
            $role->update(['name' => $name]);

            return $role;
        });
        $repository->shouldReceive('syncPermissions')->once()->andThrow(new \RuntimeException('sync failed'));
        $flash = Mockery::mock(FlashMessengerInterface::class);
        $flash->shouldNotReceive('success');

        try {
            (new RoleManagementApplicationService($repository, $flash))->update($role->id, 'Editors New', []);
            $this->fail('Expected permission sync failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('sync failed', $exception->getMessage());
        }

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Editors']);
    }
}
