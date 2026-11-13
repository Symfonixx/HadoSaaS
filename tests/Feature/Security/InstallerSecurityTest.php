<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallerSecurityTest extends TestCase
{
    use RefreshDatabase;

    private string|false $originalPassword = false;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'test-key']);
        $this->originalPassword = getenv('HADO_ADMIN_PASSWORD');
    }

    protected function tearDown(): void
    {
        if ($this->originalPassword === false) {
            putenv('HADO_ADMIN_PASSWORD');
            unset($_ENV['HADO_ADMIN_PASSWORD'], $_SERVER['HADO_ADMIN_PASSWORD']);
        } else {
            putenv('HADO_ADMIN_PASSWORD='.$this->originalPassword);
            $_ENV['HADO_ADMIN_PASSWORD'] = $this->originalPassword;
        }

        parent::tearDown();
    }

    public function test_installer_refuses_to_take_over_an_account_matching_the_email(): void
    {
        $user = $this->createUser('existing@example.test', '0905000000011');
        $oldPassword = $user->password;

        $this->artisan('app:install', [
            '--skip-sql' => true,
            '--admin-email' => $user->email,
            '--admin-mobile' => '0905000000022',
        ])->assertExitCode(1);

        $this->assertSame('user', $user->fresh()->type);
        $this->assertSame($oldPassword, $user->fresh()->password);
        $this->assertDatabaseMissing('roles', ['name' => 'Admin']);
    }

    public function test_installer_refuses_to_take_over_an_account_matching_the_mobile(): void
    {
        $user = $this->createUser('existing@example.test', '0905000000011');
        $oldPassword = $user->password;

        $this->artisan('app:install', [
            '--skip-sql' => true,
            '--admin-email' => 'new-admin@example.test',
            '--admin-mobile' => $user->mobile,
        ])->assertExitCode(1);

        $this->assertSame('user', $user->fresh()->type);
        $this->assertSame($oldPassword, $user->fresh()->password);
        $this->assertDatabaseMissing('roles', ['name' => 'Admin']);
    }

    public function test_installer_rejects_a_weak_environment_password(): void
    {
        putenv('HADO_ADMIN_PASSWORD=weak');
        $_ENV['HADO_ADMIN_PASSWORD'] = 'weak';

        $this->artisan('app:install', [
            '--skip-sql' => true,
            '--admin-email' => 'fresh-admin@example.test',
            '--admin-mobile' => '0905000000033',
        ])->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'fresh-admin@example.test']);
    }

    public function test_installer_creates_a_new_admin_without_printing_the_password(): void
    {
        $password = 'Strong-passphrase-42!';
        putenv('HADO_ADMIN_PASSWORD='.$password);
        $_ENV['HADO_ADMIN_PASSWORD'] = $password;

        $command = $this->artisan('app:install', [
            '--skip-sql' => true,
            '--admin-email' => 'fresh-admin@example.test',
            '--admin-mobile' => '0905000000033',
        ]);
        $command->assertExitCode(0);
        $command->assertDontSee($password);

        $admin = User::query()->where('email', 'fresh-admin@example.test')->firstOrFail();
        $this->assertSame('admin', $admin->type);
        $this->assertTrue(Hash::check($password, $admin->password));
        $this->assertTrue($admin->hasRole('Admin'));
    }

    private function createUser(string $email, string $mobile): User
    {
        return User::query()->create([
            'name' => 'Existing User',
            'email' => $email,
            'mobile' => $mobile,
            'password' => Hash::make('Existing-password-42!'),
        ]);
    }
}
