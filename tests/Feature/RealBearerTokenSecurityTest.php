<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\PlatformAdmin;
use App\Models\PlatformAdminRole;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class RealBearerTokenSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'sqlite',
            DB::connection()->getDriverName(),
            'These tests must never run against PostgreSQL.'
        );

        foreach ([
            'support_tickets',
            'company_branches',
            'platform_admin_login_logs',
            'personal_access_tokens',
            'platform_admin_user_roles',
            'platform_admin_roles',
            'platform_admins',
            'company_users',
            'companies',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('company_code')->unique();
            $table->string('company_name');
            $table->string('timezone')->default('UTC');
            $table->string('status')->default('ACTIVE');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('company_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id');
            $table->string('username');
            $table->string('email')->nullable();
            $table->string('password_hash');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('platform_admin_roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('role_code', 50)->unique();
            $table->string('role_name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_system_role')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('platform_admins', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('platform_admin_user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamp('created_at')->nullable();
            $table->unique(['admin_id', 'role_id']);
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->unsignedBigInteger('company_id');
            $table->string('branch_code', 50);
            $table->string('branch_name', 150);
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        // The login controller logs audit events inside a try/catch.
        // Create the expected schema so the successful path is tested too.
        Schema::create('platform_admin_login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('username');
            $table->string('login_status');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('logged_at');
        });
    }

    private function createCompanyUser(): CompanyUser
    {
        $company = new Company();

        $company->forceFill([
            'uuid' => (string) Str::uuid(),
            'company_code' => 'TOKEN-COMPANY',
            'company_name' => 'Token Test Company',
            'timezone' => 'UTC',
            'status' => 'ACTIVE',
        ]);

        $company->save();

        $user = new CompanyUser();

        $user->forceFill([
            'uuid' => (string) Str::uuid(),
            'company_id' => $company->id,
            'username' => 'token-vendor',
            'email' => 'token-vendor@example.com',
            'password_hash' => Hash::make('TestPassword123!'),
            'first_name' => 'Token',
            'last_name' => 'Vendor',
            'status' => 'ACTIVE',
            'failed_login_attempts' => 0,
        ]);

        $user->save();

        return $user;
    }

    private function createSuperAdmin(): PlatformAdmin
    {
        $admin = new PlatformAdmin();

        $admin->forceFill([
            'uuid' => (string) Str::uuid(),
            'username' => 'token-admin',
            'email' => 'token-admin@example.com',
            'password_hash' => Hash::make('TestPassword123!'),
            'first_name' => 'Token',
            'last_name' => 'Admin',
            'status' => 'ACTIVE',
            'failed_login_attempts' => 0,
        ]);

        $admin->save();

        $role = new PlatformAdminRole();

        $role->forceFill([
            'uuid' => (string) Str::uuid(),
            'role_code' => 'SUPER_ADMIN',
            'role_name' => 'Super Admin',
            'is_system_role' => true,
            'is_active' => true,
        ]);

        $role->save();

        DB::table('platform_admin_user_roles')->insert([
            'admin_id' => $admin->id,
            'role_id' => $role->id,
            'created_at' => now(),
        ]);

        return $admin;
    }

    private function companyToken(CompanyUser $user): string
    {
        $response = $this->postJson('/api/company/login', [
            'username' => 'token-vendor',
            'password' => 'TestPassword123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer');

        $token = $response->json('token');

        $this->assertIsString($token);
        $this->assertNotSame('', $token);

        return $token;
    }

    private function superAdminToken(): string
    {
        $response = $this->postJson('/api/super-admin/login', [
            'username' => 'token-admin',
            'password' => 'TestPassword123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer');

        $token = $response->json('token');

        $this->assertIsString($token);
        $this->assertNotSame('', $token);

        return $token;
    }

    public function test_company_login_token_can_access_vendor_branches(): void
    {
        $user = $this->createCompanyUser();
        $token = $this->companyToken($user);

        $this->withToken($token)
            ->getJson('/api/company/branches')
            ->assertOk();
    }

    public function test_company_login_token_cannot_access_super_admin_support(): void
    {
        $user = $this->createCompanyUser();
        $token = $this->companyToken($user);

        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/super-admin/support/tickets')
            ->assertUnauthorized();
    }

    public function test_super_admin_login_token_can_access_super_admin_support(): void
    {
        $this->createSuperAdmin();
        $token = $this->superAdminToken();

        $this->withToken($token)
            ->getJson('/api/super-admin/support/tickets')
            ->assertOk();
    }

    public function test_super_admin_login_token_cannot_access_vendor_branches(): void
    {
        $this->createSuperAdmin();
        $token = $this->superAdminToken();

        $this->withToken($token)
            ->getJson('/api/company/branches')
            ->assertForbidden()
            ->assertJson([
                'message' => 'A company user session is required.',
            ]);
    }

    public function test_invalid_bearer_token_is_rejected(): void
    {
        $this->withToken('not-a-real-sanctum-token')
            ->getJson('/api/company/branches')
            ->assertUnauthorized();
    }

    public function test_revoked_company_token_is_rejected(): void
    {
        $user = $this->createCompanyUser();
        $token = $this->companyToken($user);

        $user->tokens()->delete();

        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/company/branches')
            ->assertUnauthorized();
    }

    public function test_logout_revokes_company_token(): void
    {
        $user = $this->createCompanyUser();
        $token = $this->companyToken($user);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)
            ->postJson('/api/company/logout')
            ->assertOk()
            ->assertJson([
                'message' => 'Logout successful.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/company/branches')
            ->assertUnauthorized();
    }

    public function test_logout_revokes_super_admin_token(): void
    {
        $this->createSuperAdmin();
        $token = $this->superAdminToken();

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)
            ->postJson('/api/super-admin/logout')
            ->assertOk()
            ->assertJson([
                'message' => 'Logout successful.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/super-admin/support/tickets')
            ->assertUnauthorized();
    }
}
