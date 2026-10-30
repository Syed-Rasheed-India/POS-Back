<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->string('country_code',2)->unique(); $t->string('country_name',100); $t->string('phone_code',10)->nullable(); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('currencies', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->string('currency_code',3)->unique(); $t->string('currency_name',100); $t->string('symbol',10)->nullable(); $t->unsignedTinyInteger('decimal_places')->default(2); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('platform_settings', function (Blueprint $t) {
            $t->id(); $t->string('setting_key',100)->unique(); $t->text('setting_value')->nullable(); $t->string('value_type',20)->nullable(); $t->text('description')->nullable(); $t->boolean('is_public')->default(false); $t->timestamps();
        });
        Schema::create('platform_configs', function (Blueprint $t) {
            $t->id(); $t->string('config_group',50); $t->string('config_key',100); $t->json('config_value')->nullable(); $t->string('environment',20); $t->boolean('is_encrypted')->default(false); $t->timestamps(); $t->unique(['config_group','config_key','environment']);
        });
        Schema::create('platform_modules', function (Blueprint $t) {
            $t->id(); $t->string('module_code',50)->unique(); $t->string('module_name',100); $t->text('description')->nullable(); $t->string('version',20)->nullable(); $t->boolean('is_active')->default(true); $t->integer('sort_order')->default(0); $t->timestamps();
        });
        Schema::create('platform_features', function (Blueprint $t) {
            $t->id(); $t->foreignId('module_id')->constrained('platform_modules')->cascadeOnDelete(); $t->string('feature_code',80); $t->string('feature_name',150); $t->text('description')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps(); $t->unique(['module_id','feature_code']);
        });
        Schema::create('feature_flags', function (Blueprint $t) {
            $t->id(); $t->foreignId('feature_id')->constrained('platform_features')->cascadeOnDelete(); $t->string('flag_key',100)->unique(); $t->boolean('is_enabled')->default(false); $t->decimal('rollout_percentage',5,2)->default(0); $t->json('conditions')->nullable(); $t->timestamps();
        });
        Schema::create('platform_admin_roles', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->string('role_code',50)->unique(); $t->string('role_name',100); $t->text('description')->nullable(); $t->boolean('is_system_role')->default(false); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('platform_admins', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->string('username',50)->unique(); $t->string('email')->unique(); $t->string('password_hash'); $t->string('first_name',100); $t->string('last_name',100)->nullable(); $t->string('phone',30)->nullable(); $t->string('status',20)->default('ACTIVE'); $t->timestamp('last_login_at')->nullable(); $t->timestamp('password_changed_at')->nullable(); $t->unsignedInteger('failed_login_attempts')->default(0); $t->timestamp('locked_until')->nullable(); $t->timestamp('deleted_at')->nullable(); $t->timestamps();
        });
        Schema::create('platform_permissions', function (Blueprint $t) {
            $t->id(); $t->string('permission_code',100)->unique(); $t->string('permission_name',150); $t->string('module',80)->nullable(); $t->string('action',50)->nullable(); $t->text('description')->nullable(); $t->timestamps();
        });
        Schema::create('platform_role_permissions', function (Blueprint $t) {
            $t->id(); $t->foreignId('role_id')->constrained('platform_admin_roles')->cascadeOnDelete(); $t->foreignId('permission_id')->constrained('platform_permissions')->cascadeOnDelete(); $t->timestamp('created_at')->nullable(); $t->unique(['role_id','permission_id']);
        });
        Schema::create('platform_admin_user_roles', function (Blueprint $t) {
            $t->id(); $t->foreignId('admin_id')->constrained('platform_admins')->cascadeOnDelete(); $t->foreignId('role_id')->constrained('platform_admin_roles')->cascadeOnDelete(); $t->timestamp('created_at')->nullable(); $t->unique(['admin_id','role_id']);
        });
        Schema::create('platform_admin_sessions', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->foreignId('admin_id')->constrained('platform_admins')->cascadeOnDelete(); $t->string('token_hash',255)->unique(); $t->string('ip_address',45)->nullable(); $t->text('user_agent')->nullable(); $t->json('device_info')->nullable(); $t->timestamp('expires_at')->nullable(); $t->timestamp('last_activity_at')->nullable(); $t->timestamp('revoked_at')->nullable(); $t->timestamp('created_at')->nullable();
        });
        Schema::create('platform_admin_login_logs', function (Blueprint $t) {
            $t->id(); $t->foreignId('admin_id')->nullable()->constrained('platform_admins')->nullOnDelete(); $t->string('username',50)->nullable(); $t->string('login_status',20); $t->string('ip_address',45)->nullable(); $t->text('user_agent')->nullable(); $t->string('failure_reason',255)->nullable(); $t->timestamp('logged_at')->useCurrent(); $t->index('logged_at');
        });
        Schema::create('companies', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->string('company_code',50)->unique(); $t->string('company_name',150); $t->string('legal_name',200)->nullable(); $t->string('tax_number',100)->nullable(); $t->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete(); $t->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete(); $t->string('timezone',80); $t->string('status',30)->default('ACTIVE'); $t->string('database_identifier',150)->nullable(); $t->softDeletes(); $t->timestamps();
        });
        Schema::create('company_settings', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->string('setting_key',100); $t->json('setting_value')->nullable(); $t->timestamps(); $t->unique(['company_id','setting_key']);
        });
        Schema::create('company_contacts', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->string('contact_type',50); $t->string('contact_name',150); $t->string('email')->nullable(); $t->string('phone',30)->nullable(); $t->string('designation',100)->nullable(); $t->boolean('is_primary')->default(false); $t->timestamps();
        });
        Schema::create('company_addresses', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->string('address_type',50); $t->string('address_line_1'); $t->string('address_line_2')->nullable(); $t->string('city')->nullable(); $t->string('state')->nullable(); $t->string('postal_code',30)->nullable(); $t->foreignId('country_id')->constrained('countries'); $t->decimal('latitude',10,7)->nullable(); $t->decimal('longitude',10,7)->nullable(); $t->boolean('is_primary')->default(false); $t->timestamps();
        });
        Schema::create('company_status_history', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->string('old_status',30)->nullable(); $t->string('new_status',30); $t->text('reason')->nullable(); $t->unsignedBigInteger('changed_by')->nullable(); $t->timestamp('changed_at')->useCurrent(); $t->index(['company_id','changed_at']);
        });
        Schema::create('company_users', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->string('username',100); $t->string('email')->nullable(); $t->string('password_hash'); $t->string('first_name',100); $t->string('last_name',100)->nullable(); $t->string('phone',30)->nullable(); $t->string('status',30)->default('ACTIVE'); $t->timestamp('last_login_at')->nullable(); $t->timestamp('password_changed_at')->nullable(); $t->unsignedInteger('failed_login_attempts')->default(0); $t->timestamp('locked_until')->nullable(); $t->softDeletes(); $t->timestamps(); $t->unique(['company_id','username']);
        });
        Schema::create('username_rules', function (Blueprint $t) {
            $t->id(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->unsignedTinyInteger('min_length')->default(3); $t->unsignedTinyInteger('max_length')->default(50); $t->boolean('allow_numbers')->default(true); $t->boolean('allow_special_characters')->default(false); $t->boolean('require_unique')->default(true); $t->string('pattern')->nullable(); $t->timestamps();
        });
        Schema::create('company_branches', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->string('branch_code',50); $t->string('branch_name',150); $t->foreignId('address_id')->nullable()->constrained('company_addresses')->nullOnDelete(); $t->string('status',30)->default('ACTIVE'); $t->softDeletes(); $t->timestamps(); $t->unique(['company_id','branch_code']);
        });
        Schema::create('company_warehouses', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->foreignId('branch_id')->nullable()->constrained('company_branches')->nullOnDelete(); $t->string('warehouse_code',50); $t->string('warehouse_name',150); $t->string('status',30)->default('ACTIVE'); $t->softDeletes(); $t->timestamps(); $t->unique(['company_id','warehouse_code']);
        });
        Schema::create('company_devices', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->foreignId('branch_id')->nullable()->constrained('company_branches')->nullOnDelete(); $t->string('device_code',100); $t->string('device_name',150)->nullable(); $t->string('device_type',50); $t->string('platform',50)->nullable(); $t->string('app_version',50)->nullable(); $t->timestamp('last_seen_at')->nullable(); $t->string('status',30)->default('ACTIVE'); $t->json('device_metadata')->nullable(); $t->timestamps(); $t->unique(['company_id','device_code']);
        });
        Schema::create('company_terminals', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->foreignId('branch_id')->constrained('company_branches')->cascadeOnDelete(); $t->string('terminal_code',100); $t->string('terminal_name',150); $t->foreignId('device_id')->nullable()->constrained('company_devices')->nullOnDelete(); $t->string('status',30)->default('ACTIVE'); $t->timestamps(); $t->unique(['company_id','terminal_code']);
        });
        Schema::create('company_tax_settings', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->unique(); $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); $t->foreignId('country_id')->constrained('countries'); $t->string('tax_registration_number',100)->nullable(); $t->string('tax_system_name',100); $t->decimal('default_tax_rate',7,4)->default(0); $t->boolean('tax_inclusive')->default(false); $t->boolean('tax_enabled')->default(true); $t->json('tax_configuration')->nullable(); $t->date('effective_from'); $t->date('effective_to')->nullable(); $t->string('status',30)->default('ACTIVE'); $t->unsignedBigInteger('created_by')->nullable(); $t->unsignedBigInteger('updated_by')->nullable(); $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'company_tax_settings','company_terminals','company_devices','company_warehouses','company_branches','username_rules','company_users','company_status_history','company_addresses','company_contacts','company_settings','companies','platform_admin_login_logs','platform_admin_sessions','platform_admin_user_roles','platform_role_permissions','platform_permissions','platform_admins','platform_admin_roles','feature_flags','platform_features','platform_modules','platform_configs','platform_settings','currencies','countries'
        ] as $table) Schema::dropIfExists($table);
    }
};
