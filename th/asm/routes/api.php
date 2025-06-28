<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\SanPhamBienTheController;
use App\Http\Controllers\Api\DanhMucTtController;
use App\Http\Controllers\Api\SlideShowController;
use App\Http\Controllers\Api\TintucController;
use App\Http\Controllers\Api\DiaChiController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\GioHangController;
use App\Http\Controllers\Api\IntroduceController;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/kich-hoat/{token}', [AuthController::class, 'activate']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Public Product Routes
Route::apiResource('products', ProductController::class);
Route::put('/products/{id}/toggle-noi-bat', [ProductController::class, 'toggleNoiBat']);
Route::get('/categories/{id}/products', [CategoryController::class, 'getProductsByCategory']);


// Product Image Upload (public add, delete protected)
Route::post('/products/{product_id}/images', [ProductImageController::class, 'store']);
Route::delete('/products/{product_id}/images/{image_id}', [ProductImageController::class, 'destroy']);

// Categories
Route::apiResource('categories', CategoryController::class);

// Tin tuc
Route::apiResource('tintuc', TintucController::class);
Route::get('/tintuc', [TintucController::class, 'index']);
Route::post('/tintuc', [TintucController::class, 'store']);
Route::get('/tintuc/{id}', [TintucController::class, 'show']);
Route::put('/tintuc/{id}', [TintucController::class, 'update']);
Route::delete('/tintuc/{id}', [TintucController::class, 'destroy']);
Route::get('/tintuc-cong-khai', [TintucController::class, 'tintucCongKhai']);
Route::get('/tintuc-cong-khai/{id}', [TintucController::class, 'chitietCongKhai']);
Route::get('/tin-noi-bat', [TintucController::class, 'tinNoiBat']);
Route::get('/xemtintuc-admin/{id}', [TintucController::class, 'xemchitiettintucadmin']);


// Danh muc tin tuc
Route::get('/danh-muc-tin-tuc', [DanhMucTtController::class, 'index']);
Route::get('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'show']);
Route::post('/danh-muc-tin-tuc', [DanhMucTtController::class, 'store']);
Route::put('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'update']);
Route::delete('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'destroy']);
Route::apiResource('danh-muc-tin-tuc', DanhMucTtController::class);
Route::get('/xemdanhmuc-admin/{id}', [DanhMucTtController::class, 'xemChiTietDanhMucAdmin']);
Route::get('tintuc-cong-khai/danh-muc/{id}', [DanhMucTtController::class, 'tintucCongKhaiTheoDanhMuc']);

// Dia chi
Route::apiResource('addresses', DiaChiController::class);
Route::get('/dia_chi/nguoi_dung/{nguoi_dung_id}', [DiaChiController::class, 'index'])->name('dia_chi.by_user');
Route::post('/dia_chi', [DiaChiController::class, 'store']);
Route::put('/dia_chi/{id}', [DiaChiController::class, 'update']);
Route::get('/dia-chi/mac-dinh/{nguoi_dung_id}', [DiaChiController::class, 'diaChiMacDinh']);

// Cart
Route::middleware('auth:sanctum')->post('/cart/add', [CartController::class, 'add']);
Route::get('/cart', [CartController::class, 'index']);

// Gio hang
Route::get('/gio-hang/nguoi-dung/{id}', [GioHangController::class, 'layGioHangTheoNguoiDung']);

// Slide Show (admin)
Route::prefix('admin')->group(function () {
   Route::get('slide', [SlideShowController::class, 'index']);
    Route::get('slide/{id}', [SlideShowController::class, 'show']);
    Route::post('slide', [SlideShowController::class, 'store']);
    Route::post('slide/update', [SlideShowController::class, 'update']);
    Route::post('slide/add-image', [SlideShowController::class, 'addImageToSlide']);
    Route::post('slide/update-link', [SlideShowController::class, 'updateLink']);
    Route::post('slide/rename', [SlideShowController::class, 'rename']);
    Route::post('slide-hienthi', [SlideShowController::class, 'chonSlideHienThi']);
    Route::get('slide-hienthi', [SlideShowController::class, 'getSlideTrangChu']);
    Route::post('slide/image/update-image/{id}', [SlideShowController::class, 'updateImage']);


    Route::delete('slide/image/{id}', [SlideShowController::class, 'deleteImage']);
    Route::delete('slide/{id}', [SlideShowController::class, 'destroy']);
    Route::get('trang-tinh', [IntroduceController::class, 'index']);
    Route::post('trang-tinh', [IntroduceController::class, 'store']);
    Route::post('trang-tinh/update', [IntroduceController::class, 'update']);
    Route::post('trang-tinh/{id}', [IntroduceController::class, 'updateMeta']);
    Route::delete('trang-tinh/{id}', [IntroduceController::class, 'destroy']);
    Route::get('trang-tinh/{slug}', [IntroduceController::class, 'show']);
    Route::get('/products-sell-top', [ProductController::class, 'getTopSelling']);
    Route::get('products-featured', [ProductController::class, 'getFeatured']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::put('/users/{id}/change-password', [UserController::class, 'changePassword']);

    // Orders
    Route::get('/user/orders', [OrderController::class, 'userOrders']);
    Route::get('/user/orders/{id}', [OrderController::class, 'getByUser']);
    Route::apiResource('orders', OrderController::class)->only(['index', 'store']);
    Route::patch('/orders/{id}/approve', [OrderController::class, 'approve']);
    Route::patch('/orders/{id}/reject', [OrderController::class, 'reject']);
    Route::patch('/orders/{id}/hide', [OrderController::class, 'hideOrder']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}', [UserController::class, 'update']);

    // Product Variants
    Route::get('/products/{id}/variants', [SanPhamBienTheController::class, 'index']);
    Route::post('/products/{id}/variants', [SanPhamBienTheController::class, 'store']);
    Route::delete('/variants/{id}', [SanPhamBienTheController::class, 'destroy']);

    // Product Images (delete only)
    Route::delete('/product-images/{id}', [ProductImageController::class, 'destroy']);
});
