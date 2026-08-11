<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\InventorySnapshotController;
use App\Http\Controllers\Api\LogEntryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Licenses\LicenseAccessController;
use App\Http\Controllers\Api\Licenses\LicenseDashboardController;
use App\Http\Controllers\Api\Licenses\LicensePaymentMethodController;
use App\Http\Controllers\Api\Licenses\LicensePlanController;
use App\Http\Controllers\Api\Licenses\LicenseReportController;
use App\Http\Controllers\Api\Licenses\LicenseSubscriptionController;
use App\Http\Controllers\Api\Licenses\LicenseTenantController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('me', [AuthController::class, 'me']);
Route::post('logout', [AuthController::class, 'logout']);

Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'Mi-Stock API',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::apiResource('categories', CategoryController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('branches', BranchController::class);
Route::apiResource('clients', ClientController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('users', UserController::class)->only(['index','show','store','update','destroy']);
Route::apiResource('purchases', PurchaseController::class)->only(['index','show','store','update','destroy']);
Route::apiResource('sales', SaleController::class)->only(['index','show','store','update','destroy']);
Route::apiResource('stock-movements', StockMovementController::class)->only(['index','show','store','update','destroy']);
Route::apiResource('inventory-snapshots', InventorySnapshotController::class)->only(['index','show','store']);
Route::apiResource('logs', LogEntryController::class)->only(['index','show']);

Route::get('reports/sales', [ReportController::class, 'salesSummary']);
Route::get('reports/purchases', [ReportController::class, 'purchaseSummary']);
Route::get('reports/inventory', [ReportController::class, 'inventorySummary']);
Route::get('reports/sales/pdf', [ReportController::class, 'salesPdf']);
Route::get('reports/purchases/pdf', [ReportController::class, 'purchasesPdf']);
Route::get('reports/inventory/pdf', [ReportController::class, 'inventoryPdf']);
Route::get('reports/movements/pdf', [ReportController::class, 'movementsPdf']);
Route::get('logs/export/pdf', [ReportController::class, 'logsPdf']);

Route::prefix('v1/licenses')->group(function () {
    Route::get('public/plans', [LicensePlanController::class, 'publicIndex']);
    Route::get('public/payment-methods', [LicensePaymentMethodController::class, 'publicIndex']);

    Route::apiResource('plans', LicensePlanController::class)->only(['index', 'store', 'show', 'update']);
    Route::patch('plans/{id}/status', [LicensePlanController::class, 'status']);

    Route::apiResource('subscriptions', LicenseSubscriptionController::class)->only(['index', 'show', 'store', 'destroy']);
    Route::post('subscriptions/{id}/renew', [LicenseSubscriptionController::class, 'renew']);
    Route::post('subscriptions/{id}/invoice', [LicenseSubscriptionController::class, 'invoice']);
    Route::post('subscriptions/{id}/remind', [LicenseSubscriptionController::class, 'remind']);

    Route::get('payment-methods', [LicensePaymentMethodController::class, 'index']);
    Route::put('payment-methods', [LicensePaymentMethodController::class, 'update']);

    Route::get('reports/revenue', [LicenseReportController::class, 'revenue']);
    Route::get('dashboard', [LicenseDashboardController::class, 'dashboard']);

    Route::apiResource('tenants', LicenseTenantController::class)->only(['index', 'store', 'show', 'update']);
    Route::patch('tenants/{id}/status', [LicenseTenantController::class, 'status']);
    Route::get('tenants/{id}/payment-history', [LicenseTenantController::class, 'paymentHistory']);
    Route::get('tenants/{id}/invoices', [LicenseTenantController::class, 'invoices']);

    Route::get('check-access', [LicenseAccessController::class, 'checkAccess']);
});

// Public demo summary endpoint (no auth) - lightweight counts and recent activity for demos
Route::get('demo/summary', [\App\Http\Controllers\Api\DemoController::class, 'summary']);
