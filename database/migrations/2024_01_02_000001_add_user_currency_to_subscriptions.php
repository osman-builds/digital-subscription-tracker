<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            // Original currency the user enters the price in (e.g. USD, EUR)
            $table->string('currency', 3)->default('MYR')->after('amount');
            // Cached MYR equivalent at time of last FX refresh
            $table->decimal('amount_myr', 10, 2)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'currency', 'amount_myr']);
        });
    }
};
