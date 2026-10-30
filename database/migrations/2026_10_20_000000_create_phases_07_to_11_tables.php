
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phase 08 & 09: ECR Integration
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
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();
            $table->foreignId('ecr_provider_id')
                ->constrained('ecr_providers')
                ->cascadeOnDelete();
            $table->string('terminal_id');
            $table->string('ip_address')->nullable();
            $table->string('status')->default('online');
            $table->timestamps();
        });

        Schema::create('ecr_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terminal_mapping_id')
                ->constrained('ecr_terminal_mappings')
                ->cascadeOnDelete();
            $table->string('transaction_type');
            $table->decimal('amount', 18, 2);
            $table->string('response_code');
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        // Phase 10: Platform API Management
        Schema::create('platform_api_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();
            $table->string('client_id')->unique();
            $table->string('client_secret_hash');
            $table->string('client_name');
            $table->boolean('is_revoked')->default(false);
            $table->timestamps();
        });

        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();

            // References platform_api_clients.id, not its string client_id.
            $table->foreignId('client_id')
                ->constrained('platform_api_clients')
                ->cascadeOnDelete();

            $table->string('endpoint');
            $table->string('http_method', 10);
            $table->integer('response_code');
            $table->integer('execution_time_ms');
            $table->timestamps();
        });

        // Phase 11: Notifications
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
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('channel')->default('email');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')
                ->constrained('company_notifications')
                ->cascadeOnDelete();
            $table->string('recipient');
            $table->enum('status', ['queued', 'sent', 'failed'])
                ->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Only remove the eight tables owned by this migration.
        // Existing billing tables are intentionally untouched.
        Schema::dropIfExists('notification_delivery_logs');
        Schema::dropIfExists('company_notifications');
        Schema::dropIfExists('platform_notifications');
        Schema::dropIfExists('api_usage_logs');
        Schema::dropIfExists('platform_api_clients');
        Schema::dropIfExists('ecr_transaction_logs');
        Schema::dropIfExists('ecr_terminal_mappings');
        Schema::dropIfExists('ecr_providers');
    }
};
