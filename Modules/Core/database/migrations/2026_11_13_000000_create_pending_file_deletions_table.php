<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_file_deletions', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 64);
            $table->text('path');
            $table->char('path_hash', 64);
            $table->timestamps();
            $table->unique(['disk', 'path_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_file_deletions');
    }
};
