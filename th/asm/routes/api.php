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
Route::delete('/products/{product_id}/images/{image_id}', [ProductImageController::class, 'destroy']);

Route::post('/products/{product_id}/images', [ProductImageController::class, 'store']);

Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{category_id}', [CategoryController::class, 'destroy']);
Route::get('/categories/{category_id}/products', [CategoryController::class, 'getProductsByCategory']);


Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{id}/approve', [OrderController::class, 'approve']);
Route::patch('/orders/{id}/reject', [OrderController::class, 'reject']);
Route::get('/users', [UserController::class, 'index']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::get('/categories',[CategoryController::class, 'index']);


Route::put('/products/{id}/toggle-noi-bat', [ProductController::class, 'toggleNoiBat']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users', [UserController::class, 'index']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::get('/danh-muc-tin-tuc', [DanhMucTtController::class, 'show']);

