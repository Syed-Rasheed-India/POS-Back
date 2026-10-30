<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureTenant;
use App\Models\CompanyUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantBranchSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // These tests must use the in-memory SQLite connection.
        $this->assertSame(
            'sqlite',
            DB::connection()->getDriverName(),
            'Tenant security tests must not run against PostgreSQL.'
        );

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

        Schema::create('company_branches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id');
            $table->string('branch_code');
            $table->string('branch_name');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('company_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('address_type');
            $table->string('address_line_1');
            $table->timestamps();
        });
    }

    private function createCompany(
        string $code,
        string $status = 'ACTIVE'
    ): int {
        return DB::table('companies')->insertGetId([
            'uuid' => fake()->uuid(),
            'company_code' => $code,
            'company_name' => $code,
            'timezone' => 'Asia/Kolkata',
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCompanyUser(
        int $companyId,
        string $status = 'ACTIVE'
    ): CompanyUser {
        $id = DB::table('company_users')->insertGetId([
            'uuid' => fake()->uuid(),
            'company_id' => $companyId,
            'username' => 'user_' . fake()->unique()->numerify('#####'),
            'password_hash' => bcrypt('test-password'),
            'first_name' => 'Test',
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return CompanyUser::findOrFail($id);
    }

    private function createBranch(
        int $companyId,
        string $code = 'BR001'
    ): int {
        return DB::table('company_branches')->insertGetId([
            'uuid' => fake()->uuid(),
            'company_id' => $companyId,
            'branch_code' => $code,
            'branch_name' => 'Test Branch',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_unauthenticated_user_cannot_list_branches(): void
    {
        $this->getJson('/api/company/branches')
            ->assertUnauthorized();
    }

    public function test_company_user_cannot_view_another_company_branch(): void
    {
        $companyA = $this->createCompany('COMPANY_A');
        $companyB = $this->createCompany('COMPANY_B');

        $userA = $this->createCompanyUser($companyA);
        $branchB = $this->createBranch($companyB);

        $this->actingAs($userA, 'sanctum')
            ->getJson("/api/company/branches/{$branchB}")
            ->assertNotFound();
    }

    public function test_company_user_cannot_update_another_company_branch(): void
    {
        $companyA = $this->createCompany('COMPANY_A');
        $companyB = $this->createCompany('COMPANY_B');

        $userA = $this->createCompanyUser($companyA);
        $branchB = $this->createBranch($companyB);

        $this->actingAs($userA, 'sanctum')
            ->putJson("/api/company/branches/{$branchB}", [
                'branch_name' => 'Unauthorized Change',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('company_branches', [
            'id' => $branchB,
            'branch_name' => 'Test Branch',
        ]);
    }

    public function test_company_user_cannot_delete_another_company_branch(): void
    {
        $companyA = $this->createCompany('COMPANY_A');
        $companyB = $this->createCompany('COMPANY_B');

        $userA = $this->createCompanyUser($companyA);
        $branchB = $this->createBranch($companyB);

        $this->actingAs($userA, 'sanctum')
            ->deleteJson("/api/company/branches/{$branchB}")
            ->assertNotFound();

        $this->assertDatabaseHas('company_branches', [
            'id' => $branchB,
            'deleted_at' => null,
        ]);
    }

    public function test_inactive_company_user_is_blocked(): void
    {
        $companyId = $this->createCompany('INACTIVE_USER_TEST');
        $user = $this->createCompanyUser($companyId, 'INACTIVE');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/company/branches')
            ->assertForbidden();
    }

    public function test_user_of_inactive_company_is_blocked(): void
    {
        $companyId = $this->createCompany(
            'INACTIVE_COMPANY_TEST',
            'INACTIVE'
        );

        $user = $this->createCompanyUser($companyId);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/company/branches')
            ->assertForbidden();
    }
}
