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
        Schema::create('billing_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3);
            $table->decimal('fx_rate_snapshot', 12, 6);
            $table->decimal('amount_base', 10, 2);
            $table->char('base_currency', 3)->default('MYR');
            $table->string('billing_cycle', 20);
            $table->date('billed_at');
            $table->string('source', 20)->default('manual');
            $table->timestamps();

            $table->index(['user_id', 'billed_at'], 'billing_events_user_billed_idx');
            $table->index(['subscription_id', 'billed_at'], 'billing_events_sub_billed_idx');
        });

        Schema::create('subscription_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_amount', 10, 2);
            $table->decimal('new_amount', 10, 2);
            $table->char('currency', 3);
            $table->decimal('change_amount', 10, 2);
            $table->decimal('change_percent', 7, 2);
            $table->string('detected_via', 20)->default('manual_edit');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['subscription_id', 'recorded_at'], 'sub_price_histories_sub_recorded_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_price_histories');
        Schema::dropIfExists('billing_events');
    }
};
