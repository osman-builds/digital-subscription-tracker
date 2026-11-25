<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->decimal('monthly_budget', 10, 2)->nullable()->after('base_currency');
            $table->unsignedTinyInteger('budget_alert_percent')->default(80)->after('monthly_budget');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn(['monthly_budget', 'budget_alert_percent']);
        });
    }
};
