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
use App\Models\GiamGia; // Import model GiamGia]

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
                'user', // Load thông tin người dùng
                'paymentMethod', // Load thông tin phương thức thanh toán
                'diachi', // Load thông tin địa chỉ
                'orderItems.bien_the.sanPham.hinhAnhSanPham', // Load hình ảnh sản phẩm từ sản phẩm cha
                // Hoặc 'orderItems.bien_the.hinhAnhDaiDien' nếu bạn có mối quan hệ đó
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

        $perPage = $request->input('per_page', 10); // Mặc định 10 nếu không có
        if ($request->has('get_latest_for_user') && $request->get_latest_for_user) {
            // Logic để chỉ lấy N đơn hàng gần nhất cho một user cụ thể
            $userId = Auth::id(); // Lấy ID của người dùng hiện tại
            if ($userId) {
                $query->where('nguoi_dung_id', $userId);
            }
            $orders = $query->orderBy('ngay_dat', 'desc')
                                 ->limit(20) // Giới hạn 20 đơn hàng gần nhất
                                 ->get();
            return response()->json(['data' => $orders]); // Trả về dạng data: [...]
        } else {
            // Mặc định cho admin hoặc các trường hợp khác, sử dụng phân trang đầy đủ
            $orders = $query->orderBy('ngay_dat', 'desc')->paginate($perPage);
            return response()->json($orders); // Laravel paginate() trả về cấu trúc đầy đủ
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
            'trang_thai_don_hang' => 'required|integer|min:1|max:5', // <-- Validate là số nguyên từ 1-5
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
            ->with('paymentMethod', 'diachi', 'orderItems.bien_the.sanPham.hinhAnhSanPham'); // Thêm eager loading

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
            'dia_chi_id' => 'required|integer|exists:dia_chi,id_dia_chi',
            'phi_van_chuyen' => 'required|numeric|min:0',
            'ma_giam_gia' => 'nullable|string|max:255',
            'ghi_chu' => 'nullable|string',
        ]);

        // Bắt đầu transaction để đảm bảo an toàn dữ liệu: nếu có lỗi, mọi thứ sẽ được hoàn tác.
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
                    DB::rollBack(); // Hủy bỏ transaction
                    return response()->json(['message' => 'Sản phẩm "' . $item->ten_bien_the . '" không đủ số lượng tồn kho. Chỉ còn ' . $item->so_luong_ton_kho . ' sản phẩm.'], 400);
                }
            }

            // BƯỚC 4: Tính tổng tiền hàng (subtotal)
            $subtotal = $cartItems->sum(fn($item) => $item->gia * $item->so_luong);
            $coupon = null;
            $discountAmount = 0;

            // BƯỚC 5: Xác thực lại toàn bộ mã giảm giá ở backend
            if (!empty($validatedData['ma_giam_gia'])) {
                $coupon = GiamGia::where('ma_giam_gia', $validatedData['ma_giam_gia'])->first();

                // 5.1. Kiểm tra các điều kiện cơ bản của mã
                if (!$coupon || !$coupon->trang_thai || $coupon->ngay_ket_thuc < now() || $coupon->so_luong_da_dung >= $coupon->so_luong) {
                     return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], 400);
                }
                if ($coupon->gia_tri_don_hang_toi_thieu && $subtotal < $coupon->gia_tri_don_hang_toi_thieu) {
                     return response()->json(['message' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã.'], 400);
                }

                // 5.2. Kiểm tra nếu là mã cá nhân (đã được gửi riêng)
                $isPrivateCouponQuery = DB::table('nguoi_dung_giam_gia')->where('giam_gia_id', $coupon->giam_gia_id);
                if ($isPrivateCouponQuery->exists()) {
                    $userHasCoupon = $isPrivateCouponQuery->where('nguoi_dung_id', $user->nguoi_dung_id)->first();
                    if (!$userHasCoupon || $userHasCoupon->da_su_dung) {
                        return response()->json(['message' => 'Mã giảm giá này không dành cho bạn hoặc đã được sử dụng.'], 400);
                    }
                }

                // 5.3. Tính toán lại số tiền giảm giá một cách an toàn
                if ($coupon->loai_giam_gia == 'percentage') {
                    $discountAmount = ($subtotal * $coupon->gia_tri) / 100;
                    if ($coupon->gia_tri_giam_toi_da && $discountAmount > $coupon->gia_tri_giam_toi_da) {
                        $discountAmount = $coupon->gia_tri_giam_toi_da;
                    }
                } elseif ($coupon->loai_giam_gia == 'fixed_amount') {
                    $discountAmount = $coupon->gia_tri;
                }
            }

            // BƯỚC 6: Tính toán tổng tiền cuối cùng
            $finalTotal = $subtotal - $discountAmount + $validatedData['phi_van_chuyen'];
            if ($finalTotal < 0) $finalTotal = 0;

            // BƯỚC 7: Tạo đơn hàng
            $order = Order::create([
                'nguoi_dung_id' => $user->nguoi_dung_id,
                'phuong_thuc_thanh_toan_id' => $validatedData['phuong_thuc_thanh_toan_id'],
                'id_giam_gia' => $coupon ? $coupon->giam_gia_id : null,
                'so_tien_giam' => $discountAmount,
                'dia_chi_id' => $validatedData['dia_chi_id'],
                'trang_thai_don_hang' => 1, // 1 = Chờ xác nhận
                'ghi_chu' => $validatedData['ghi_chu'] ?? null,
                'phi_van_chuyen' => $validatedData['phi_van_chuyen'],
                'tong_tien' => $finalTotal,
                'ngay_dat' => now(),
            ]);

            // BƯỚC 8: Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'don_hang_id' => $order->id,
                    'san_pham_bien_the_id' => $item->bien_the_id,
                    'so_luong' => $item->so_luong,
                    'don_gia' => $item->gia,
                    'thanh_tien' => $item->gia * $item->so_luong
                ]);
                // Trừ số lượng tồn kho
                SanPhamBienThe::where('bien_the_id', $item->bien_the_id)->decrement('so_luong_ton_kho', $item->so_luong);
            }

            // BƯỚC 9: Cập nhật số lượt sử dụng mã giảm giá
            if ($coupon) {
                $coupon->increment('so_luong_da_dung');
                // Nếu là mã cá nhân, đánh dấu đã sử dụng
                DB::table('nguoi_dung_giam_gia')
                    ->where('nguoi_dung_id', $user->nguoi_dung_id)
                    ->where('giam_gia_id', $coupon->giam_gia_id)
                    ->update(['da_su_dung' => true]);
            }

            // BƯỚC 10: Xóa giỏ hàng
            DB::table('gio_hang_chi_tiet')->whereIn('gio_hang_chi_tiet_id', function($query) use ($user) {
                $query->select('ghct.gio_hang_chi_tiet_id')->from('gio_hang_chi_tiet as ghct')
                    ->join('gio_hang as gh', 'ghct.gio_hang_id', '=', 'gh.gio_hang_id')
                    ->where('gh.nguoi_dung_id', $user->nguoi_dung_id);
            })->delete();

            // Hoàn tất và lưu mọi thay đổi vào CSDL
            DB::commit();

            return response()->json(['message' => 'Đặt hàng thành công!', 'order_id' => $order->id], 201);

        } catch (\Exception $e) {
            // Nếu có bất kỳ lỗi nào xảy ra, hủy bỏ mọi thay đổi đã thực hiện
            DB::rollBack();
            Log::error('Lỗi khi tạo đơn hàng: ' . $e->getMessage());
            return response()->json(['message' => 'Hệ thống đã xảy ra lỗi, vui lòng thử lại sau.', 'error' => $e->getMessage()], 500);
        }
    }
    public function cancel($id)
    {
        $order = Order::find($id);

        // Debug
        Log::info('Hủy đơn:', [
            'id' => $id,
            'order_user_id' => $order?->nguoi_dung_id,
            'auth_user_id' => Auth::id(),
        ]);

        if (!$order || $order->nguoi_dung_id !== Auth::id()) {
            return response()->json(['message' => 'Đơn hàng không tồn tại hoặc không thuộc về bạn'], 404);
        }

        $order->trang_thai_don_hang = 5; // Đã hủy
        $order->save();

        return response()->json(['message' => 'Đơn hàng đã được hủy']);
    }
    public function getStatusCounts(Request $request)
    {
        try {
            // 1. Định nghĩa các key mà frontend mong đợi
            $statusKeys = [
                1 => 'pending',     // Chờ xác nhận
                2 => 'confirmed',   // Đã xác nhận
                3 => 'shipping',    // Đang giao
                4 => 'completed',   // Hoàn thành
                5 => 'cancelled',   // Đã hủy
            ];

            // 2. Khởi tạo mảng đếm với giá trị ban đầu là 0
            $counts = array_fill_keys(array_values($statusKeys), 0);

            // 3. Truy vấn CSDL để lấy số lượng theo từng 'trang_thai_don_hang'
            // Đây là một truy vấn rất hiệu quả
            $dbCounts = Order::query()
                ->select('trang_thai_don_hang', DB::raw('count(*) as total'))
                ->groupBy('trang_thai_don_hang')
                ->get();

            // 4. Lặp qua kết quả từ CSDL và điền vào mảng $counts
            foreach ($dbCounts as $row) {
                // Kiểm tra xem trạng thái từ CSDL có trong định nghĩa của chúng ta không
                if (isset($statusKeys[$row->trang_thai_don_hang])) {
                    $statusKey = $statusKeys[$row->trang_thai_don_hang];
                    $counts[$statusKey] = $row->total;
                }
            }

            // 5. Tính tổng số đơn hàng và thêm vào mảng
            $counts['all'] = array_sum($counts);

            // Hoặc nếu bạn muốn 'all' là tổng tất cả đơn hàng không phân biệt trạng thái
            // $counts['all'] = Order::count();


            // 6. Trả về kết quả dưới dạng JSON
            return response()->json($counts);

        } catch (\Exception $e) {
            // Xử lý nếu có lỗi xảy ra
            return response()->json([
                'message' => 'Không thể lấy dữ liệu thống kê.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
