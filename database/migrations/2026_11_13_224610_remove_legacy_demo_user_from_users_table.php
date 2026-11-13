<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $demoUserIds = DB::table('users')
            ->where('email', 'demo@subtracker.test')
            ->pluck('id');

        if ($demoUserIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($demoUserIds): void {
            DB::table('subscriptions')->whereIn('user_id', $demoUserIds)->delete();
            DB::table('categories')->whereIn('user_id', $demoUserIds)->delete();
            DB::table('user_settings')->whereIn('user_id', $demoUserIds)->delete();
            DB::table('users')->whereIn('id', $demoUserIds)->delete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy shared demo account is intentionally removed and not recreated.
    }
};
