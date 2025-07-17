<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Import Carbon để xử lý ngày tháng

class OrderController extends Controller
{
    /**
     * Lấy tất cả đơn hàng (cho quản trị viên) với các bộ lọc tìm kiếm nâng cao.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
    // Bắt đầu câu truy vấn, chỉ lấy đơn hàng chưa bị ẩn
    $query = Order::whereNull('ngay_xoa')
        ->with('user', 'paymentMethod', 'orderItems.bien_the.sanPham', 'diachi');

    // Bộ lọc trạng thái
    if ($request->has('status') && $request->status) {
        $query->where('trang_thai', $request->status);
    }

    // Bộ lọc tìm kiếm theo mã đơn hàng hoặc tên khách hàng
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('id', 'like', "%$search%")
              ->orWhereHas('user', function ($userQuery) use ($search) {
                  $userQuery->where('ho_ten', 'like', "%$search%");
              });
        });
    }

    // Bộ lọc theo ngày đặt hàng
    if ($request->has('date') && $request->date) {
        try {
            $date = Carbon::parse($request->date)->toDateString();
            $query->whereDate('ngay_tao', $date);
        } catch (\Exception $e) {
            // Bỏ qua nếu định dạng ngày không hợp lệ
        }
    }

    // Bộ lọc theo phương thức thanh toán
    if ($request->has('payment_method') && $request->payment_method) {
        $query->where('phuong_thuc_thanh_toan_id', $request->payment_method);
    }

    // Bộ lọc theo khoảng giá
    if ($request->has('price_range') && $request->price_range) {
        $priceParts = explode('-', $request->price_range);
        if (count($priceParts) == 2) {
            $minPrice = (int) $priceParts[0];
            $maxPrice = (int) $priceParts[1];
            $query->whereBetween('tong_tien', [$minPrice, $maxPrice]);
        }
    }

    // Xử lý phân trang
    $perPage = $request->input('per_page', 10); // Số đơn hàng mỗi trang, mặc định 10
    $orders = $query->orderBy('ngay_tao', 'desc')->paginate($perPage);

    return response()->json($orders);
}

    /**
     * Ẩn một đơn hàng bằng cách đặt thời gian xóa mềm.
     *
     * @param  int  $id ID của đơn hàng cần ẩn
     * @return \Illuminate\Http\JsonResponse
     */
    public function hideOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->ngay_xoa = now();
        $order->save();
        return response()->json(['message' => 'Đã ẩn đơn hàng thành công']);
    }

    /**
     * Cập nhật trạng thái của một đơn hàng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id ID của đơn hàng cần cập nhật
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|integer|min:1',
        ]);

        $order = Order::findOrFail($id);
        $order->trang_thai = $request->input('trang_thai');
        $order->save();
        return response()->json($order);
    }

    /**
     * Lấy lịch sử đơn hàng của người dùng hiện tại.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userOrders(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Bạn cần đăng nhập để xem lịch sử đơn hàng.'], 401);
        }

        $query = Order::where('nguoi_dung_id', $userId)
            ->with('paymentMethod', 'orderItems.bien_the.san_pham', 'diachi');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhereHas('orderItems.bien_the.san_pham', function ($q3) use ($search) {
                      $q3->where('ten_san_pham', 'like', "%$search%");
                  });
            });
        }

        $orders = $query->orderBy('ngay_tao', 'desc')->get();
        return response()->json($orders);
    }
    public function confirmPayment(Request $request, Order $order)
        {
            // Nếu đã thanh toán rồi thì không xác nhận lại
            if ($order->is_paid == 1) {
                return response()->json(['message' => 'Đơn hàng này đã được xác nhận thanh toán trước đó.'], 400);
            }
            $order->is_paid = 1;
            $order->save();

            return response()->json([
                'message' => 'Xác nhận thanh toán thành công',
                'order' => $order
            ]);
        }
    /**
     * Tạo một đơn hàng mới.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // ... (Giữ nguyên hàm store của bạn)
    }
}
