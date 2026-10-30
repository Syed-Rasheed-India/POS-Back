<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompanyLoginSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(
            'sqlite',
            DB::connection()->getDriverName(),
            'These tests must not run against PostgreSQL.'
        );

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('company_users');
        Schema::dropIfExists('companies');

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
    }

    
private function createCompany(string $code = 'COMP001', string $status = 'ACTIVE'): Company
{
    $company = new Company();

    $company->forceFill([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'company_code' => $code,
        'company_name' => $code . ' Company',
        'timezone' => 'UTC',
        'status' => $status,
    ]);

    $company->save();

    return $company;
}


    private function createCompanyUser(
        Company $company,
        string $status = 'ACTIVE',
        int $failedAttempts = 0,
        ?\Illuminate\Support\Carbon $lockedUntil = null
    ): CompanyUser {
        return CompanyUser::query()->create([
            'company_id' => $company->id,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => Hash::make('CorrectPassword123!'),
            'first_name' => 'Test',
            'last_name' => 'User',
            'status' => $status,
            'failed_login_attempts' => $failedAttempts,
            'locked_until' => $lockedUntil,
        ]);
    }

    private function login(string $username = 'testuser', string $password = 'CorrectPassword123!')
    {
        return $this->postJson('/api/company/login', [
            'username' => $username,
            'password' => $password,
        ]);
    }

    public function test_incorrect_password_increments_failed_attempts(): void
    {
        $company = $this->createCompany();
        $user = $this->createCompanyUser($company);

        $this->login('testuser', 'WrongPassword123!')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Invalid username or password.',
            ]);

        $this->assertSame(
            1,
            (int) $user->fresh()->failed_login_attempts
        );

        $this->assertNull($user->fresh()->locked_until);
    }

    public function test_fifth_failed_attempt_locks_account_for_fifteen_minutes(): void
    {
        $company = $this->createCompany();
        $user = $this->createCompanyUser($company);

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->login('testuser', 'WrongPassword123!')
                ->assertUnauthorized();
        }

        $this->login('testuser', 'WrongPassword123!')
            ->assertStatus(423)
            ->assertJson([
                'message' => 'This account is temporarily locked.',
            ]);

        $user->refresh();

        $this->assertSame(5, (int) $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());
        $this->assertTrue(
            $user->locked_until->between(
                now()->addMinutes(14),
                now()->addMinutes(16)
            )
        );
    }

    public function test_correct_password_cannot_bypass_active_lock(): void
    {
        $company = $this->createCompany();
        $user = $this->createCompanyUser(
            $company,
            'ACTIVE',
            5,
            now()->addMinutes(15)
        );

        $this->login()
            ->assertStatus(423)
            ->assertJson([
                'message' => 'This account is temporarily locked.',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => $user->getMorphClass(),
        ]);
    }

    public function test_inactive_company_user_cannot_log_in(): void
    {
        $company = $this->createCompany();
        $this->createCompanyUser($company, 'INACTIVE');

        $this->login()
            ->assertForbidden()
            ->assertJson([
                'message' => 'This company account is inactive.',
            ]);
    }

    public function test_user_of_inactive_company_cannot_log_in(): void
    {
        $company = $this->createCompany('COMP002', 'INACTIVE');
        $this->createCompanyUser($company);

        $this->login()
            ->assertForbidden()
            ->assertJson([
                'message' => 'This company account is inactive or unavailable.',
            ]);
    }

    public function test_user_of_deleted_company_cannot_log_in(): void
    {
        $company = $this->createCompany('COMP003');
        $this->createCompanyUser($company);

        $company->delete();

        $this->login()
            ->assertForbidden()
            ->assertJson([
                'message' => 'This company account is inactive or unavailable.',
            ]);
    }

    public function test_successful_login_resets_failed_attempts_and_returns_token(): void
    {
        $company = $this->createCompany();
        $user = $this->createCompanyUser($company, 'ACTIVE', 3);

        $this->login()
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'token_type',
                'user' => [
                    'id',
                    'uuid',
                    'company_id',
                    'username',
                    'email',
                    'first_name',
                    'last_name',
                    'status',
                ],
            ])
            ->assertJson([
                'message' => 'Login successful.',
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'company_id' => $company->id,
                    'username' => 'testuser',
                    'status' => 'ACTIVE',
                ],
            ]);

        $user->refresh();

        $this->assertSame(0, (int) $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
        $this->assertNotNull($user->last_login_at);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => $user->getMorphClass(),
            'name' => 'retailpos-company',
        ]);
    }
}
