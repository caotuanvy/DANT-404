<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\SanPhamBienThe;
use App\Models\DiaChi;
use App\Models\Notifications;
use App\Models\SanPham; // Import model SanPham
use App\Models\HinhAnhSanPham; // Import model HinhAnhSanPham
use App\Models\GiamGia; // Import model GiamGia

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::whereNull('ngay_xoa')
            ->with([
                'user',
                'paymentMethod',
                'orderItems.bien_the.sanPham.hinhAnhSanPham',
            ]);

        if ($request->has('status') && $request->status) {
            $query->where('trang_thai_don_hang', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('ho_ten', 'like', "%$search%");
                    });
            });
        }

        if ($request->has('date') && $request->date) {
            try {
                $date = Carbon::parse($request->date)->toDateString();
                $query->whereDate('ngay_dat', $date);
            } catch (\Exception $e) {
                Log::warning('Định dạng ngày không hợp lệ trong OrderController::index: ' . $request->date);
            }
        }

        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('phuong_thuc_thanh_toan_id', $request->payment_method);
        }

        if ($request->has('price_range') && $request->price_range) {
            $priceParts = explode('-', $request->price_range);
            if (count($priceParts) == 2) {
                $minPrice = (int) $priceParts[0];
                $maxPrice = (int) $priceParts[1];
                $query->whereBetween('tong_tien', [$minPrice, $maxPrice]);
            }
        }

        $perPage = $request->input('per_page', 10);
        if ($request->has('get_latest_for_user') && $request->get_latest_for_user) {
            $userId = Auth::id();
            if ($userId) {
                $query->where('nguoi_dung_id', $userId);
            }
            $orders = $query->orderBy('ngay_dat', 'desc')
                             ->limit(20)
                             ->get();
            return response()->json(['data' => $orders]);
        } else {
            $orders = $query->orderBy('ngay_dat', 'desc')->paginate($perPage);
            return response()->json($orders);
        }
    }

    public function hideOrder($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->ngay_xoa = now();
            $order->save();
            return response()->json(['message' => 'Đã ẩn đơn hàng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi ẩn đơn hàng ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Không thể ẩn đơn hàng. Vui lòng thử lại.'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'trang_thai_don_hang' => 'required|integer|min:1|max:5',
        ]);

        try {
            $order = Order::findOrFail($id);
            $order->trang_thai_don_hang = $request->input('trang_thai_don_hang');
            $order->save();
            return response()->json($order, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật trạng thái đơn hàng ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Không thể cập nhật trạng thái. Vui lòng thử lại.'], 500);
        }
    }

    public function userOrders(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Bạn cần đăng nhập để xem lịch sử đơn hàng.'], 401);
        }

        $query = Order::where('nguoi_dung_id', $userId)
            ->with('paymentMethod', 'orderItems.bien_the.sanPham.hinhAnhSanPham');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhereHas('orderItems.bien_the.sanPham', function ($q3) use ($search) {
                        $q3->where('ten_san_pham', 'like', "%$search%");
                    });
            });
        }

        $orders = $query->orderBy('ngay_dat', 'desc')->get();
        return response()->json($orders, 200);
    }

    public function confirmPayment(Request $request, Order $order)
    {
        try {
            if ($order->is_paid == 1) {
                return response()->json(['message' => 'Đơn hàng này đã được xác nhận thanh toán trước đó.'], 400);
            }
            $order->is_paid = 1;
            $order->save();

            return response()->json([
                'message' => 'Xác nhận thanh toán thành công',
                'order' => $order
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xác nhận thanh toán đơn hàng ' . $order->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Không thể xác nhận thanh toán. Vui lòng thử lại.'], 500);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Bạn cần đăng nhập để đặt hàng.'], 401);
        }

        // BƯỚC 1: Validate dữ liệu đầu vào từ frontend
        $validatedData = $request->validate([
            'phuong_thuc_thanh_toan_id' => 'required|integer|exists:phuong_thuc_thanh_toan,phuong_thuc_thanh_toan_id',
            'dia_chi_giao_hang' => 'required|string|max:255',
            'phi_van_chuyen' => 'required|numeric|min:0',
            'ma_giam_gia' => 'nullable|string|max:255',
            'ghi_chu' => 'nullable|string',
            'ten_nguoi_nhan' => 'required|string|max:100',
            'sdt_nguoi_nhan' => 'required|string|max:20',
        ]);

        // Bắt đầu transaction để đảm bảo an toàn dữ liệu
        DB::beginTransaction();

        try {
            // BƯỚC 2: Lấy thông tin giỏ hàng và sản phẩm của người dùng
            $cartItems = DB::table('gio_hang_chi_tiet as ghct')
                ->join('san_pham_bien_the as spbt', 'ghct.san_pham_bien_the_id', '=', 'spbt.bien_the_id')
                ->join('gio_hang as gh', 'ghct.gio_hang_id', '=', 'gh.gio_hang_id')
                ->where('gh.nguoi_dung_id', $user->nguoi_dung_id)
                ->select('spbt.bien_the_id', 'spbt.ten_bien_the', 'spbt.gia', 'spbt.so_luong_ton_kho', 'ghct.so_luong')
                ->lockForUpdate() // Khóa các dòng sản phẩm để tránh race condition
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Giỏ hàng của bạn đang trống.'], 400);
            }

            // BƯỚC 3: Kiểm tra số lượng tồn kho trước khi xử lý
            foreach ($cartItems as $item) {
                if ($item->so_luong > $item->so_luong_ton_kho) {
                    DB::rollBack();
                    return response()->json(['message' => 'Sản phẩm "' . $item->ten_bien_the . '" không đủ số lượng tồn kho. Chỉ còn ' . $item->so_luong_ton_kho . ' sản phẩm.'], 400);
                }
            }

            // BƯỚC 4: Tính tổng tiền hàng và xác thực mã giảm giá
            $subtotal = $cartItems->sum(fn($item) => $item->gia * $item->so_luong);
            $coupon = null;
            $discountAmount = 0;

            if (!empty($validatedData['ma_giam_gia'])) {
                $coupon = GiamGia::where('ma_giam_gia', $validatedData['ma_giam_gia'])->first();

                if (!$coupon || !$coupon->trang_thai || $coupon->ngay_ket_thuc < now() || $coupon->so_luong_da_dung >= $coupon->so_luong) {
                    return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], 400);
                }
                if ($coupon->gia_tri_don_hang_toi_thieu && $subtotal < $coupon->gia_tri_don_hang_toi_thieu) {
                    return response()->json(['message' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã.'], 400);
                }

                $isPrivateCouponQuery = DB::table('nguoi_dung_giam_gia')->where('giam_gia_id', $coupon->giam_gia_id);
                if ($isPrivateCouponQuery->exists()) {
                    $userHasCoupon = $isPrivateCouponQuery->where('nguoi_dung_id', $user->nguoi_dung_id)->first();
                    if (!$userHasCoupon || $userHasCoupon->da_su_dung) {
                        return response()->json(['message' => 'Mã giảm giá này không dành cho bạn hoặc đã được sử dụng.'], 400);
                    }
                }

                if ($coupon->loai_giam_gia == 'percentage') {
                    $discountAmount = ($subtotal * $coupon->gia_tri) / 100;
                    if ($coupon->gia_tri_giam_toi_da && $discountAmount > $coupon->gia_tri_giam_toi_da) {
                        $discountAmount = $coupon->gia_tri_giam_toi_da;
                    }
                } elseif ($coupon->loai_giam_gia == 'fixed_amount') {
                    $discountAmount = $coupon->gia_tri;
                }
            }

            // BƯỚC 5: Tính toán tổng tiền cuối cùng
            $finalTotal = $subtotal - $discountAmount + $validatedData['phi_van_chuyen'];
            if ($finalTotal < 0) $finalTotal = 0;

            // BƯỚC 6: Tạo đơn hàng
            $order = Order::create([
                'nguoi_dung_id' => $user->nguoi_dung_id,
                'phuong_thuc_thanh_toan_id' => $validatedData['phuong_thuc_thanh_toan_id'],
                'id_giam_gia' => $coupon ? $coupon->giam_gia_id : null,
                'so_tien_giam' => $discountAmount,
                'ten_nguoi_nhan' => $validatedData['ten_nguoi_nhan'],
                'sdt_nguoi_nhan' => $validatedData['sdt_nguoi_nhan'],
                'dia_chi_giao_hang' => $validatedData['dia_chi_giao_hang'],
                'trang_thai_don_hang' => 1,
                'ghi_chu' => $validatedData['ghi_chu'] ?? null,
                'phi_van_chuyen' => $validatedData['phi_van_chuyen'],
                'tong_tien' => $finalTotal,
                'ngay_dat' => now(),
            ]);

            // BƯỚC 7: Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'don_hang_id' => $order->id,
                    'san_pham_bien_the_id' => $item->bien_the_id,
                    'so_luong' => $item->so_luong,
                    'don_gia' => $item->gia,
                    'thanh_tien' => $item->gia * $item->so_luong
                ]);
                SanPhamBienThe::where('bien_the_id', $item->bien_the_id)->decrement('so_luong_ton_kho', $item->so_luong);
            }

            // BƯỚC 8: Cập nhật số lượt sử dụng mã giảm giá và đánh dấu đã dùng
            if ($coupon) {
                $coupon->increment('so_luong_da_dung');
                DB::table('nguoi_dung_giam_gia')
                    ->where('nguoi_dung_id', $user->nguoi_dung_id)
                    ->where('giam_gia_id', $coupon->giam_gia_id)
                    ->update(['da_su_dung' => true]);
            }

            // BƯỚC 9: Xóa giỏ hàng
            $cart = Cart::where('nguoi_dung_id', $user->nguoi_dung_id)->first();
            if ($cart) {
                 DB::table('gio_hang_chi_tiet')->where('gio_hang_id', $cart->gio_hang_id)->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Đặt hàng thành công!', 'order_id' => $order->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo đơn hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Hệ thống đã xảy ra lỗi, vui lòng thử lại sau.', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function cancel(Request $request, $id)
    {
        $order = Order::find($id);

        Log::info('Hủy đơn:', [
            'id' => $id,
            'order_user_id' => $order?->nguoi_dung_id,
            'auth_user_id' => Auth::id(),
            'ly_do_huy_request' => $request->input('ly_do_huy')
        ]);

        if (!$order || $order->nguoi_dung_id !== Auth::id()) {
            return response()->json(['message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn'], 404);
        }
        
        if ($order->trang_thai_don_hang !== 1) {
            return response()->json(['message' => 'Không thể hủy đơn hàng này'], 400);
        }

        $order->trang_thai_don_hang = 5;
        $order->ly_do_huy = $request->input('ly_do_huy');
        $order->save();

        return response()->json(['message' => 'Đơn hàng đã được hủy thành công']);
    }

    public function getStatusCounts(Request $request)
    {
        try {
            $statusKeys = [
                1 => 'pending',
                2 => 'confirmed',
                3 => 'shipping',
                4 => 'completed',
                5 => 'cancelled',
            ];

            $counts = array_fill_keys(array_values($statusKeys), 0);

            $dbCounts = Order::query()
                ->select('trang_thai_don_hang', DB::raw('count(*) as total'))
                ->groupBy('trang_thai_don_hang')
                ->get();

            foreach ($dbCounts as $row) {
                if (isset($statusKeys[$row->trang_thai_don_hang])) {
                    $statusKey = $statusKeys[$row->trang_thai_don_hang];
                    $counts[$statusKey] = $row->total;
                }
            }

            $counts['all'] = array_sum($counts);
            return response()->json($counts);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể lấy dữ liệu thống kê.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createVnPayPayment(Request $request)
    {
        $cart = $request->cart;
        $total = $request->total;
        $userId = $request->user_id;
        $ten_nguoi_nhan = $request->ten_nguoi_nhan;
        $sdt_nguoi_nhan = $request->sdt_nguoi_nhan;
        $dia_chi_giao_hang = $request->dia_chi_giao_hang;
        $phi_van_chuyen = $request->phi_van_chuyen;
        $ma_giam_gia = $request->ma_giam_gia;
        $ghi_chu = $request->ghi_chu;

        // BƯỚC 1: Tạo đơn hàng tạm trong DB với các thông tin mới
        $order = Order::create([
            'nguoi_dung_id' => $userId,
            'dia_chi_giao_hang' => $dia_chi_giao_hang,
            'phi_van_chuyen' => $phi_van_chuyen,
            'ten_nguoi_nhan' => $ten_nguoi_nhan,
            'sdt_nguoi_nhan' => $sdt_nguoi_nhan,
            'ma_giam_gia' => $ma_giam_gia,
            'ghi_chu' => $ghi_chu,
            'tong_tien' => $total,
            'trang_thai_don_hang' => 1,
            'phuong_thuc_thanh_toan_id' => 2, // VNPAY
            'is_paid' => 0,
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'don_hang_id' => $order->id,
                'san_pham_bien_the_id' => $item['san_pham_bien_the_id'],
                'so_luong' => $item['so_luong'],
                'don_gia' => $item['don_gia'],
                'thanh_tien' => $item['thanh_tien']
            ]);
        }

        // BƯỚC 2: Chuẩn bị dữ liệu VNPAY
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_Url = env('VNP_URL');
        $vnp_Returnurl = env('VNP_RETURN_URL');

        $vnp_TxnRef = $order->id;
        $vnp_OrderInfo = "Thanh toán đơn hàng #$order->id";
        $vnp_Amount = $total * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = http_build_query($inputData);
        $hashdata = hash_hmac('sha512', urldecode($query), $vnp_HashSecret);
        $vnp_Url .= '?' . $query . '&vnp_SecureHash=' . $hashdata;

        return response()->json([
            'payment_url' => $vnp_Url
        ]);
    }

    public function show($id)
    {
        try {
            $order = Order::with([
                'user',
                'paymentMethod',
                'orderItems.bien_the.sanPham.hinhAnhSanPham',
                'orderItems.bien_the.sanPham.danhMuc',
            ])->findOrFail($id);
            return response()->json($order);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng.'], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết đơn hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi server khi lấy thông tin đơn hàng.'], 500);
        }
    }
}