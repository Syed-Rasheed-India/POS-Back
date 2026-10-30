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

class CrossRoleAccessSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Security tests must run only against SQLite.
        $this->assertSame(
            'sqlite',
            DB::connection()->getDriverName(),
            'These tests must not run against PostgreSQL.'
        );

        foreach ([
            'company_branches',
            'support_tickets',
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

        // Minimal fixture for SuperAdminSupportController pagination.
        // This table exists only in the in-memory SQLite test database.
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });

        // Minimal fixture for Super Admin company-branch listing.
        // This table exists only in the in-memory SQLite test database.
        Schema::create('company_branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('branch_code', 50);
            $table->string('branch_name', 150);
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function createCompanyUser(): CompanyUser
    {
        $company = new Company();

        $company->forceFill([
            'uuid' => (string) Str::uuid(),
            'company_code' => 'COMP001',
            'company_name' => 'Test Company',
            'timezone' => 'UTC',
            'status' => 'ACTIVE',
        ]);

        $company->save();

        $user = new CompanyUser();

        $user->forceFill([
            'uuid' => (string) Str::uuid(),
            'company_id' => $company->id,
            'username' => 'vendor-user',
            'email' => 'vendor@example.com',
            'password_hash' => Hash::make('TestPassword123!'),
            'first_name' => 'Vendor',
            'last_name' => 'User',
            'status' => 'ACTIVE',
            'failed_login_attempts' => 0,
        ]);

        $user->save();

        return $user;
    }

    private function createSuperAdmin(
        bool $activeRole = true
    ): PlatformAdmin {
        $admin = new PlatformAdmin();

        $admin->forceFill([
            'uuid' => (string) Str::uuid(),
            'username' => 'super-admin',
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('TestPassword123!'),
            'first_name' => 'Platform',
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
            'is_active' => $activeRole,
        ]);

        $role->save();

        DB::table('platform_admin_user_roles')->insert([
            'admin_id' => $admin->id,
            'role_id' => $role->id,
            'created_at' => now(),
        ]);

        return $admin;
    }

    public function test_company_user_cannot_access_super_admin_support(): void
    {
        $user = $this->createCompanyUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/super-admin/support/tickets')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_super_admin_cannot_access_vendor_branches(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/company/branches')
            ->assertForbidden()
            ->assertJson([
                'message' => 'A company user session is required.',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_super_admin_support(): void
    {
        $this->getJson('/api/super-admin/support/tickets')
            ->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_access_vendor_branches(): void
    {
        $this->getJson('/api/company/branches')
            ->assertUnauthorized();
    }

    public function test_active_super_admin_with_active_role_can_access_super_admin_support(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/super-admin/support/tickets')
            ->assertOk();
    }

    public function test_admin_without_active_super_admin_role_is_rejected(): void
    {
        $admin = $this->createSuperAdmin(activeRole: false);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/super-admin/support/tickets')
            ->assertForbidden()
            ->assertJson([
                'message' => 'Super Admin access is required.',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_company_management(): void
    {
        $this->getJson('/api/companies')
            ->assertUnauthorized();

        $this->getJson('/api/company-branches')
            ->assertUnauthorized();
    }

    public function test_company_user_cannot_access_company_management(): void
    {
        $user = $this->createCompanyUser();

        // EnsureSuperAdmin returns 401 for a non-PlatformAdmin model.
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/companies')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/company-branches')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_company_user_cannot_create_company_or_branch_management_records(): void
    {
        $user = $this->createCompanyUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/companies', [])
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/company-branches', [])
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_super_admin_can_access_company_management_lists(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/companies')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/company-branches')
            ->assertOk();
    }
}
