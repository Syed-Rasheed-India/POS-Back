<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // The original project archive references these named tables but has no
        // migrations for them. Create only when absent; retain any pre-existing data.
        if (!Schema::hasTable('platform_security_events')) {
            Schema::create('platform_security_events', function (Blueprint $table) {
                $table->id(); $table->uuid('uuid')->unique(); $table->string('event_code', 80)->index();
                $table->string('severity', 20)->default('MEDIUM')->index(); $table->string('status', 20)->default('OPEN')->index();
                $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->foreignId('company_user_id')->nullable()->constrained('company_users')->nullOnDelete();
                $table->string('source_ip', 45)->nullable()->index(); $table->text('user_agent')->nullable();
                $table->text('description'); $table->json('event_metadata')->nullable();
                $table->foreignId('assigned_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->text('resolution_notes')->nullable(); $table->timestamp('occurred_at')->useCurrent()->index();
                $table->timestamp('resolved_at')->nullable(); $table->timestamps();
                $table->index(['company_id', 'occurred_at']);
            });
        }
        if (!Schema::hasTable('platform_audit_logs')) {
            Schema::create('platform_audit_logs', function (Blueprint $table) {
                $table->id(); $table->uuid('uuid')->unique(); $table->string('actor_type', 30)->index();
                $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
                $table->foreignId('company_user_id')->nullable()->constrained('company_users')->nullOnDelete();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->string('action_code', 100)->index(); $table->string('entity_type', 100)->index();
                $table->unsignedBigInteger('entity_id')->nullable()->index(); $table->uuid('entity_uuid')->nullable()->index();
                $table->text('description')->nullable(); $table->json('old_values')->nullable(); $table->json('new_values')->nullable();
                $table->uuid('request_id')->nullable()->index(); $table->string('source_ip', 45)->nullable()->index();
                $table->text('user_agent')->nullable(); $table->timestamp('created_at')->useCurrent()->index();
                $table->index(['company_id', 'created_at']); $table->index(['actor_type', 'action_code', 'created_at']);
                $table->index(['entity_type', 'entity_id', 'created_at']);
            });
        }
        if (!Schema::hasTable('two_factor_auth')) {
            Schema::create('two_factor_auth', function (Blueprint $table) {
                $table->id(); $table->uuid('uuid')->unique();
                $table->foreignId('platform_admin_id')->constrained('platform_admins')->cascadeOnDelete();
                $table->string('method', 30)->default('TOTP'); $table->text('secret_encrypted')->nullable();
                $table->boolean('is_enabled')->default(false); $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('last_verified_at')->nullable(); $table->timestamps();
                $table->unique(['platform_admin_id', 'method']);
            });
        }
    }

    // These shared security records may predate this migration. Keep them on rollback.
    public function down(): void {}
};
