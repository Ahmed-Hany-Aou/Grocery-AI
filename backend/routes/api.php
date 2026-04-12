<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\InvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| GroceryAI API v1 endpoints for product, category, and invoice management.
|
*/

Route::prefix('v1')->group(function () {
    
    // Public endpoints (no auth required for now)
    
    Route::apiResource('products', ProductController::class);
    Route::get('products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
    Route::post('products/bulk-stock', [ProductController::class, 'bulkStockUpdate']);
    Route::post('products/process-image', [ProductController::class, 'processImage']);
    
    // Categories
    Route::apiResource('categories', CategoryController::class);
    
    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/draft-from-ai', [InvoiceController::class, 'draftFromAi']);
    Route::post('invoices/{invoice}/items', [InvoiceController::class, 'addItem']);
    Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class, 'removeItem']);
    
});

// Fast health check for Railway
Route::get('/status', fn () => response('OK', 200));

// Health check with DB verification
Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'database' => 'disconnected',
            'error' => $e->getMessage()
        ], 500);
    }
});
