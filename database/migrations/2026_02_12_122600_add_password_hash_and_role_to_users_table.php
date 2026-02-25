<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_hash')) {
                $table->text('password_hash')->nullable();
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->nullable();
            }
        });

        // Backfill for legacy/default Laravel schema (password -> password_hash, role -> user).
        if (Schema::hasColumn('users', 'password') && Schema::hasColumn('users', 'password_hash')) {
            DB::table('users')
                ->whereNull('password_hash')
                ->update(['password_hash' => DB::raw('password')]);
        }

        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->whereNull('role')
                ->update(['role' => 'user']);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_hash')) {
                $table->dropColumn('password_hash');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};

