<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiamGia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Thêm DB Facade

class CouponController extends Controller
{
     public function myCoupons(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Bắt đầu truy vấn trực tiếp từ bảng pivot `nguoi_dung_giam_gia`
        $vouchers = DB::table('nguoi_dung_giam_gia')
            // 1. Chỉ lấy các dòng của người dùng đang đăng nhập
            ->where('nguoi_dung_giam_gia.nguoi_dung_id', $user->nguoi_dung_id)

            // 2. Kết nối (join) với bảng `giam_gia` để lấy thông tin chi tiết của mã
            ->join('giam_gia', 'nguoi_dung_giam_gia.giam_gia_id', '=', 'giam_gia.giam_gia_id')

            // 3. Áp dụng các điều kiện lọc
            ->where('nguoi_dung_giam_gia.da_su_dung', false) // Phải chưa được sử dụng
            ->where('giam_gia.trang_thai', true)            // Mã phải đang hoạt động
            ->where('giam_gia.ngay_ket_thuc', '>', now())    // Mã phải còn hạn sử dụng

            // 4. Lựa chọn các cột cần thiết để trả về cho frontend
            ->select('giam_gia.*') // Lấy tất cả thông tin của mã giảm giá
            ->get();

        return response()->json($vouchers);
    }

    /**
     * [SỬA ĐỔI TOÀN BỘ] - Áp dụng mã giảm giá
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ma_giam_gia' => 'required|string',
            'cart_items' => 'required|array',
            'cart_items.*.id' => 'required|integer', // ID của san_pham_bien_the
            'cart_items.*.quantity' => 'required|integer|min:1',
            'cart_items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu giỏ hàng không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        $code = $request->ma_giam_gia;
        $cartItems = collect($request->cart_items);
        $user = $request->user(); // Lấy thông tin người dùng đang đăng nhập

        $coupon = GiamGia::where('ma_giam_gia', $code)->first();

        // --- Chuỗi kiểm tra điều kiện mã ---
        if (!$coupon) return response()->json(['message' => 'Mã giảm giá không tồn tại.'], 404);
        if (!$coupon->trang_thai) return response()->json(['message' => 'Mã giảm giá không hoạt động.'], 400);
        if ($coupon->ngay_bat_dau > now()) return response()->json(['message' => 'Mã giảm giá chưa đến ngày sử dụng.'], 400);
        if ($coupon->ngay_ket_thuc < now()) return response()->json(['message' => 'Mã giảm giá đã hết hạn.'], 400);
        if ($coupon->so_luong_da_dung >= $coupon->so_luong) return response()->json(['message' => 'Mã giảm giá đã hết lượt sử dụng.'], 400);

        // [FIX] KIỂM TRA MÃ RIÊNG TƯ / CÔNG KHAI
        $isPrivate = DB::table('nguoi_dung_giam_gia')->where('giam_gia_id', $coupon->giam_gia_id)->exists();
        if ($isPrivate) {
            $userHasCoupon = DB::table('nguoi_dung_giam_gia')
                ->where('giam_gia_id', $coupon->giam_gia_id)
                ->where('nguoi_dung_id', $user->nguoi_dung_id)
                ->first();

            if (!$userHasCoupon) {
                return response()->json(['message' => 'Bạn không thể sử dụng mã giảm giá này.'], 403);
            }
            if ($userHasCoupon->da_su_dung) {
                return response()->json(['message' => 'Bạn đã sử dụng mã giảm giá này rồi.'], 400);
            }
        }

        // --- Tính toán giỏ hàng và kiểm tra điều kiện ---
        $cartTotal = $cartItems->sum(fn($item) => $item['quantity'] * $item['price']);

        if ($coupon->gia_tri_don_hang_toi_thieu && $cartTotal < $coupon->gia_tri_don_hang_toi_thieu) {
            return response()->json(['message' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã.'], 400);
        }

        // --- Kiểm tra phạm vi áp dụng ---
        $applicableTotal = $cartTotal; // Mặc định, áp dụng cho toàn bộ giỏ hàng

        if ($coupon->ap_dung_cho) {
            $condition = json_decode($coupon->ap_dung_cho, true);
            $applicableItems = collect([]);

            if ($condition['type'] === 'product') {
                $productVariantIds = $cartItems->pluck('id')->toArray();
                $applicableVariantIds = DB::table('san_pham_bien_the')
                    ->whereIn('bien_the_id', $productVariantIds)
                    ->whereIn('san_pham_id', $condition['ids'])
                    ->pluck('bien_the_id');

                $applicableItems = $cartItems->whereIn('id', $applicableVariantIds);

            } else if ($condition['type'] === 'category') { // [FIX] HOÀN THIỆN LOGIC CHO CATEGORY
                $productVariantIds = $cartItems->pluck('id')->toArray();

                // Lấy ID danh mục của các sản phẩm trong giỏ hàng
                $applicableVariantIds = DB::table('san_pham_bien_the as spbt')
                    ->join('san_pham as sp', 'spbt.san_pham_id', '=', 'sp.san_pham_id')
                    ->whereIn('spbt.bien_the_id', $productVariantIds)
                    ->whereIn('sp.ten_danh_muc_id', $condition['ids'])
                    ->pluck('spbt.bien_the_id');

                $applicableItems = $cartItems->whereIn('id', $applicableVariantIds);
            }

            if ($applicableItems->isEmpty()) {
                return response()->json(['message' => 'Mã giảm giá không áp dụng cho các sản phẩm trong giỏ hàng.'], 400);
            }
            // Tính lại tổng tiền của các sản phẩm được áp dụng
            $applicableTotal = $applicableItems->sum(fn($item) => $item['quantity'] * $item['price']);
        }

        // --- Tính toán giá trị giảm giá ---
        $discountAmount = 0;
        if ($coupon->loai_giam_gia == 'percentage') {
            $discountAmount = ($applicableTotal * $coupon->gia_tri) / 100;
            if ($coupon->gia_tri_giam_toi_da && $discountAmount > $coupon->gia_tri_giam_toi_da) {
                $discountAmount = $coupon->gia_tri_giam_toi_da;
            }
        } else if ($coupon->loai_giam_gia == 'fixed_amount') {
            $discountAmount = $coupon->gia_tri;
        } else if ($coupon->loai_giam_gia == 'free_ship') {
            // Logic freeship (ví dụ trả về một cờ 'is_free_ship' để frontend xử lý)
            // $discountAmount = $shippingFee; // Frontend cần gửi phí ship lên
        }

        return response()->json([
            'message' => 'Áp dụng mã giảm giá thành công!',
            'discount_amount' => round($discountAmount),
            'new_total' => $cartTotal - round($discountAmount),
            'coupon_details' => $coupon
        ]);
    }
}
