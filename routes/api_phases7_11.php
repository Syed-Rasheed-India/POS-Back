<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Phase7BillingController;
use App\Http\Controllers\Api\Phase8GatewayController;
use App\Http\Controllers\Api\Phase9EcrController;
use App\Http\Controllers\Api\Phase10ApiClientController;
use App\Http\Controllers\Api\Phase11NotificationController;

Route::prefix('v1')->group(function () {
    // Phase 07: Billing & Payments
    Route::get('/invoices', [Phase7BillingController::class, 'indexInvoices']);
    Route::post('/invoices', [Phase7BillingController::class, 'storeInvoice']);
    Route::get('/invoices/{id}', [Phase7BillingController::class, 'showInvoice']);
    Route::post('/payments', [Phase7BillingController::class, 'recordPayment']);

    // Phase 08: Payment Gateways
    Route::get('/payment-gateways', [Phase8GatewayController::class, 'index']);
    Route::post('/company-payment-gateways', [Phase8GatewayController::class, 'configureCompanyGateway']);

    // Phase 09: ECR Integration
    Route::get('/ecr/providers', [Phase9EcrController::class, 'getProviders']);
    Route::get('/ecr/terminals', [Phase9EcrController::class, 'getTerminals']);
    Route::post('/ecr/terminals', [Phase9EcrController::class, 'mapTerminal']);

    // Phase 10: API Management
    Route::get('/api-clients', [Phase10ApiClientController::class, 'index']);
    Route::post('/api-clients', [Phase10ApiClientController::class, 'store']);

    // Phase 11: Notifications
    Route::get('/notifications', [Phase11NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [Phase11NotificationController::class, 'markRead']);
});
