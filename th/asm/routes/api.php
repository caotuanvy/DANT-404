<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ProductController,
    AuthController,
    CategoryController,
    ProductImageController,
    OrderController,
    UserController,
    DanhMucTtController,
    SlideShowController,
    SanPhamBienTheController,
    TintucController,
    DiaChiController
};


Route::post('/products/{product_id}/images', [ProductImageController::class, 'store']);

Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{category_id}', [CategoryController::class, 'destroy']);
Route::get('/categories/{category_id}/products', [CategoryController::class, 'getProductsByCategory']);


Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{id}/approve', [OrderController::class, 'approve']);
Route::patch('/orders/{id}/reject', [OrderController::class, 'reject']);
Route::patch('/orders/{id}/hide', [OrderController::class, 'hideOrder']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
Route::get('/users', [UserController::class, 'index']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);




Route::get('/user', function (Request $request) {
    return $request->user();
});

// Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::put('/products/{id}/toggle-noi-bat', [ProductController::class, 'toggleNoiBat']);

// Product images
Route::post('/products/{product_id}/images', [ProductImageController::class, 'store']);
Route::delete('/products/{product_id}/images/{image_id}', [ProductImageController::class, 'destroy']);
Route::delete('/product-images/{id}', [ProductImageController::class, 'destroy']);

// Categories
Route::apiResource('categories', CategoryController::class);
Route::get('/categories/{category_id}/products', [CategoryController::class, 'getProductsByCategory']);

// Orders
Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{id}/approve', [OrderController::class, 'approve']);
Route::patch('/orders/{id}/reject', [OrderController::class, 'reject']);

// Users
Route::get('/users', [UserController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::get('/kich-hoat/{token}', [AuthController::class, 'activate']);

// Tin tức
Route::get('/tintuc', [TintucController::class, 'index']);
Route::get('/tintuc/{id}', [TintucController::class, 'show']);
Route::put('/tintuc/{id}', [TintucController::class, 'update']);
Route::delete('/tintuc/{id}', [TintucController::class, 'destroy']);

Route::post('/tintuc', [TintucController::class, 'store']);
Route::apiResource('addresses', DiaChiController::class);


// Danh mục tin tức
Route::get('/danh-muc-tin-tuc', [DanhMucTtController::class, 'index']);
Route::post('/danh-muc-tin-tuc', [DanhMucTtController::class, 'store']);
Route::get('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'show']);
Route::put('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'update']);
Route::delete('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'destroy']);

// Slide show (admin)
Route::prefix('admin')->group(function () {
    Route::get('slide', [SlideShowController::class, 'index']);
    Route::post('slide', [SlideShowController::class, 'store']);
    Route::post('slide/update', [SlideShowController::class, 'update']);
    Route::post('/slide/add-image', [SlideShowController::class, 'addImageToSlide']);
    Route::delete('slide/{slide_id}/{loai_anh}', [SlideShowController::class, 'deleteImage']);
    Route::post('slide/update-link', [SlideShowController::class, 'updateLink']);
    Route::delete('slide/{id}', [SlideShowController::class, 'destroy']);
    Route::get('slide-hienthi', [SlideShowController::class, 'getSlideTrangChu']);
    Route::post('slide-hienthi', [SlideShowController::class, 'chonSlideHienThi']);
    Route::post('slide/rename', [SlideShowController::class, 'rename']);
});

// Biến thể sản phẩm (variants)
Route::prefix('products/{product_id}')->group(function () {
    Route::get('/variants', [SanPhamBienTheController::class, 'index']);
    Route::post('/variants', [SanPhamBienTheController::class, 'store']);



});
Route::put('/products/{product_id}/variants/{variant_id}', [SanPhamBienTheController::class, 'update']);
Route::delete('/variants/{id}', [SanPhamBienTheController::class, 'destroy']);

// Địa chỉ người dùng

Route::get('/dia_chi/nguoi_dung/{nguoi_dung_id}', [DiaChiController::class, 'index'])->name('dia_chi.by_user');
Route::post('/dia_chi', [DiaChiController::class, 'store']);
Route::put('/dia_chi/{id}', [DiaChiController::class, 'update']);
Route::apiResource('addresses', DiaChiController::class);
