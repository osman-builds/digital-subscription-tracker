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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('is_trial')->default(false)->after('status');
            $table->date('trial_ends_at')->nullable()->after('is_trial');
            $table->string('cancellation_url')->nullable()->after('website_url');
            $table->index(['user_id', 'status', 'next_billing_date'], 'subscriptions_user_status_billing_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subscriptions_user_status_billing_idx');
            $table->dropColumn(['is_trial', 'trial_ends_at', 'cancellation_url']);
        });
    }
};
