<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\SanPhamBienThe;
use App\Models\DiaChi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request){
        $query = Order::whereNull('ngay_xoa')
            ->with('user', 'paymentMethod', 'orderItems.bien_the.sanPham', 'diachi');

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
        $orders = $query->orderBy('ngay_dat', 'desc')->paginate($perPage);

        return response()->json($orders);
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
            ->with('paymentMethod', 'orderItems.bien_the.sanPham', 'diachi');

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
            return response()->json(['message' => 'Vui lòng đăng nhập.'], 401);
        }

        $userId = $user->nguoi_dung_id ?? $user->id;

        try {
            $validatedData = $request->validate([
                'tong_tien' => 'required|numeric|min:0',
                'phuong_thuc_thanh_toan_id' => 'required|integer',
                'hinh_thuc_giao_hang' => 'required|string|max:50',
                'phi_van_chuyen' => 'required|numeric|min:0',
                'san_pham' => 'required|array|min:1',
                'san_pham.*.bien_the_id' => 'required|integer|exists:san_pham_bien_the,bien_the_id',
                'san_pham.*.so_luong' => 'required|integer|min:1',
                'san_pham.*.don_gia' => 'required|numeric|min:0',

                'dia_chi_id' => 'sometimes|integer|exists:dia_chi,id_dia_chi',
                'dia_chi_moi' => 'sometimes|array',
                'dia_chi_moi.ho_ten' => 'required_with:dia_chi_moi|string|max:255',
                'dia_chi_moi.sdt' => 'required_with:dia_chi_moi|string|max:20',
                'dia_chi_moi.dia_chi' => 'required_with:dia_chi_moi|string|max:255',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Lỗi Validation khi đặt hàng: ' . json_encode($e->errors()));
            return response()->json(['message' => 'Dữ liệu đầu vào không hợp lệ.', 'errors' => $e->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $shippingAddressId = null;
            if (isset($validatedData['dia_chi_id'])) {
                $shippingAddress = DiaChi::find($validatedData['dia_chi_id']);
                if (!$shippingAddress || ($shippingAddress->nguoi_dung_id != $userId && !($user->isAdmin ?? false))) {
                    DB::rollBack();
                    return response()->json(['message' => 'Địa chỉ giao hàng không hợp lệ hoặc không thuộc về bạn.'], 400);
                }
                $shippingAddressId = $shippingAddress->id_dia_chi ?? $shippingAddress->id;
            } elseif (isset($validatedData['dia_chi_moi'])) {
                $newAddress = DiaChi::create([
                    'nguoi_dung_id' => $userId,
                    'ho_ten' => $validatedData['dia_chi_moi']['ho_ten'],
                    'sdt' => $validatedData['dia_chi_moi']['sdt'],
                    'dia_chi' => $validatedData['dia_chi_moi']['dia_chi'],
                ]);
                $shippingAddressId = $newAddress->id_dia_chi ?? $newAddress->id;
            } else {
                DB::rollBack();
                return response()->json(['message' => 'Vui lòng cung cấp địa chỉ giao hàng.'], 400);
            }

            $order = Order::create([
                'nguoi_dung_id' => $userId,
                'tong_tien' => $validatedData['tong_tien'],
                'phuong_thuc_thanh_toan_id' => $validatedData['phuong_thuc_thanh_toan_id'],
                'hinh_thuc_giao_hang' => $validatedData['hinh_thuc_giao_hang'],
                'phi_van_chuyen' => $validatedData['phi_van_chuyen'],
                'dia_chi_id' => $shippingAddressId,
                'ngay_dat' => now(),
                'trang_thai_don_hang' => '1',
                'is_paid' => 0,
            ]);

            foreach ($validatedData['san_pham'] as $item) {
                $bienThe = SanPhamBienThe::find($item['bien_the_id']);
                if (!$bienThe || $bienThe->so_luong_ton_kho < $item['so_luong']) {
                    DB::rollBack();
                    return response()->json(['message' => 'Sản phẩm "' . ($bienThe->ten_bien_the ?? 'ID: ' . $item['bien_the_id']) . '" không đủ số lượng tồn kho hoặc không tồn tại.'], 400);
                }

                OrderItem::create([
                    'don_hang_id' => $order->id,
                    'san_pham_bien_the_id' => $item['bien_the_id'],
                    'so_luong' => $item['so_luong'],
                    'don_gia' => $bienThe->gia,
                    'thanh_tien' => $item['so_luong'] * $bienThe->gia,
                ]);

                // Giảm số lượng tồn kho của sản phẩm (Bỏ comment nếu muốn kích hoạt)
                $bienThe->so_luong_ton_kho -= $item['so_luong'];
                $bienThe->save();
            }

            // Xóa/Làm trống giỏ hàng của người dùng sau khi đặt hàng thành công
            // (Không còn dựa vào cột tong_tien/trang_thai trên Cart Model)
            $cart = Cart::where('nguoi_dung_id', $userId)->first();
            if ($cart) {
                $cart->chiTiet()->delete(); // Xóa tất cả chi tiết giỏ hàng liên quan đến giỏ hàng này
                $cart->delete(); // Xóa bản ghi giỏ hàng chính
                // HOẶC: Nếu bạn muốn giữ lại bản ghi giỏ hàng nhưng làm trống nó và thay đổi trạng thái:
                // $cart->update(['ngay_sua' => now()]); // Cập nhật thời gian sửa
            }

            DB::commit();

            return response()->json(['message' => 'Đặt hàng thành công!', 'order_id' => $order->id], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đặt hàng: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return response()->json(['message' => 'Không thể đặt hàng. Vui lòng thử lại sau.', 'error_detail' => $e->getMessage()], 500);
        }
    }
}
