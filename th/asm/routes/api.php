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
use App\Http\Controllers\Api\IntroduceController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DanhMucChaController;
use App\Http\Controllers\Api\CategoryProductController;
use App\Http\Controllers\Api\ChildCategoryController;
use App\Http\Controllers\Api\BinhLuanController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\ParentCategoryProductController;
use App\Http\Controllers\Api\SocialLinkController;
Route::get('/social-links/active', [SocialLinkController::class, 'getActiveLinks']);
Route::patch('/admin/social-links/{id}/status', [SocialLinkController::class, 'updateStatus']);
Route::apiResource('/admin/social-links', SocialLinkController::class);




// Public Auth Routes
Route::post('/auth/google', [GoogleAuthController::class, 'handleGoogleLogin']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/kich-hoat/{token}', [AuthController::class, 'activate']);
Route::post('/resend-activation', [AuthController::class, 'resendActivationEmail']);

Route::post('/send-reset-code', [AuthController::class, 'sendResetCode']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public Product Routes
Route::get('user/product/details-by-slugs', [ProductController::class, 'getDetailsBySlugs']);
Route::apiResource('products', ProductController::class);
Route::put('/products/{id}/toggle-noi-bat', [ProductController::class, 'toggleNoiBat']);

// Product Image Upload (public add, delete protected)
Route::post('/products/{product_id}/images', [ProductImageController::class, 'store']);
Route::delete('/products/{product_id}/images/{image_id}', [ProductImageController::class, 'destroy']);

// Categories
Route::apiResource('categories', CategoryController::class);

// Tin tuc
Route::post('/api/tinymce/upload-image', [TintucController::class, 'uploadTinyMCEImage']);
Route::apiResource('tintuc', TintucController::class);
Route::post('/tintuc/generate-seo', [TintucController::class, 'generateSeoContent']);
Route::get('/tintuc', [TintucController::class, 'index']);
Route::post('/tintuc', [TintucController::class, 'store']);
Route::get('/tintuc/{id}', [TintucController::class, 'show']);
Route::put('/tintuc/{id}', [TintucController::class, 'update']);
Route::delete('/tintuc/{id}', [TintucController::class, 'destroy']);
Route::get('/tintuc-cong-khai', [TintucController::class, 'tintucCongKhai']);
Route::get('tintuc-cong-khai/slug/{slug}', [TintucController::class, 'chitietCongKhaiBySlug']);
Route::get('/tin-noi-bat', [TintucController::class, 'tinNoiBat']);
Route::get('/xemtintuc-admin/{id}', [TintucController::class, 'xemchitiettintucadmin']);
Route::get('/tin-lien-quan/{currentNewsId}/{categoryId}', [TintucController::class, 'tinLienQuan']);
Route::post('/tin-tuc/tang-like/{id}', [TintucController::class, 'tangLuotLike']);


// Danh muc tin tuc
Route::get('/danh-muc-tin-tuc', [DanhMucTtController::class, 'index']);
Route::get('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'show']);
Route::post('/danh-muc-tin-tuc', [DanhMucTtController::class, 'store']);
Route::put('/danh-muc-tin-tuc/{id}', [DanhMucTtController::class, 'update']);
Route::put('/danh-muc-tin-tuc/{id}/toggle-status', [DanhMucTtController::class, 'toggleStatus']);
Route::apiResource('danh-muc-tin-tuc', DanhMucTtController::class);
Route::get('/xemdanhmuc-admin/{id}', [DanhMucTtController::class, 'xemChiTietDanhMucAdmin']);
Route::get('tintuc-cong-khai/danh-muc/{id}', [DanhMucTtController::class, 'tintucCongKhaiTheoDanhMuc']);

// Binh luan
Route::prefix('admin/binhluan')->group(function () {
Route::get('/', [BinhLuanController::class, 'index']);
Route::put('/{id}/toggle', [BinhLuanController::class, 'toggleTrangThai']);
Route::put('/{id}/reset-bao-cao', [BinhLuanController::class, 'resetBaoCao']); // Reset báo cáo (nếu bạn vẫn dùng)
Route::put('/{id}/set-bao-cao', [BinhLuanController::class, 'setBaoCao']);
});

Route::prefix('binh-luan')->group(function () {
    Route::get('danh-gia/{tinTucId}', [BinhLuanController::class, 'getCommentsByRating']);
    Route::get('thong-ke/{tinTucId}', [BinhLuanController::class, 'getCommentStatistics']);
    Route::post('{id}/dislike', [BinhLuanController::class, 'toggleDislike']);
    Route::post('{id}/like', [BinhLuanController::class, 'toggleLike']);
    Route::get('tin-tuc/{tinTucId}', [BinhLuanController::class, 'getCommentsForNews']);
    Route::post('tin-tuc', [BinhLuanController::class, 'addCommentForNews']);
     Route::post('{id}/bao-cao', [BinhLuanController::class, 'setBaoCao']);
});

// Dia chi
Route::apiResource('addresses', DiaChiController::class);
Route::get('/dia_chi/nguoi_dung/{nguoi_dung_id}', [DiaChiController::class, 'index'])->name('dia_chi.by_user');
Route::post('/dia_chi', [DiaChiController::class, 'store']);
Route::put('/dia_chi/{id}', [DiaChiController::class, 'update']);
Route::get('/dia-chi/mac-dinh/{nguoi_dung_id}', [DiaChiController::class, 'diaChiMacDinh']);

// Cart
Route::middleware('auth:sanctum')->group(function () {
Route::get('/cart/{nguoiDungId?}', [CartController::class, 'getCart']);
Route::post('/cart/add', [CartController::class, 'addItem']);
Route::delete('/cart/{userId}/{sanPhamBienTheId}', [CartController::class, 'deleteItem']);
Route::get('/carts/all', [CartController::class, 'getAllCartsAndItems']);
});
Route::get('/cart', [CartController::class, 'index']);

// Gio hang
// Route::get('/gio-hang/nguoi-dung/{id}', [GioHangController::class, 'layGioHangTheoNguoiDung']);


// Thong-bao
Route::get('/Notifications', [NotificationsController::class, 'index']);
Route::post('/Notifications/{id}/read', [NotificationsController::class, 'markAsRead']);
Route::post('/Notifications/read-all', [NotificationsController::class, 'markAllAsRead']);

// Don hang
Route::get('/orders', [OrderController::class, 'index']);
Route::patch('/orders/{order}/payment', [OrderController::class, 'confirmPayment']);

//payment methods
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);// Slide Show (admin)
Route::get('/static-pages', [IntroduceController::class, 'index']);
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
Route::put('/variants/{id}', [SanPhamBienTheController::class, 'update']);


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
Route::get('/products/detail/{slug}', [ProductController::class, 'showSpDetail']);
// Dòng này đã được sửa:
Route::get('/products-sell-top-all', [ProductController::class, 'getTopSelling']); // Đã đổi 'allBestSelling' thành 'getTopSelling'
});
Route::post('/tinymce/upload-image', [ProductController::class, 'uploadEditorImage']);

