<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Status
use App\Http\Controllers\Api\StatusController;

Route::apiResource('statuses', StatusController::class);

// User
use App\Http\Controllers\Api\UserController;

Route::apiResource('users', UserController::class);

// Page
use App\Http\Controllers\Api\PageController;

Route::apiResource('pages', PageController::class);

// Service
use App\Http\Controllers\Api\ServiceController;

Route::apiResource('services', ServiceController::class);

// Portfolio
use App\Http\Controllers\Api\PortfolioController;

Route::apiResource('portfolios', PortfolioController::class);

// Integration
use App\Http\Controllers\Api\IntegrationController;

Route::apiResource('integrations', IntegrationController::class);

// Testimonial
use App\Http\Controllers\Api\TestimonialController;

Route::apiResource('testimonials', TestimonialController::class);

// Slider
use App\Http\Controllers\Api\SliderController;

Route::apiResource('sliders', SliderController::class);

// UserGroup
use App\Http\Controllers\Api\UserGroupController;

Route::apiResource('usergroups', UserGroupController::class);

// Permission
use App\Http\Controllers\Api\PermissionController;

Route::apiResource('permissions', PermissionController::class);

// Invoice
use App\Http\Controllers\Api\InvoiceController;

Route::apiResource('invoices', InvoiceController::class);

// Transaction
use App\Http\Controllers\Api\TransactionController;

Route::apiResource('transactions', TransactionController::class);

// Category
use App\Http\Controllers\Api\CategoryController;

Route::apiResource('categories', CategoryController::class);

// Product
use App\Http\Controllers\Api\ProductController;

Route::get('products/search', [ProductController::class, 'search']);
Route::get('products/recent', [ProductController::class, 'recent']);
Route::get('products/promotions', [ProductController::class, 'promotions']);
Route::post('products/format-whatsapp', [ProductController::class, 'formatWhatsAppMessage']);
Route::apiResource('products', ProductController::class);

// Facebook Catalog
use App\Http\Controllers\Api\FacebookCatalogController;

Route::post('facebook/sync-catalog', [FacebookCatalogController::class, 'syncCatalog']);
Route::post('facebook/sync-product/{productId}', [FacebookCatalogController::class, 'syncProduct']);
