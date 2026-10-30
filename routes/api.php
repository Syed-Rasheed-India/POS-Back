<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Company\VendorAuthorizationController;

// =====================================================
// CONTROLLERS
// =====================================================

// Authentication
use App\Http\Controllers\Api\PlatformAdminAuthController;
use App\Http\Controllers\Api\CompanyAuthController;

// Countries & currencies
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;

// Platform settings & configuration
use App\Http\Controllers\Api\PlatformSettingController;
use App\Http\Controllers\Api\PlatformConfigController;
use App\Http\Controllers\Api\PlatformModuleController;
use App\Http\Controllers\Api\PlatformFeatureController;
use App\Http\Controllers\Api\FeatureFlagController;

// Platform admin access control
use App\Http\Controllers\Api\PlatformAdminController;
use App\Http\Controllers\Api\PlatformAdminRoleController;
use App\Http\Controllers\Api\PlatformPermissionController;
use App\Http\Controllers\Api\PlatformRolePermissionController;
use App\Http\Controllers\Api\PlatformAdminUserRoleController;
use App\Http\Controllers\Api\PlatformAdminSessionController;
use App\Http\Controllers\Api\PlatformAdminLoginLogController;

// Companies & branches
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompanySettingController;
use App\Http\Controllers\Api\CompanyContactController;
use App\Http\Controllers\Api\CompanyAddressController;
use App\Http\Controllers\Api\CompanyStatusHistoryController;
use App\Http\Controllers\Api\CompanyUserController;
use App\Http\Controllers\Api\UsernameRuleController;
use App\Http\Controllers\Api\CompanyBranchController;
use App\Http\Controllers\Api\CompanyWarehouseController;
use App\Http\Controllers\Api\CompanyDeviceController;
use App\Http\Controllers\Api\CompanyTerminalController;
use App\Http\Controllers\Api\CompanyTaxSettingController;

// Subscription plans
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\SubscriptionPlanLimitController;
use App\Http\Controllers\Api\SubscriptionPlanModuleController;
use App\Http\Controllers\Api\SubscriptionPlanFeatureController;

// Phase 6 subscriptions & limits
use App\Http\Controllers\Api\CompanySubscriptionController;
use App\Http\Controllers\Api\SubscriptionLimitSnapshotController;
use App\Http\Controllers\Api\SubscriptionUsageController;
use App\Http\Controllers\Api\SubscriptionUsageLogController;
use App\Http\Controllers\Api\SubscriptionAddonController;
use App\Http\Controllers\Api\CompanySubscriptionAddonController;
use App\Http\Controllers\Api\SubscriptionTrialController;
use App\Http\Controllers\Api\SubscriptionGracePeriodController;
use App\Http\Controllers\Api\SubscriptionChangeController;
use App\Http\Controllers\Api\CompanyLimitOverrideController;

// Phases 7–11
use App\Http\Controllers\Api\Phase7BillingController;
use App\Http\Controllers\Api\Phase8GatewayController;
use App\Http\Controllers\Api\Phase9EcrController;
use App\Http\Controllers\Api\Phase10ApiClientController;
use App\Http\Controllers\Api\Phase11NotificationController;

// Phases 12–13
use App\Http\Controllers\CompanySupportController;
use App\Http\Controllers\SuperAdminSupportController;
use App\Http\Controllers\SuperAdminSecurityController;

use App\Http\Controllers\Api\Company\BranchController as VendorBranchController;


// =====================================================
// PUBLIC AUTHENTICATION ROUTES
// =====================================================

Route::post('/company/login', [
    CompanyAuthController::class,
    'login',
]);

Route::post('/super-admin/login', [
    PlatformAdminAuthController::class,
    'login',
]);


// =====================================================
// AUTHENTICATED LOGOUT ROUTES
// =====================================================

Route::post('/company/logout', [
    CompanyAuthController::class,
    'logout',
])->middleware('auth:sanctum');

