<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\DiaChi;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import facade Auth để lấy người dùng hiện tại
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Lấy tất cả đơn hàng (thường dùng cho quản trị viên) với các bộ lọc tìm kiếm.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Truy vấn các đơn hàng chưa bị xóa mềm ('ngay_xoa' là null)
        $query = Order::whereNull('ngay_xoa')
            // Tải các mối quan hệ liên quan để hiển thị thông tin chi tiết
            ->with('user', 'paymentMethod', 'orderItems.bien_the.sanPham', 'diachi');

        // Áp dụng bộ lọc tìm kiếm nếu có
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Tìm kiếm theo ID đơn hàng
                $q->where('id', 'like', "%$search%")
                    // Hoặc tìm kiếm theo tên người dùng liên quan đến đơn hàng
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('ho_ten', 'like', "%$search%");
                    });
            });
        }

        // Lấy tất cả các đơn hàng phù hợp
        $orders = $query->get();
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
        $order = Order::findOrFail($id); // Tìm đơn hàng theo ID hoặc ném lỗi 404
        $order->ngay_xoa = now(); // Đặt cột 'ngay_xoa' thành thời gian hiện tại
        $order->save(); // Lưu thay đổi vào cơ sở dữ liệu
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
        $order = Order::findOrFail($id); // Tìm đơn hàng theo ID hoặc ném lỗi 404
        $order->trang_thai = $request->input('trang_thai'); // Cập nhật trạng thái từ request
        $order->save(); // Lưu thay đổi
        return response()->json($order); // Trả về đơn hàng đã được cập nhật
    }

    /**
     * Lấy lịch sử đơn hàng của người dùng hiện tại.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userOrders(Request $request)
    {
        // Lấy ID của người dùng đang đăng nhập
        $userId = Auth::id(); // Hoặc auth()->id();

        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (!$userId) {
            return response()->json(['message' => 'Bạn cần đăng nhập để xem lịch sử đơn hàng.'], 401);
        }

        // Truy vấn các đơn hàng thuộc về người dùng hiện tại và chưa bị xóa mềm
        $query = Order::where('nguoi_dung_id', $userId)
            // Tải các mối quan hệ cần thiết để hiển thị chi tiết đơn hàng
            ->with('paymentMethod', 'orderItems.bien_the.sanPham', 'diachi');

        // Tùy chọn tìm kiếm trong lịch sử đơn hàng của người dùng (ví dụ: theo ID đơn hàng hoặc tên sản phẩm)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Tìm kiếm theo ID đơn hàng
                $q->where('id', 'like', "%$search%")
                    // Hoặc tìm kiếm theo tên sản phẩm trong các mục của đơn hàng
                    ->orWhereHas('orderItems.bien_the.sanPham', function ($q3) use ($search) {
                        $q3->where('ten_san_pham', 'like', "%$search%");
                    });
            });
        }

        // Lấy tất cả các đơn hàng phù hợp của người dùng
        $orders = $query->get();
        return response()->json($orders);
    }
    public function store(Request $request)
    {
        $request->validate([
            'nguoi_dung_id' => 'required|exists:nguoi_dung,nguoi_dung_id',
            'tong_tien' => 'required|numeric',
            'phuong_thuc_thanh_toan_id' => 'required|exists:phuong_thuc_thanh_toan,phuong_thuc_thanh_toan_id',
            'dia_chi_id' => 'nullable|exists:dia_chi,id_dia_chi',
            'hinh_thuc_giao_hang' => 'required|string',
            'san_pham' => 'required|array',
            'san_pham.*.bien_the_id' => 'required|exists:san_pham_bien_the,bien_the_id',
            'san_pham.*.so_luong' => 'required|integer|min:1',
            'san_pham.*.don_gia' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Tạo đơn hàng trước, tạm chưa có dia_chi_id
            $order = Order::create([
                'nguoi_dung_id' => $request->nguoi_dung_id,
                'tong_tien' => $request->tong_tien,
                'phuong_thuc_thanh_toan_id' => $request->phuong_thuc_thanh_toan_id,
                'hinh_thuc_giao_hang' => $request->hinh_thuc_giao_hang,
                'trang_thai' => 1,
                'ngay_tao' => now(),
            ]);

            // Nếu không có dia_chi_id nhưng có thông tin địa chỉ mới
            if (!$request->filled('dia_chi_id') && $request->has('dia_chi_moi')) {
                $diaChiMoi = $request->input('dia_chi_moi');
                $newAddress = \App\Models\DiaChi::create([
                    'nguoi_dung_id' => $request->nguoi_dung_id,
                    'ho_ten' => $diaChiMoi['ho_ten'],
                    'sdt' => $diaChiMoi['sdt'],
                    'dia_chi' => $diaChiMoi['dia_chi'],
                ]);

                $order->dia_chi_id = $newAddress->id_dia_chi;
                $order->save();
            } elseif ($request->filled('dia_chi_id')) {
                $order->dia_chi_id = $request->dia_chi_id;
                $order->save();
            }

            foreach ($request->san_pham as $sp) {
                \App\Models\OrderItem::create([
                    'don_hang_id' => $order->id,
                    'bien_the_id' => $sp['bien_the_id'],
                    'so_luong' => $sp['so_luong'],
                    'don_gia' => $sp['don_gia'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Đơn hàng đã được tạo thành công', 'order_id' => $order->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi tạo đơn hàng', 'error' => $e->getMessage()], 500);
        }
    }
}
