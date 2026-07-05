<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change default status from pending -> active
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','inactive') NOT NULL DEFAULT 'active'");

        // Activate all currently pending users
        DB::table('users')->where('status', 'pending')->update(['status' => 'active', 'is_active' => true]);

        // Seed registration_enabled setting
        DB::table('settings')->updateOrInsert(
            ['key' => 'registration_enabled'],
            ['value' => '1', 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','inactive') NOT NULL DEFAULT 'pending'");
        DB::table('settings')->where('key', 'registration_enabled')->delete();
    }
};
