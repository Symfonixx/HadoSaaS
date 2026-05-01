<?php

namespace Modules\Core\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:install
                            {--fresh : Drop all tables and re-run all migrations}
                            {--skip-sql : Skip importing Modules/Core/database/db.sql}
                            {--admin-name=Admin : Initial admin display name}
                            {--admin-email=admin@example.com : Initial admin email}
                            {--admin-password=12345678 : Initial admin password}
                            {--admin-mobile=0905000000000 : Initial admin mobile}';

    /**
     * The console command description.
     */
    protected $description = 'This Command Will Install App.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Starting HadoSaaS installation...');

        if (! config('app.key')) {
            $this->components->task('Generating APP_KEY', function () {
                Artisan::call('key:generate', ['--force' => true]);
            });
        } else {
            $this->line('APP_KEY already exists, skipping key generation.');
        }

        $this->runMigrations();

        if (! $this->option('skip-sql')) {
            if (! $this->importCoreSqlDump()) {
                return self::FAILURE;
            }
        }

        $this->bootstrapAdmin();

        $this->newLine();
        $this->components->info('Application installed successfully.');
        $this->components->twoColumnDetail('Admin Email', $this->option('admin-email'));
        $this->components->twoColumnDetail('Admin Password', $this->option('admin-password'));

        return self::SUCCESS;
    }

    private function runMigrations(): void
    {
        if ($this->option('fresh')) {
            $this->components->task('Running migrate:fresh', function () {
                Artisan::call('migrate:fresh', ['--force' => true]);
                $this->output->write(Artisan::output());
            });

            return;
        }

        $this->components->task('Running migrations', function () {
            Artisan::call('migrate', ['--force' => true]);
            $this->output->write(Artisan::output());
        });
    }

    private function importCoreSqlDump(): bool
    {
        $sqlFilePath = module_path('Core', 'database/db.sql');
        if (! file_exists($sqlFilePath)) {
            $this->components->error('SQL file not found at path: '.$sqlFilePath);

            return false;
        }

        if ($this->coreSeedDataAlreadyImported()) {
            $this->line('Core SQL data already exists, skipping SQL import.');

            return true;
        }

        $this->components->task('Importing Core SQL dump', function () use ($sqlFilePath) {
            DB::unprepared(file_get_contents($sqlFilePath));
        });

        return true;
    }

    private function coreSeedDataAlreadyImported(): bool
    {
        foreach (['countries', 'permissions'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function bootstrapAdmin(): void
    {
        DB::transaction(function () {
            $adminEmail = (string) $this->option('admin-email');
            $adminMobile = (string) $this->option('admin-mobile');

            $role = Role::firstOrCreate([
                'name' => 'Admin',
                'guard_name' => 'web',
            ]);
            $role->syncPermissions(Permission::all());

            $userAttributes = [
                'name' => $this->option('admin-name'),
                'password' => Hash::make((string) $this->option('admin-password')),
                'email' => $adminEmail,
                'mobile' => $adminMobile,
                'type' => 'admin',
            ];

            $user = User::query()
                ->where('email', $adminEmail)
                ->orWhere('mobile', $adminMobile)
                ->first();

            if ($user) {
                $user->fill($userAttributes)->save();
            } else {
                $user = User::query()->create($userAttributes);
            }

            if (! $user->hasRole($role->name)) {
                $user->assignRole($role);
            }
        });
    }
}