// Protected Routes (Yêu cầu xác thực Sanctum)
Route::middleware('auth:sanctum')->group(function () {
Route::get('/user', function (Request $request) {
return $request->user();
});
Route::post('/save-fcm-token', [NotificationController::class, 'saveFcmToken']);
Route::get('/admin/users-for-notification', [NotificationController::class, 'getUsersForNotification']);
Route::post('/admin/notifications/send', [NotificationController::class, 'sendNotification']);

Route::put('/users/{id}/change-password', [UserController::class, 'changePassword']);

// Orders
Route::get('/user/orders', [OrderController::class, 'userOrders']);
Route::get('/user/orders/{id}', [OrderController::class, 'getByUser']);
Route::apiResource('orders', OrderController::class)->only(['store']);
Route::patch('/orders/{id}/approve', [OrderController::class, 'approve']);
Route::patch('/orders/{id}/reject', [OrderController::class, 'reject']);
Route::patch('/orders/{id}/hide', [OrderController::class, 'hideOrder']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
// Thống Kê
Route::get('/analytics/revenue', [ProductController::class, 'getRevenueStatistics']);
Route::get('/analytics/overall', [ProductController::class, 'getOverallStatistics']);
// Users
Route::get('/users', [UserController::class, 'index']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::put('/users/{id}', [UserController::class, 'update']);

// Product Variants
Route::get('/products/{id}/variants', [SanPhamBienTheController::class, 'index']);
Route::post('/products/{id}/variants', [SanPhamBienTheController::class, 'store']);
Route::delete('/variants/{id}', [SanPhamBienTheController::class, 'destroy']);
Route::put('/products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
// Product Images (delete only)
Route::delete('/product-images/{id}', [ProductImageController::class, 'destroy']);
Route::post('/products/generate-seo', [ProductController::class, 'generateSeoContent']);

// ROUTE GỠ BỎ DANH MỤC CON - ĐÃ DI CHUYỂN VÀO ĐÂY
// Route này yêu cầu xác thực Sanctum (auth:sanctum)
Route::patch('child-categories/{subcategoryId}/detach', [ChildCategoryController::class, 'detachSubcategory']);


// Admin-specific routes (chỉ truy cập nếu có middleware 'admin' VÀ 'auth:sanctum')
// Nếu bạn không có middleware 'admin' hoặc không cần nó cho các route này, có thể bỏ group này
Route::middleware('admin')->group(function () {
// Các route chỉ dành cho admin (ví dụ: quản lý người dùng, cài đặt hệ thống, v.v.)
// Nếu không có route nào ở đây, có thể bỏ toàn bộ khối admin middleware này.
});

});

// Các route không cần xác thực
Route::get('user/{slug}', [ProductController::class, 'showBySlug'])
->where('slug', '[a-zA-Z0-9-]+');

Route::put('/categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus']);
Route::get('/danh-muc-cha', [DanhMucChaController::class, 'index']);
Route::put('/danh-muc-cha/{id}', [DanhMucChaController::class, 'update']);
Route::put('/danh-muc-cha/{id}/toggle-status', [DanhMucChaController::class, 'toggleStatus']);
Route::post('/danh-muc-cha', [DanhMucChaController::class, 'store']);
Route::get('/danh-muc-cha/{id}', [DanhMucChaController::class, 'show']);
Route::get('/parent-categories', [CategoryController::class, 'getParentCategories']);
Route::get('/categories/{id}/products', [CategoryProductController::class, 'getProductsByCategory']);

Route::delete('/categories/{categoryId}/products/{productId}', [CategoryProductController::class, 'removeProductFromCategory']);
Route::get('/categories/{categoryId}/children', [ChildCategoryController::class, 'getSubcategories']);
Route::get('/categories/{categoryId}/products/{productId}', [CategoryProductController::class, 'getProductByCategory']);
Route::post('/categories/{categoryId}/products', [CategoryProductController::class, 'addProductToCategory']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);



Route::get('/parent-categories/{id}/products', [ParentCategoryProductController::class, 'getProductsByParentCategory']);


