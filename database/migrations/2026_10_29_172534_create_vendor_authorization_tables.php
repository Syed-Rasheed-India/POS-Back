<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('role_code', 100);
            $table->string('role_name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['company_id', 'role_code']);
            $table->index(['company_id', 'is_active']);
        });

        Schema::create('vendor_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('permission_code', 150)->unique();
            $table->string('permission_name', 150);
            $table->string('module', 100);
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['module', 'action']);
        });

        Schema::create('vendor_user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('company_user_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('company_user_id')->references('id')->on('company_users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('vendor_roles')->cascadeOnDelete();
            $table->foreign('assigned_by')->references('id')->on('company_users')->nullOnDelete();

            $table->unique(['company_user_id', 'role_id']);
            $table->index(['company_id', 'company_user_id']);
            $table->index('expires_at');
        });

        Schema::create('vendor_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('vendor_roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('vendor_permissions')->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('vendor_user_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('company_user_id');
            $table->string('scope_type', 30);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('company_user_id')->references('id')->on('company_users')->cascadeOnDelete();
            $table->foreign('assigned_by')->references('id')->on('company_users')->nullOnDelete();

            $table->index(['company_id', 'company_user_id', 'scope_type']);
            $table->index(['scope_type', 'scope_id']);
            $table->index('expires_at');
        });

        Schema::create('vendor_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('action', 150);
            $table->string('entity_type', 100)->nullable();
            $table->string('entity_id', 100)->nullable();
            $table->jsonb('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('actor_user_id')->references('id')->on('company_users')->nullOnDelete();
            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'action']);
        });

        Schema::create('vendor_login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('company_user_id')->nullable();
            $table->string('username', 100);
            $table->boolean('login_success')->default(false);
            $table->string('failure_reason', 150)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('company_user_id')->references('id')->on('company_users')->nullOnDelete();
            $table->index(['company_id', 'created_at']);
            $table->index(['username', 'created_at']);
            $table->index(['company_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_login_logs');
        Schema::dropIfExists('vendor_audit_logs');
        Schema::dropIfExists('vendor_user_scopes');
        Schema::dropIfExists('vendor_role_permissions');
        Schema::dropIfExists('vendor_user_roles');
        Schema::dropIfExists('vendor_permissions');
        Schema::dropIfExists('vendor_roles');
    }
};
