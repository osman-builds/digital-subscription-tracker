<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('billing_cycle', ['weekly', 'monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
            $table->date('start_date');
            $table->date('next_billing_date');
            $table->enum('status', ['active', 'inactive', 'cancelled'])->default('active');
            $table->string('website_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