Route::post('/super-admin/logout', [
    PlatformAdminAuthController::class,
    'logout',
])->middleware('auth:sanctum');


// =====================================================
// COMPANY IDENTITY / SESSION VALIDATION
// Requires an authenticated, active company user and company.
// =====================================================

Route::middleware(['auth:sanctum', 'tenant'])
    ->get('/company/me', function (Request $request) {
        $user = $request->user();
        $company = $user->company;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'company_id' => $user->company_id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'status' => $user->status,
            ],
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'status' => $company->status,
            ],
        ]);
    });


// =====================================================
// PUBLIC REFERENCE DATA
// Read-only endpoints retained as public.
// =====================================================

Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{country}', [CountryController::class, 'show']);

Route::get('/currencies', [CurrencyController::class, 'index']);
Route::get('/currencies/{currency}', [CurrencyController::class, 'show']);


// =====================================================
// SUPER ADMIN MANAGEMENT
// Phases 1–6
// =====================================================

Route::middleware(['auth:sanctum', 'super_admin'])->group(function () {

    // Platform settings & configuration
    Route::apiResource('platform-settings', PlatformSettingController::class);
    Route::apiResource('platform-configs', PlatformConfigController::class);
    Route::apiResource('platform-modules', PlatformModuleController::class);
    Route::apiResource('platform-features', PlatformFeatureController::class);
    Route::apiResource('feature-flags', FeatureFlagController::class);

    // Platform admins & access control
    Route::apiResource('platform-admins', PlatformAdminController::class);
    Route::apiResource('platform-admin-roles', PlatformAdminRoleController::class);
    Route::apiResource('platform-permissions', PlatformPermissionController::class);
    Route::apiResource('platform-role-permissions', PlatformRolePermissionController::class);
    Route::apiResource('platform-admin-user-roles', PlatformAdminUserRoleController::class);
    Route::apiResource('platform-admin-sessions', PlatformAdminSessionController::class);
    Route::apiResource('platform-admin-login-logs', PlatformAdminLoginLogController::class);

    // Companies & branches
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('company-settings', CompanySettingController::class);
    Route::apiResource('company-contacts', CompanyContactController::class);
    Route::apiResource('company-addresses', CompanyAddressController::class);
    Route::apiResource('company-status-history', CompanyStatusHistoryController::class);
    Route::apiResource('company-users', CompanyUserController::class);
    Route::apiResource('username-rules', UsernameRuleController::class);
    Route::apiResource('company-branches', CompanyBranchController::class);
    Route::apiResource('company-warehouses', CompanyWarehouseController::class);
    Route::apiResource('company-devices', CompanyDeviceController::class);
    Route::apiResource('company-terminals', CompanyTerminalController::class);
    Route::apiResource('company-tax-settings', CompanyTaxSettingController::class);

    // Subscription plans
    Route::apiResource('subscription-plans', SubscriptionPlanController::class);
    Route::apiResource('subscription-plan-limits', SubscriptionPlanLimitController::class);
    Route::apiResource('subscription-plan-modules', SubscriptionPlanModuleController::class);
    Route::apiResource('subscription-plan-features', SubscriptionPlanFeatureController::class);

    // Company subscriptions & limits
    Route::apiResource('company-subscriptions', CompanySubscriptionController::class);
    Route::apiResource('subscription-limit-snapshots', SubscriptionLimitSnapshotController::class);
    Route::apiResource('subscription-usage', SubscriptionUsageController::class);
    Route::apiResource('subscription-usage-logs', SubscriptionUsageLogController::class);
    Route::apiResource('subscription-addons', SubscriptionAddonController::class);
    Route::apiResource('company-subscription-addons', CompanySubscriptionAddonController::class);
    Route::apiResource('subscription-trials', SubscriptionTrialController::class);
    Route::apiResource('subscription-grace-periods', SubscriptionGracePeriodController::class);
    Route::apiResource('subscription-changes', SubscriptionChangeController::class);
    Route::apiResource('company-limit-overrides', CompanyLimitOverrideController::class);

    // Phase 07: Billing & payments
    Route::get('/invoices', [Phase7BillingController::class, 'indexInvoices']);
    Route::post('/invoices', [Phase7BillingController::class, 'storeInvoice']);
    Route::get('/invoices/{id}', [Phase7BillingController::class, 'showInvoice'])
        ->whereNumber('id');
    Route::post('/payments', [Phase7BillingController::class, 'recordPayment']);

    // Phase 08: Payment gateways
    Route::get('/payment-gateways', [Phase8GatewayController::class, 'index']);
    Route::post('/company-payment-gateways', [Phase8GatewayController::class, 'configureCompanyGateway']);

    // Phase 09: ECR integration
    Route::get('/ecr/providers', [Phase9EcrController::class, 'getProviders']);
    Route::get('/ecr/terminals', [Phase9EcrController::class, 'getTerminals']);
    Route::post('/ecr/terminals', [Phase9EcrController::class, 'mapTerminal']);

    // Phase 10: API management
    Route::get('/api-clients', [Phase10ApiClientController::class, 'index']);
    Route::post('/api-clients', [Phase10ApiClientController::class, 'store']);

    // Phase 11: Notifications
    Route::get('/notifications', [Phase11NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [Phase11NotificationController::class, 'markRead'])
        ->whereNumber('id');
});


// =====================================================
// COMPANY BRANCHES
// =====================================================

Route::middleware(['auth:sanctum', 'tenant'])
    ->prefix('company/branches')
    ->group(function () {
        Route::get('/', [VendorBranchController::class, 'index']);
        Route::post('/', [VendorBranchController::class, 'store']);
        Route::get('/{id}', [VendorBranchController::class, 'show']);
        Route::put('/{id}', [VendorBranchController::class, 'update']);
        Route::patch('/{id}', [VendorBranchController::class, 'update']);
        Route::delete('/{id}', [VendorBranchController::class, 'destroy']);
    });

// =====================================================
// VENDOR ROLE & PERMISSION MANAGEMENT
// =====================================================

Route::middleware(['auth:sanctum', 'tenant', 'vendor.permission:roles.view'])
    ->prefix('company')
    ->group(function () {

        Route::get('/roles', [
            VendorAuthorizationController::class,
            'roles',
        ]);

        Route::get('/permissions', [
            VendorAuthorizationController::class,
            'permissions',
        ]);

        Route::get('/users/{userId}/roles', [
            VendorAuthorizationController::class,
            'userRoles',
        ])->whereNumber('userId');

        Route::post('/users/{userId}/roles', [
            VendorAuthorizationController::class,
            'assignRole',
        ])->whereNumber('userId');

        Route::delete('/users/{userId}/roles/{roleId}', [
            VendorAuthorizationController::class,
            'revokeRole',
        ])->whereNumber('userId')
          ->whereNumber('roleId');
    });

// =====================================================
// PHASE 12: COMPANY SUPPORT
// =====================================================

Route::middleware(['auth:sanctum', 'tenant'])
    ->prefix('company/support')
    ->group(function () {

        Route::get('/tickets', [
            CompanySupportController::class,
            'index',
        ]);

        Route::post('/tickets', [
            CompanySupportController::class,
            'store',
        ]);

        Route::get('/tickets/{uuid}', [
            CompanySupportController::class,
            'show',
        ]);

        Route::post('/tickets/{uuid}/messages', [
            CompanySupportController::class,
            'reply',
        ]);

        Route::post('/tickets/{uuid}/attachments', [
            CompanySupportController::class,
            'uploadAttachment',
        ]);

        Route::get('/tickets/{uuid}/attachments/{attachmentUuid}', [
            CompanySupportController::class,
            'downloadAttachment',
        ]);

        Route::post('/tickets/{uuid}/reopen', [
            CompanySupportController::class,
            'reopen',
        ]);

        Route::post('/tickets/{uuid}/rating', [
            CompanySupportController::class,
            'submitRating',
        ]);
    });


// =====================================================
// PHASES 12–13: SUPER ADMIN SUPPORT & SECURITY
// =====================================================

Route::middleware(['auth:sanctum', 'super_admin'])
    ->prefix('super-admin')
    ->group(function () {

        // Validate the current Super Admin session.
        Route::get('/me', function (Request $request) {
            $admin = $request->user();

            return response()->json([
                'admin' => [
                    'id' => $admin->id,
                    'uuid' => $admin->uuid,
                    'username' => $admin->username,
                    'email' => $admin->email,
                    'first_name' => $admin->first_name,
                    'last_name' => $admin->last_name,
                    'status' => $admin->status,
                ],
            ]);
        });

        // Support tickets
        Route::get('/support/tickets', [
            SuperAdminSupportController::class,
            'index',
        ]);

        Route::get('/support/tickets/{uuid}', [
            SuperAdminSupportController::class,
            'show',
        ]);

        Route::put('/support/tickets/{uuid}/assign', [
            SuperAdminSupportController::class,
            'assign',
        ]);

        Route::put('/support/tickets/{uuid}/status', [
            SuperAdminSupportController::class,
            'updateStatus',
        ]);

        Route::put('/support/tickets/{uuid}/priority', [
            SuperAdminSupportController::class,
            'updatePriority',
        ]);

        Route::post('/support/tickets/{uuid}/internal-notes', [
            SuperAdminSupportController::class,
            'addInternalNote',
        ]);

        Route::get('/support/reports', [
            SuperAdminSupportController::class,
            'reports',
        ]);

        Route::get('/support/tickets/{uuid}/attachments/{attachmentUuid}', [
            SuperAdminSupportController::class,
            'downloadAttachment',
        ]);

        Route::post('/support/tickets/{uuid}/messages', [
            SuperAdminSupportController::class,
            'reply',
        ]);

        // Security dashboard & events
        Route::get('/security/dashboard', [
            SuperAdminSecurityController::class,
            'dashboard',
        ]);

        Route::get('/security/events', [
            SuperAdminSecurityController::class,
            'events',
        ]);

        Route::get('/security/events/{uuid}', [
            SuperAdminSecurityController::class,
            'showEvent',
        ]);

        Route::put('/security/events/{uuid}/assign', [
            SuperAdminSecurityController::class,
            'assignEvent',
        ]);

        Route::put('/security/events/{uuid}/status', [
            SuperAdminSecurityController::class,
            'updateEventStatus',
        ]);

        Route::post('/security/events/{uuid}/resolve', [
            SuperAdminSecurityController::class,
            'resolveEvent',
        ]);

        // Audit logs
        Route::get('/security/audit-logs', [
            SuperAdminSecurityController::class,
            'auditLogs',
        ]);

        Route::get('/audit-logs', [
            SuperAdminSecurityController::class,
            'auditLogs',
        ]);

        // Login history & sessions
        Route::get('/security/login-history', [
            SuperAdminSecurityController::class,
            'loginHistory',
        ]);

        Route::get('/security/sessions', [
            SuperAdminSecurityController::class,
            'sessions',
        ]);

        Route::delete('/security/sessions/{uuid}', [
            SuperAdminSecurityController::class,
            'revokeSession',
        ]);

        // API tokens
        Route::get('/security/tokens', [
            SuperAdminSecurityController::class,
            'tokens',
        ]);

        Route::delete('/security/tokens/{id}', [
            SuperAdminSecurityController::class,
            'revokeToken',
        ])->whereNumber('id');

        // Two-factor authentication & settings
        Route::get('/security/two-factor', [
            SuperAdminSecurityController::class,
            'twoFactorStatus',
        ]);

        Route::get('/security/settings', [
            SuperAdminSecurityController::class,
            'securitySettings',
        ]);

        Route::put('/security/settings', [
            SuperAdminSecurityController::class,
            'updateSecuritySettings',
        ]);
    });
