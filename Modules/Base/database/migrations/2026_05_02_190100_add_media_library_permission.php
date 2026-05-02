<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Permission::query()->firstOrCreate([
            'name' => 'Media Library Management',
            'guard_name' => 'web',
        ]);
    }

    public function down(): void
    {
        Permission::query()
            ->where('name', 'Media Library Management')
            ->where('guard_name', 'web')
            ->delete();
    }
};
