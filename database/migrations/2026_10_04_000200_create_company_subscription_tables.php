<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_subscriptions', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->uuid('uuid')->unique();
            $t->foreignId('company_id')->constrained('companies');
            $t->foreignId('plan_id')->constrained('subscription_plans');
            $t->string('billing_cycle', 20)->default('MONTHLY');
            $t->date('start_date');
            $t->date('end_date');
            $t->string('status', 20)->default('TRIAL')->index();
            $t->boolean('auto_renew')->default(true);
            $t->decimal('price', 18, 2)->default(0);
            $t->foreignId('currency_id')->constrained('currencies');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });

        Schema::create('subscription_limit_snapshots', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $t->string('limit_code', 80);
            $t->decimal('limit_value', 18, 4)->nullable();
            $t->boolean('unlimited')->default(false);
            $t->timestamp('effective_from')->useCurrent();
            $t->timestamp('effective_to')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->unique(['subscription_id', 'limit_code', 'effective_from']);
        });

        Schema::create('subscription_usage', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('company_id')->constrained('companies');
            $t->foreignId('subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $t->string('usage_code', 80);
            $t->decimal('usage_value', 18, 4)->default(0);
            $t->timestamp('last_calculated_at')->useCurrent();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });

        Schema::create('subscription_usage_logs', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('company_id')->constrained('companies');
            $t->foreignId('subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $t->string('usage_code', 80);
            $t->decimal('previous_value', 18, 4)->default(0);
            $t->decimal('new_value', 18, 4)->default(0);
            $t->string('source', 50)->nullable();
            $t->timestamp('created_at')->useCurrent();
        });

        Schema::create('subscription_addons', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->uuid('uuid')->unique();
            $t->string('addon_code', 50)->unique();
            $t->string('addon_name', 100);
            $t->text('description')->nullable();
            $t->decimal('price', 18, 2)->default(0);
            $t->foreignId('currency_id')->constrained('currencies');
            $t->string('billing_cycle', 20)->default('MONTHLY');
            $t->boolean('is_active')->default(true);
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });

        Schema::create('company_subscription_addons', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('company_subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $t->foreignId('addon_id')->constrained('subscription_addons');
            $t->decimal('quantity', 18, 4)->default(1);
            $t->decimal('unit_price', 18, 2)->default(0);
            $t->date('start_date');
            $t->date('end_date')->nullable();
            $t->string('status', 20)->default('ACTIVE');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });

        Schema::create('subscription_trials', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('company_id')->constrained('companies');
            $t->foreignId('subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $t->date('start_date');
            $t->date('end_date');
            $t->string('status', 20)->default('ACTIVE');
            $t->timestamp('converted_at')->nullable();
            $t->timestamp('created_at')->useCurrent();
        });

        Schema::create('subscription_grace_periods', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('company_id')->constrained('companies');
            $t->foreignId('subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $t->date('start_date');
            $t->date('end_date');
            $t->text('reason')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('platform_admins')->nullOnDelete();
            $t->timestamp('created_at')->useCurrent();
        });

        Schema::create('subscription_changes', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->foreignId('company_id')->constrained('companies');
            $t->foreignId('old_subscription_id')->nullable()->constrained('company_subscriptions')->nullOnDelete();
            $t->foreignId('new_subscription_id')->nullable()->constrained('company_subscriptions')->nullOnDelete();
            $t->string('change_type', 30);
            $t->foreignId('old_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $t->foreignId('new_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $t->text('reason')->nullable();
            $t->foreignId('changed_by')->nullable()->constrained('platform_admins')->nullOnDelete();
            $t->timestamp('changed_at')->useCurrent();
        });

        Schema::create('company_limit_overrides', function (Blueprint $t) {
            $t->bigInteger('id')->generatedAs()->always()->primary();
            $t->uuid('uuid')->unique();
            $t->foreignId('company_id')->constrained('companies');
            $t->foreignId('subscription_id')->constrained('company_subscriptions')->cascadeOnDelete();
            $t->string('limit_code', 80);
            $t->decimal('base_limit', 18, 4)->default(0);
            $t->decimal('override_value', 18, 4)->default(0);
            $t->string('override_type', 20)->default('INCREASE');
            $t->text('reason')->nullable();
            $t->timestamp('starts_at')->useCurrent();
            $t->timestamp('expires_at')->nullable();
            $t->string('status', 20)->default('ACTIVE');
            $t->foreignId('created_by')->constrained('platform_admins');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_limit_overrides');
        Schema::dropIfExists('subscription_changes');
        Schema::dropIfExists('subscription_grace_periods');
        Schema::dropIfExists('subscription_trials');
        Schema::dropIfExists('company_subscription_addons');
        Schema::dropIfExists('subscription_addons');
        Schema::dropIfExists('subscription_usage_logs');
        Schema::dropIfExists('subscription_usage');
        Schema::dropIfExists('subscription_limit_snapshots');
        Schema::dropIfExists('company_subscriptions');
    }
};
