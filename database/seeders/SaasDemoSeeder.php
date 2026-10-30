<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\CompanyBranch;
use App\Models\CompanyContact;
use App\Models\CompanyDevice;
use App\Models\CompanySetting;
use App\Models\CompanyStatusHistory;
use App\Models\CompanyTaxSetting;
use App\Models\CompanyTerminal;
use App\Models\CompanyUser;
use App\Models\CompanyWarehouse;
use App\Models\Country;
use App\Models\Currency;
use App\Models\FeatureFlag;
use App\Models\PlatformAdmin;
use App\Models\PlatformAdminLoginLog;
use App\Models\PlatformAdminRole;
use App\Models\PlatformAdminSession;
use App\Models\PlatformAdminUserRole;
use App\Models\PlatformConfig;
use App\Models\PlatformFeature;
use App\Models\PlatformModule;
use App\Models\PlatformPermission;
use App\Models\PlatformRolePermission;
use App\Models\PlatformSetting;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanLimit;
use App\Models\CompanySubscription;
use App\Models\SubscriptionLimitSnapshot;
use App\Models\SubscriptionUsage;
use App\Models\SubscriptionUsageLog;
use App\Models\SubscriptionAddon;
use App\Models\CompanySubscriptionAddon;
use App\Models\SubscriptionTrial;
use App\Models\SubscriptionGracePeriod;
use App\Models\SubscriptionChange;
use App\Models\CompanyLimitOverride;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SaasDemoSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Countries
        |--------------------------------------------------------------------------
        */

        $countries = [
            ['QA', 'Qatar', '+974'],
            ['AE', 'United Arab Emirates', '+971'],
            ['SA', 'Saudi Arabia', '+966'],
            ['IN', 'India', '+91'],
            ['GB', 'United Kingdom', '+44'],
            ['US', 'United States', '+1'],
        ];

        foreach ($countries as [$code, $name, $phone]) {
            Country::updateOrCreate(
                ['country_code' => $code],
                [
                    'uuid' => (string) Str::uuid(),
                    'country_name' => $name,
                    'phone_code' => $phone,
                    'is_active' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Currencies
        |--------------------------------------------------------------------------
        */

        $currencies = [
            ['QAR', 'Qatari Riyal', 'QR'],
            ['USD', 'US Dollar', '$'],
            ['AED', 'UAE Dirham', 'AED'],
            ['INR', 'Indian Rupee', '₹'],
            ['GBP', 'Pound Sterling', '£'],
        ];

        foreach ($currencies as [$code, $name, $symbol]) {
            Currency::updateOrCreate(
                ['currency_code' => $code],
                [
                    'uuid' => (string) Str::uuid(),
                    'currency_name' => $name,
                    'symbol' => $symbol,
                    'decimal_places' => 2,
                    'is_active' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Settings
        |--------------------------------------------------------------------------
        */

        $settings = [
            [
                'default_timezone',
                'Asia/Qatar',
                'string',
                'Applied to new tenants',
                true,
            ],
            [
                'default_language',
                'en',
                'string',
                'Portal language',
                true,
            ],
            [
                'default_country',
                'QA',
                'string',
                'Country pre-selected for sign-up',
                true,
            ],
            [
                'default_currency',
                'QAR',
                'string',
                'Billing currency fallback',
                false,
            ],
            [
                'company_registration_enabled',
                'true',
                'boolean',
                'Allow public vendor sign-up',
                false,
            ],
            [
                'trial_days_default',
                '14',
                'number',
                'Trial length when a plan has none',
                false,
            ],
            [
                'maintenance_mode',
                'false',
                'boolean',
                'Blocks vendor logins platform wide',
                false,
            ],
            [
                'support_email',
                'support@retailpos.cloud',
                'string',
                'Shown in vendor tickets',
                true,
            ],
            [
                'invoice_due_days',
                '7',
                'number',
                'Payment terms on subscription invoices',
                false,
            ],
            [
                'data_retention_default_days',
                '1825',
                'number',
                'Default retention',
                false,
            ],
        ];

        foreach ($settings as [$key, $value, $type, $description, $public]) {
            PlatformSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $value,
                    'value_type' => $type,
                    'description' => $description,
                    'is_public' => $public,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Configs
        |--------------------------------------------------------------------------
        */

        $configs = [
            [
                'database',
                'pool_size',
                ['min' => 5, 'max' => 40],
            ],
            [
                'database',
                'read_replica',
                ['host' => 'ro-1.db.internal', 'weight' => 0.3],
            ],
            [
                'queue',
                'sync_worker_concurrency',
                ['workers' => 12, 'retries' => 5],
            ],
            [
                'cache',
                'ttl_seconds',
                ['products' => 300, 'reports' => 900],
            ],
            [
                'security',
                'rate_limit',
                ['perMinute' => 600, 'perDay' => 250000],
            ],
        ];

        foreach ($configs as [$group, $key, $value]) {
            PlatformConfig::updateOrCreate(
                [
                    'config_group' => $group,
                    'config_key' => $key,
                    'environment' => 'production',
                ],
                [
                    'config_value' => $value,
                    'is_encrypted' => false,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Modules
        |--------------------------------------------------------------------------
        */

        $modules = [
            [
                'RETAIL',
                'Retail',
                'POS, inventory, purchasing, pricing',
                '2.4.1',
                1,
            ],
            [
                'RESTAURANT',
                'Restaurant',
                'Menu, tables, KOT, kitchen, recipes',
                '1.8.0',
                2,
            ],
            [
                'POS',
                'POS Engine',
                'Offline terminals, shifts, payments, hardware',
                '3.1.2',
                3,
            ],
            [
                'ACCOUNTING',
                'Accounting',
                'GL, AR, AP, banking, tax reports',
                '1.5.0',
                4,
            ],
            [
                'ONLINE_ORDERING',
                'Online Ordering',
                'Web store, delivery aggregators',
                '1.2.0',
                5,
            ],
            [
                'LOYALTY',
                'Loyalty',
                'Points, tiers, rewards',
                '1.1.4',
                6,
            ],
            [
                'API',
                'API & Integrations',
                'Clients, webhooks, delivery bridges',
                '1.6.0',
                7,
            ],
        ];

        foreach ($modules as [$code, $name, $description, $version, $order]) {
            PlatformModule::updateOrCreate(
                ['module_code' => $code],
                [
                    'module_name' => $name,
                    'description' => $description,
                    'version' => $version,
                    'is_active' => true,
                    'sort_order' => $order,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Features
        |--------------------------------------------------------------------------
        */

        $features = [
            ['RETAIL', 'MULTI_BRANCH', 'Multi branch'],
            ['RETAIL', 'MULTI_WAREHOUSE', 'Multi warehouse'],
            ['POS', 'OFFLINE_POS', 'Offline POS'],
            ['POS', 'BARCODE_SCALE', 'Barcode scale'],
            ['ACCOUNTING', 'ADVANCED_GL', 'Advanced GL'],
            ['ONLINE_ORDERING', 'DELIVERY_BRIDGE', 'Delivery bridge'],
            ['LOYALTY', 'LOYALTY_TIERS', 'Loyalty tiers'],
        ];

        foreach ($features as [$module, $code, $name]) {
            $m = PlatformModule::where('module_code', $module)->firstOrFail();

            PlatformFeature::updateOrCreate(
                [
                    'module_id' => $m->id,
                    'feature_code' => $code,
                ],
                [
                    'feature_name' => $name,
                    'description' => $name,
                    'is_active' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Feature Flags
        |--------------------------------------------------------------------------
        */

        foreach (PlatformFeature::all() as $feature) {
            FeatureFlag::updateOrCreate(
                ['feature_id' => $feature->id],
                [
                    'flag_key' => strtolower($feature->feature_code) . '_rollout',
                    'is_enabled' => true,
                    'rollout_percentage' => 100,
                    'conditions' => ['seeded' => true],
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Default Country / Currency
        |--------------------------------------------------------------------------
        */

        $qa = Country::where('country_code', 'QA')->firstOrFail();

        $qar = Currency::where('currency_code', 'QAR')->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Phase 05 — Subscription Plans
        |--------------------------------------------------------------------------
        |
        | Plan capacity is stored in subscription_plan_limits.
        | Modules are connected through subscription_plan_modules.
        | Features are connected through subscription_plan_features.
        |
        */

        $retail = PlatformModule::where('module_code', 'RETAIL')->firstOrFail();
        $restaurant = PlatformModule::where('module_code', 'RESTAURANT')->firstOrFail();
        $pos = PlatformModule::where('module_code', 'POS')->firstOrFail();
        $accounting = PlatformModule::where('module_code', 'ACCOUNTING')->firstOrFail();
        $online = PlatformModule::where('module_code', 'ONLINE_ORDERING')->firstOrFail();
        $loyalty = PlatformModule::where('module_code', 'LOYALTY')->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Phase 05 Features
        |--------------------------------------------------------------------------
        */

        $phase5Features = [
            [$retail->id, 'POS_SALES', 'POS Sales'],
            [$retail->id, 'RETURNS', 'Returns'],
            [$retail->id, 'HOLD_BILL', 'Hold Bill'],
            [$retail->id, 'DISCOUNTS', 'Discounts'],
            [$retail->id, 'PRICE_LISTS', 'Price Lists'],
            [$pos->id, 'SPLIT_PAYMENT', 'Split Payment'],
            [$pos->id, 'OFFLINE_POS', 'Offline POS'],
            [$loyalty->id, 'LOYALTY', 'Loyalty'],
        ];

        foreach ($phase5Features as [$moduleId, $code, $name]) {
            PlatformFeature::updateOrCreate(
                [
                    'module_id' => $moduleId,
                    'feature_code' => $code,
                ],
                [
                    'feature_name' => $name,
                    'description' => $name,
                    'is_active' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Subscription Plans
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Use updateOrCreate instead of firstOrCreate + update.
        |
        | The subscription_plans table has NOT NULL columns such as
        | plan_name and currency_id. The old firstOrCreate() attempted
        | to insert the plan before those fields were populated.
        |
        */

        $basic = SubscriptionPlan::updateOrCreate(
            ['plan_code' => 'BASIC'],
            [
                'uuid' => (string) Str::uuid(),
                'plan_name' => 'Basic',
                'description' => 'Essential retail POS for a small business',
                'monthly_price' => 29,
                'quarterly_price' => 84,
                'half_yearly_price' => 162,
                'yearly_price' => 290,
                'currency_id' => $qar->id,
                'trial_days' => 14,
                'grace_days' => 3,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $pro = SubscriptionPlan::updateOrCreate(
            ['plan_code' => 'PRO'],
            [
                'uuid' => (string) Str::uuid(),
                'plan_name' => 'Pro',
                'description' => 'Multi-branch retail and advanced operations',
                'monthly_price' => 79,
                'quarterly_price' => 228,
                'half_yearly_price' => 426,
                'yearly_price' => 790,
                'currency_id' => $qar->id,
                'trial_days' => 14,
                'grace_days' => 7,
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $enterprise = SubscriptionPlan::updateOrCreate(
            ['plan_code' => 'ENTERPRISE'],
            [
                'uuid' => (string) Str::uuid(),
                'plan_name' => 'Enterprise',
                'description' => 'Advanced scale, unlimited capacity and integrations',
                'monthly_price' => 199,
                'quarterly_price' => 576,
                'half_yearly_price' => 1075,
                'yearly_price' => 1990,
                'currency_id' => $qar->id,
                'trial_days' => 30,
                'grace_days' => 14,
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Subscription Plan Limits
        |--------------------------------------------------------------------------
        */

        $planLimits = [
            $basic->id => [
                ['MAX_USERS', 5, false],
                ['MAX_BRANCHES', 1, false],
                ['MAX_WAREHOUSES', 2, false],
                ['MAX_TERMINALS', 2, false],
                ['MAX_DEVICES', 2, false],
                ['MAX_PRODUCTS', 5000, false],
                ['MAX_STORAGE_GB', 5, false],
                ['MAX_API_REQUESTS', 0, false],
                ['MAX_PRICE_LISTS', 2, false],
            ],

            $pro->id => [
                ['MAX_USERS', 25, false],
                ['MAX_BRANCHES', 5, false],
                ['MAX_WAREHOUSES', 10, false],
                ['MAX_TERMINALS', 15, false],
                ['MAX_DEVICES', 25, false],
                ['MAX_PRODUCTS', 20000, false],
                ['MAX_STORAGE_GB', 50, false],
                ['MAX_API_REQUESTS', 1000000, false],
                ['MAX_PRICE_LISTS', 20, false],
            ],

            $enterprise->id => [
                ['MAX_USERS', null, true],
                ['MAX_BRANCHES', null, true],
                ['MAX_WAREHOUSES', null, true],
                ['MAX_TERMINALS', null, true],
                ['MAX_DEVICES', null, true],
                ['MAX_PRODUCTS', null, true],
                ['MAX_STORAGE_GB', null, true],
                ['MAX_API_REQUESTS', null, true],
                ['MAX_PRICE_LISTS', null, true],
            ],
        ];

        foreach ($planLimits as $planId => $limits) {
            foreach ($limits as [$code, $value, $unlimited]) {
                \App\Models\SubscriptionPlanLimit::updateOrCreate(
                    [
                        'plan_id' => $planId,
                        'limit_code' => $code,
                    ],
                    [
                        'limit_value' => $value,
                        'unlimited' => $unlimited,
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Subscription Plan Modules
        |--------------------------------------------------------------------------
        */

        $basic->modules()->sync([
            $retail->id,
        ]);

        $pro->modules()->sync([
            $retail->id,
            $restaurant->id,
            $pos->id,
            $accounting->id,
            $loyalty->id,
        ]);

        $enterprise->modules()->sync([
            $retail->id,
            $restaurant->id,
            $pos->id,
            $accounting->id,
            $online->id,
            $loyalty->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Subscription Plan Features
        |--------------------------------------------------------------------------
        */

        $featureId = function ($moduleId, $code) {
            return PlatformFeature::where('module_id', $moduleId)
                ->where('feature_code', $code)
                ->value('id');
        };

        /*
        | BASIC
        */

        $basic->features()->sync(
            array_values(
                array_filter([
                    $featureId($retail->id, 'POS_SALES'),
                    $featureId($retail->id, 'RETURNS'),
                    $featureId($retail->id, 'HOLD_BILL'),
                    $featureId($retail->id, 'DISCOUNTS'),
                ])
            )
        );

        /*
        | PRO
        */

        $pro->features()->sync(
            array_values(
                array_filter([
                    $featureId($retail->id, 'POS_SALES'),
                    $featureId($retail->id, 'RETURNS'),
                    $featureId($retail->id, 'HOLD_BILL'),
                    $featureId($retail->id, 'DISCOUNTS'),
                    $featureId($pos->id, 'SPLIT_PAYMENT'),
                    $featureId($retail->id, 'PRICE_LISTS'),
                    $featureId($loyalty->id, 'LOYALTY'),
                    $featureId($pos->id, 'OFFLINE_POS'),
                ])
            )
        );

        /*
        | ENTERPRISE
        */

        $enterprise->features()->sync(
            PlatformFeature::whereIn(
                'module_id',
                [
                    $retail->id,
                    $restaurant->id,
                    $pos->id,
                    $accounting->id,
                    $online->id,
                    $loyalty->id,
                ]
            )->pluck('id')->all()
        );

        /*
        |--------------------------------------------------------------------------
        | Platform Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            [
                'company.view',
                'View vendors',
                'Vendors',
                'view',
            ],
            [
                'company.create',
                'Create vendor',
                'Vendors',
                'create',
            ],
            [
                'company.edit',
                'Edit vendor',
                'Vendors',
                'edit',
            ],
            [
                'company.suspend',
                'Suspend vendor',
                'Vendors',
                'suspend',
            ],
            [
                'admin.manage',
                'Manage platform admins',
                'Platform',
                'manage',
            ],
            [
                'platform.settings',
                'Edit platform settings',
                'Platform',
                'manage',
            ],
            [
                'api.manage',
                'Manage API clients',
                'API',
                'manage',
            ],
            [
                'audit.view',
                'View audit and security logs',
                'Security',
                'view',
            ],
        ];

        foreach ($permissions as [$code, $name, $module, $action]) {
            PlatformPermission::updateOrCreate(
                ['permission_code' => $code],
                [
                    'permission_name' => $name,
                    'module' => $module,
                    'action' => $action,
                    'description' => $name,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Admin Roles
        |--------------------------------------------------------------------------
        */

        $roles = [
            [
                'SUPER_ADMIN',
                'Super Admin',
                'Unrestricted platform access',
                true,
            ],
            [
                'SUPPORT_ADMIN',
                'Support Admin',
                'Vendor support',
                true,
            ],
            [
                'TECHNICAL_ADMIN',
                'Technical Admin',
                'API, devices and configs',
                false,
            ],
        ];

        foreach ($roles as [$code, $name, $description, $system]) {
            PlatformAdminRole::updateOrCreate(
                ['role_code' => $code],
                [
                    'uuid' => (string) Str::uuid(),
                    'role_name' => $name,
                    'description' => $description,
                    'is_system_role' => $system,
                    'is_active' => true,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Admins
        |--------------------------------------------------------------------------
        */

        $admins = [
            [
                'adrian.herman',
                'admin@retailpos.cloud',
                'Adrian',
                'Herman',
                'ACTIVE',
            ],
            [
                'linda.cruz',
                'linda@retailpos.cloud',
                'Linda',
                'Cruz',
                'ACTIVE',
            ],
            [
                'ken.tanaka',
                'tech@retailpos.cloud',
                'Ken',
                'Tanaka',
                'ACTIVE',
            ],
        ];

        foreach ($admins as [$username, $email, $first, $last, $status]) {
            $admin = PlatformAdmin::updateOrCreate(
                ['username' => $username],
                [
                    'uuid' => (string) Str::uuid(),
                    'email' => $email,
                    'password_hash' => Hash::make('password'),
                    'first_name' => $first,
                    'last_name' => $last,
                    'status' => $status,
                    'password_changed_at' => now(),
                    'failed_login_attempts' => 0,
                ]
            );

            $role = PlatformAdminRole::where(
                'role_code',
                $username === 'ken.tanaka'
                    ? 'TECHNICAL_ADMIN'
                    : 'SUPER_ADMIN'
            )->firstOrFail();

            PlatformAdminUserRole::firstOrCreate(
                [
                    'admin_id' => $admin->id,
                    'role_id' => $role->id,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin Permissions
        |--------------------------------------------------------------------------
        */

        $super = PlatformAdminRole::where(
            'role_code',
            'SUPER_ADMIN'
        )->firstOrFail();

        foreach (PlatformPermission::all() as $permission) {
            PlatformRolePermission::firstOrCreate(
                [
                    'role_id' => $super->id,
                    'permission_id' => $permission->id,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Admin Session
        |--------------------------------------------------------------------------
        */

        $admin = PlatformAdmin::where(
            'username',
            'adrian.herman'
        )->firstOrFail();

        PlatformAdminSession::updateOrCreate(
            ['token_hash' => 'demo-session-token'],
            [
                'uuid' => (string) Str::uuid(),
                'admin_id' => $admin->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Demo browser',
                'device_info' => ['seeded' => true],
                'expires_at' => now()->addDays(7),
                'last_activity_at' => now(),
                'revoked_at' => null,
                'created_at' => now(),
            ]
        );

        PlatformAdminLoginLog::create([
            'admin_id' => $admin->id,
            'username' => $admin->username,
            'login_status' => 'SUCCESS',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Demo browser',
            'failure_reason' => null,
            'logged_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Demo Company
        |--------------------------------------------------------------------------
        */

        $company = Company::updateOrCreate(
            ['company_code' => 'CMP-001'],
            [
                'uuid' => (string) Str::uuid(),
                'company_name' => 'ABC Supermarket',
                'legal_name' => 'ABC Supermarket WLL',
                'tax_number' => 'QA-TAX-001',
                'country_id' => $qa->id,
                'currency_id' => $qar->id,
                'timezone' => 'Asia/Qatar',
                'status' => 'ACTIVE',
                'database_identifier' => 'tenant_cmp_001',
            ]
        );

        $address = CompanyAddress::updateOrCreate(
            [
                'company_id' => $company->id,
                'address_type' => 'HEAD_OFFICE',
            ],
            [
                'address_line_1' => 'Doha Main Street',
                'city' => 'Doha',
                'state' => 'Doha',
                'postal_code' => '10001',
                'country_id' => $qa->id,
                'is_primary' => true,
            ]
        );

        $branch = CompanyBranch::updateOrCreate(
            [
                'company_id' => $company->id,
                'branch_code' => 'DOHA',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'branch_name' => 'Doha Main',
                'address_id' => $address->id,
                'status' => 'ACTIVE',
            ]
        );

        CompanyWarehouse::updateOrCreate(
            [
                'company_id' => $company->id,
                'warehouse_code' => 'DOHA-MAIN',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'branch_id' => $branch->id,
                'warehouse_name' => 'Doha Main Warehouse',
                'status' => 'ACTIVE',
            ]
        );

        $device = CompanyDevice::updateOrCreate(
            [
                'company_id' => $company->id,
                'device_code' => 'POS-001',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'branch_id' => $branch->id,
                'device_name' => 'POS Counter 1',
                'device_type' => 'POS',
                'platform' => 'Web',
                'app_version' => '1.0.0',
                'last_seen_at' => now(),
                'status' => 'ACTIVE',
                'device_metadata' => ['seeded' => true],
            ]
        );

        CompanyTerminal::updateOrCreate(
            [
                'company_id' => $company->id,
                'terminal_code' => 'T1',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'branch_id' => $branch->id,
                'terminal_name' => 'Doha Counter 1',
                'device_id' => $device->id,
                'status' => 'ACTIVE',
            ]
        );

        CompanyContact::updateOrCreate(
            [
                'company_id' => $company->id,
                'contact_type' => 'PRIMARY',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'contact_name' => 'Store Owner',
                'email' => 'owner@example.com',
                'phone' => '+97440000000',
                'designation' => 'Owner',
                'is_primary' => true,
            ]
        );

        CompanySetting::updateOrCreate(
            [
                'company_id' => $company->id,
                'setting_key' => 'default_timezone',
            ],
            [
                'setting_value' => [
                    'value' => 'Asia/Qatar',
                ],
            ]
        );

        CompanyTaxSetting::updateOrCreate(
            [
                'company_id' => $company->id,
                'country_id' => $qa->id,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'tax_registration_number' => 'QA-TAX-001',
                'tax_system_name' => 'VAT',
                'default_tax_rate' => 5,
                'tax_inclusive' => false,
                'tax_enabled' => true,
                'tax_configuration' => [
                    'rate' => 5,
                ],
                'effective_from' => now()->toDateString(),
                'status' => 'ACTIVE',
            ]
        );

        CompanyStatusHistory::create([
            'company_id' => $company->id,
            'old_status' => null,
            'new_status' => 'ACTIVE',
            'reason' => 'Seeded demo tenant',
            'changed_by' => $admin->id,
            'changed_at' => now(),
        ]);

        CompanyUser::updateOrCreate(
            [
                'company_id' => $company->id,
                'username' => 'owner',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'email' => 'owner@example.com',
                'password_hash' => Hash::make('password'),
                'first_name' => 'Store',
                'last_name' => 'Owner',
                'status' => 'ACTIVE',
                'password_changed_at' => now(),
                'failed_login_attempts' => 0,
            ]
        );

        Company::updateOrCreate(
            ['company_code' => 'CMP-002'],
            [
                'uuid' => (string) Str::uuid(),
                'company_name' => 'Freshmart Electronics',
                'legal_name' => 'Freshmart Electronics LLC',
                'tax_number' => 'QA-TAX-002',
                'country_id' => $qa->id,
                'currency_id' => $qar->id,
                'timezone' => 'Asia/Qatar',
                'status' => 'TRIAL',
                'database_identifier' => 'tenant_cmp_002',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Phase 06 — Company Subscription & Limits
        |--------------------------------------------------------------------------
        */

        $companyTwo = Company::where('company_code', 'CMP-002')->firstOrFail();

        // Keep one historical subscription and one current subscription for the
        // demo tenant so upgrades/changes and limit snapshots are visible.
        $oldSubscription = CompanySubscription::updateOrCreate(
            ['company_id' => $company->id, 'plan_id' => $basic->id, 'status' => 'EXPIRED'],
            [
                'uuid' => (string) Str::uuid(),
                'billing_cycle' => 'MONTHLY',
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->subMonth()->subDay()->toDateString(),
                'auto_renew' => false,
                'price' => $basic->monthly_price,
                'currency_id' => $qar->id,
            ]
        );

        $subscription = CompanySubscription::updateOrCreate(
            ['company_id' => $company->id, 'plan_id' => $pro->id, 'status' => 'ACTIVE'],
            [
                'uuid' => (string) Str::uuid(),
                'billing_cycle' => 'MONTHLY',
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonth()->subDay()->toDateString(),
                'auto_renew' => true,
                'price' => $pro->monthly_price,
                'currency_id' => $qar->id,
            ]
        );

        $trialSubscription = CompanySubscription::updateOrCreate(
            ['company_id' => $companyTwo->id, 'plan_id' => $basic->id, 'status' => 'TRIAL'],
            [
                'uuid' => (string) Str::uuid(),
                'billing_cycle' => 'MONTHLY',
                'start_date' => now()->subDays(7)->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'auto_renew' => true,
                'price' => $basic->monthly_price,
                'currency_id' => $qar->id,
            ]
        );

        // Preserve the plan limits at the time each current subscription was created.
        foreach ([$subscription, $trialSubscription] as $sub) {
            $planLimitsForSubscription = SubscriptionPlanLimit::where('plan_id', $sub->plan_id)->get();
            foreach ($planLimitsForSubscription as $limit) {
                SubscriptionLimitSnapshot::updateOrCreate(
                    [
                        'subscription_id' => $sub->id,
                        'limit_code' => $limit->limit_code,
                        'effective_from' => $sub->start_date->startOfDay(),
                    ],
                    [
                        'limit_value' => $limit->limit_value,
                        'unlimited' => $limit->unlimited,
                        'effective_to' => null,
                    ]
                );
            }
        }

        $usageSeed = [
            [$company->id, $subscription->id, 'CURRENT_USERS', 4],
            [$company->id, $subscription->id, 'CURRENT_BRANCHES', 2],
            [$company->id, $subscription->id, 'CURRENT_WAREHOUSES', 2],
            [$company->id, $subscription->id, 'CURRENT_TERMINALS', 3],
            [$companyTwo->id, $trialSubscription->id, 'CURRENT_USERS', 2],
            [$companyTwo->id, $trialSubscription->id, 'CURRENT_WAREHOUSES', 1],
        ];

        foreach ($usageSeed as [$companyId, $subscriptionId, $code, $value]) {
            SubscriptionUsage::updateOrCreate(
                ['company_id' => $companyId, 'subscription_id' => $subscriptionId, 'usage_code' => $code],
                ['usage_value' => $value, 'last_calculated_at' => now()]
            );
        }

        foreach ($usageSeed as [$companyId, $subscriptionId, $code, $value]) {
            SubscriptionUsageLog::updateOrCreate(
                ['company_id' => $companyId, 'subscription_id' => $subscriptionId, 'usage_code' => $code, 'source' => 'SEED'],
                ['previous_value' => 0, 'new_value' => $value]
            );
        }

        $addons = [
            ['EXTRA_USER', 'Extra User', 'Additional company user capacity', 10],
            ['EXTRA_WAREHOUSE', 'Extra Warehouse', 'Additional warehouse capacity', 15],
            ['EXTRA_BRANCH', 'Extra Branch', 'Additional branch capacity', 25],
            ['EXTRA_TERMINAL', 'Extra Terminal', 'Additional POS terminal capacity', 12],
            ['EXTRA_STORAGE', 'Extra Storage', 'Additional storage capacity', 8],
            ['EXTRA_API', 'Extra API', 'Additional API capacity', 20],
        ];

        $addonModels = [];
        foreach ($addons as [$code, $name, $description, $price]) {
            $addonModels[$code] = SubscriptionAddon::updateOrCreate(
                ['addon_code' => $code],
                [
                    'uuid' => (string) Str::uuid(),
                    'addon_name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'currency_id' => $qar->id,
                    'billing_cycle' => 'MONTHLY',
                    'is_active' => true,
                ]
            );
        }

        CompanySubscriptionAddon::updateOrCreate(
            ['company_subscription_id' => $subscription->id, 'addon_id' => $addonModels['EXTRA_USER']->id],
            [
                'quantity' => 3,
                'unit_price' => $addonModels['EXTRA_USER']->price,
                'start_date' => $subscription->start_date,
                'end_date' => null,
                'status' => 'ACTIVE',
            ]
        );

        SubscriptionTrial::updateOrCreate(
            ['company_id' => $companyTwo->id, 'subscription_id' => $trialSubscription->id],
            [
                'start_date' => $trialSubscription->start_date,
                'end_date' => $trialSubscription->end_date,
                'status' => 'ACTIVE',
                'converted_at' => null,
            ]
        );

        SubscriptionGracePeriod::updateOrCreate(
            ['company_id' => $company->id, 'subscription_id' => $subscription->id, 'start_date' => now()->subDays(2)->toDateString()],
            [
                'end_date' => now()->addDay()->toDateString(),
                'reason' => 'Demo grace period for subscription recovery flow',
                'approved_by' => $admin->id,
            ]
        );

        SubscriptionChange::updateOrCreate(
            [
                'company_id' => $company->id,
                'old_subscription_id' => $oldSubscription->id,
                'new_subscription_id' => $subscription->id,
                'change_type' => 'UPGRADE',
            ],
            [
                'old_plan_id' => $basic->id,
                'new_plan_id' => $pro->id,
                'reason' => 'Demo upgrade from Basic to Pro',
                'changed_by' => $admin->id,
                'changed_at' => now()->subMonth(),
            ]
        );

        CompanyLimitOverride::updateOrCreate(
            [
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'limit_code' => 'MAX_WAREHOUSES',
                'status' => 'ACTIVE',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'base_limit' => 10,
                'override_value' => 2,
                'override_type' => 'INCREASE',
                'reason' => 'Temporary warehouse expansion for demo tenant',
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(3),
                'created_by' => $admin->id,
            ]
        );
    }
}