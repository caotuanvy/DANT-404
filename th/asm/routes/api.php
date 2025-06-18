<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DanhMucTtController;
use App\Http\Controllers\Api\SlideShowController;
use App\Http\Controllers\Api\SanPhamBienTheController;
use App\Http\Controllers\Api\TintucController;
use App\Http\Controllers\Api\DiaChiController;

// Auth & User
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/kich-hoat/{token}', [AuthController::class, 'activate']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // User Orders
    Route::get('/user/orders', [OrderController::class, 'userOrders']);
    Route::get('/user/orders/{id}', [OrderController::class, 'getByUser']);

    // Product Variants
    Route::get('/products/{id}/variants', [SanPhamBienTheController::class, 'index']);
    Route::post('/products/{id}/variants', [SanPhamBienTheController::class, 'store']);
    Route::delete('/variants/{id}', [SanPhamBienTheController::class, 'destroy']);

    // Protected Product Images (delete)
    Route::delete('/product-images/{id}', [ProductImageController::class, 'destroy']);
});

// Product
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::put('/products/{id}/toggle-noi-bat', [ProductController::class, 'toggleNoiBat']);

// Product Images
Route::post('/products/{product_id}/images', [ProductImageController::class, 'store']);
Route::delete('/products/{product_id}/images/{image_id}', [ProductImageController::class, 'destroy']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
Route::get('/categories/{id}/products', [CategoryController::class, 'getProductsByCategory']);

// Orders
Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{id}/approve', [OrderController::class, 'approve']);
Route::patch('/orders/{id}/reject', [OrderController::class, 'reject']);
Route::patch('/orders/{id}/hide', [OrderController::class, 'hideOrder']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

// Users
Route::get('/users', [UserController::class, 'index']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::put('/users/{id}', [UserController::class, 'update']);

// Danh mục tin tức
Route::get('/danh-muc-tin-tuc', [DanhMucTtController::class, 'index']);
Route::get('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'show']);
Route::post('/danh-muc-tin-tuc', [DanhMucTtController::class, 'store']);
Route::put('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'update']);
Route::delete('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'destroy']);

// Tin tức
Route::get('/tintuc', [TintucController::class, 'index']);
Route::get('/tintuc/{id}', [TintucController::class, 'show']);
Route::post('/tintuc', [TintucController::class, 'store']);
Route::put('/tintuc/{id}', [TintucController::class, 'update']);
Route::delete('/tintuc/{id}', [TintucController::class, 'destroy']);

// Địa chỉ người dùng
Route::apiResource('addresses', DiaChiController::class);
Route::get('/dia_chi/nguoi_dung/{nguoi_dung_id}', [DiaChiController::class, 'index'])->name('dia_chi.by_user');
Route::post('/dia_chi', [DiaChiController::class, 'store']);
Route::put('/dia_chi/{id}', [DiaChiController::class, 'update']);

// Slide Show (admin)
Route::prefix('admin')->group(function () {
    Route::get('slide', [SlideShowController::class, 'index']);
    Route::get('slide/{id}', [SlideShowController::class, 'show']);
    Route::post('slide/{id}', [SlideShowController::class, 'update']);
});
