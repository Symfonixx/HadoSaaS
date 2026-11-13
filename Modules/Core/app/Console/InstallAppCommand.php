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
                            {--admin-password= : Initial admin password (prompted securely when omitted)}
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

        $password = (string) $this->option('admin-password');
        if ($password === '') {
            $password = (string) $this->secret('Choose an administrator password (minimum 12 characters)');
            if (strlen($password) < 12) {
                $this->components->error('Administrator password must be at least 12 characters.');
                return self::FAILURE;
            }
        }
        $this->bootstrapAdmin($password);

        $this->newLine();
        $this->components->info('Application installed successfully.');
        $this->components->twoColumnDetail('Admin Email', $this->option('admin-email'));

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
        // Only treat Core SQL as imported when seed data that lives exclusively in db.sql is present.
        // Do not use `permissions`: migrations may insert rows before import (e.g. module permissions),
        // which would skip the dump and leave Admin with only those migration-defined permissions.
        return Schema::hasTable('countries') && DB::table('countries')->exists();
    }

    private function bootstrapAdmin(string $password): void
    {
        DB::transaction(function () use ($password) {
            $adminEmail = (string) $this->option('admin-email');
            $adminMobile = (string) $this->option('admin-mobile');

            $role = Role::firstOrCreate([
                'name' => 'Admin',
                'guard_name' => 'web',
            ]);
            $role->syncPermissions(Permission::all());

            $userAttributes = [
                'name' => $this->option('admin-name'),
                'password' => Hash::make($password),
                'email' => $adminEmail,
                'mobile' => $adminMobile,
                'type' => 'admin',
            ];

            $user = User::query()
                ->where('email', $adminEmail)
                ->orWhere('mobile', $adminMobile)
                ->first();

            if ($user) {
                $user->fill(collect($userAttributes)->except('type')->all())->forceFill(['type' => 'admin'])->save();
            } else {
                $user = User::query()->create(collect($userAttributes)->except('type')->all());
                $user->forceFill(['type' => 'admin'])->save();
            }

            if (! $user->hasRole($role->name)) {
                $user->assignRole($role);
            }
        });
    }
}
