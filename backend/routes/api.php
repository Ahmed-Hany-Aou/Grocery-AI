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
    
    // Products
    Route::apiResource('products', ProductController::class);
    Route::get('products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
    
    // Categories
    Route::apiResource('categories', CategoryController::class);
    
    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/items', [InvoiceController::class, 'addItem']);
    Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class, 'removeItem']);
    
});

// Health check
Route::get('/health', fn () => response()->json(['status' => 'ok', 'version' => '1.0.0']));
