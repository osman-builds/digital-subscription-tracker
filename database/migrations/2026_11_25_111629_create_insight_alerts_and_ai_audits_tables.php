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
        Schema::create('insight_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('alert_type', 32);
            $table->string('severity', 16)->default('info');
            $table->string('title', 160);
            $table->text('message');
            $table->string('dedupe_key', 120);
            $table->boolean('is_read')->default(false);
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'dedupe_key'], 'insight_alerts_user_dedupe_unique');
            $table->index(['user_id', 'is_read', 'severity'], 'insight_alerts_user_read_severity_idx');
        });

        Schema::create('ai_extraction_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('input_type', 16);
            $table->char('file_sha256', 64);
            $table->string('model_used', 64);
            $table->decimal('confidence_score', 5, 2);
            $table->string('status', 24)->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'file_sha256'], 'ai_extraction_audits_user_hash_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_extraction_audits');
        Schema::dropIfExists('insight_alerts');
    }
};
