<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phase 07: Billing & Payments
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('company_subscriptions')->onDelete('set null');
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date')->default(now());
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 18, 2)->default(0.00);
            $table->decimal('tax_amount', 18, 2)->default(0.00);
            $table->decimal('discount_amount', 18, 2)->default(0.00);
            $table->decimal('total_amount', 18, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'paid', 'partially_paid', 'overdue', 'void', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('subscription_invoices')->onDelete('cascade');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 18, 2)->default(0.00);
            $table->decimal('subtotal', 18, 2)->default(0.00);
            $table->decimal('tax_amount', 18, 2)->default(0.00);
            $table->decimal('total_amount', 18, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('invoice_id')->constrained('subscription_invoices')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('payment_method', 50)->default('credit_card');
            $table->string('transaction_reference')->nullable();
            $table->decimal('amount', 18, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('completed');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->json('supported_currencies')->nullable();
            $table->timestamps();
        });

        Schema::create('company_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('gateway_id')->constrained('payment_gateways')->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->text('encrypted_credentials')->nullable();
            $table->timestamps();
        });

        // Phase 08 & 09: ECR Integration & Terminal Logs
        Schema::create('ecr_providers', function (Blueprint $table) {
            $table->id();
            $table->string('provider_code')->unique();
            $table->string('provider_name');
            $table->string('api_version')->default('v1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ecr_terminal_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('ecr_provider_id')->constrained('ecr_providers')->onDelete('cascade');
            $table->string('terminal_id');
            $table->string('ip_address')->nullable();
            $table->string('status')->default('online');
            $table->timestamps();
        });

        Schema::create('ecr_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terminal_mapping_id')->constrained('ecr_terminal_mappings')->onDelete('cascade');
            $table->string('transaction_type');
            $table->decimal('amount', 18, 2);
            $table->string('response_code');
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        // Phase 10: Platform API Management
        Schema::create('platform_api_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('client_id')->unique();
            $table->string('client_secret_hash');
            $table->string('client_name');
            $table->boolean('is_revoked')->default(false);
            $table->timestamps();
        });

        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('platform_api_clients')->onDelete('cascade');
            $table->string('endpoint');
            $table->string('http_method', 10);
            $table->integer('response_code');
            $table->integer('execution_time_ms');
            $table->timestamps();
        });

        // Phase 11: Notifications Management
        Schema::create('platform_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->boolean('is_global')->default(false);
            $table->timestamps();
        });

        Schema::create('company_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->string('channel')->default('email');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('company_notifications')->onDelete('cascade');
            $table->string('recipient');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_logs');
        Schema::dropIfExists('company_notifications');
        Schema::dropIfExists('platform_notifications');
        Schema::dropIfExists('api_usage_logs');
        Schema::dropIfExists('platform_api_clients');
        Schema::dropIfExists('ecr_transaction_logs');
        Schema::dropIfExists('ecr_terminal_mappings');
        Schema::dropIfExists('ecr_providers');
        Schema::dropIfExists('company_payment_gateways');
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscription_invoice_items');
        Schema::dropIfExists('subscription_invoices');
    }
};
