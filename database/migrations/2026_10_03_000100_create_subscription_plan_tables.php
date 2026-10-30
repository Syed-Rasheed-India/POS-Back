<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->uuid('uuid')->unique();
            $t->string('plan_code', 50)->unique();
            $t->string('plan_name', 100);
            $t->text('description')->nullable();
            $t->decimal('monthly_price', 18, 2)->default(0);
            $t->decimal('quarterly_price', 18, 2)->nullable();
            $t->decimal('half_yearly_price', 18, 2)->nullable();
            $t->decimal('yearly_price', 18, 2)->nullable();
            $t->foreignId('currency_id')->constrained('currencies');
            $t->integer('trial_days')->default(0);
            $t->integer('grace_days')->default(0);
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });

        Schema::create('subscription_plan_limits', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $t->string('limit_code', 80);
            $t->decimal('limit_value', 18, 4)->nullable();
            $t->boolean('unlimited')->default(false);
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->unique(['plan_id', 'limit_code']);
        });

        Schema::create('subscription_plan_modules', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $t->foreignId('module_id')->constrained('platform_modules')->cascadeOnDelete();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->unique(['plan_id', 'module_id']);
        });

        Schema::create('subscription_plan_features', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $t->foreignId('feature_id')->constrained('platform_features')->cascadeOnDelete();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->unique(['plan_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_features');
        Schema::dropIfExists('subscription_plan_modules');
        Schema::dropIfExists('subscription_plan_limits');
        Schema::dropIfExists('subscription_plans');
    }
};
