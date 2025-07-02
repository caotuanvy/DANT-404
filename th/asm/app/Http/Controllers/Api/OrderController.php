<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Models\Order; // Model cho bảng 'don_hang'
use App\Http\Controllers\Controller;
use App\Models\DiaChi; // Model cho bảng 'dia_chi'
use App\Models\ChiTietDonHang; // Model CHÍNH XÁC cho bảng 'chi_tiet_don_hang'
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade

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
            ->with([
                'user', // Mối quan hệ với người dùng
                'paymentMethod', // Mối quan hệ với phương thức thanh toán
                'chiTietDonHang.sanPhamBienThe.sanPhamGoc', // Mối quan hệ chi tiết đơn hàng -> sản phẩm biến thể -> sản phẩm gốc
                'diachi' // Mối quan hệ với địa chỉ
            ]);

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
            ->with([
                'paymentMethod', // Mối quan hệ với phương thức thanh toán
                'chiTietDonHang.sanPhamBienThe.sanPhamGoc', // Mối quan hệ chi tiết đơn hàng -> sản phẩm biến thể -> sản phẩm gốc
                'diachi' // Mối quan hệ với địa chỉ
            ]);

        // Tùy chọn tìm kiếm trong lịch sử đơn hàng của người dùng (ví dụ: theo ID đơn hàng hoặc tên sản phẩm)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Tìm kiếm theo ID đơn hàng
                $q->where('id', 'like', "%$search%")
                    // Hoặc tìm kiếm theo tên sản phẩm trong các mục của đơn hàng
                    ->orWhereHas('chiTietDonHang.sanPhamBienThe.sanPhamGoc', function ($q3) use ($search) {
                        $q3->where('ten_san_pham', 'like', "%$search%");
                    });
            });
        }

        // Lấy tất cả các đơn hàng phù hợp của người dùng
        $orders = $query->get();
        return response()->json($orders);
    }

    /**
     * Xử lý việc tạo một đơn hàng mới từ giỏ hàng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào từ request
        $request->validate([
            'nguoi_dung_id' => 'required|exists:nguoi_dung,nguoi_dung_id',
            'tong_tien' => 'required|numeric',
            'phuong_thuc_thanh_toan_id' => 'required|exists:phuong_thuc_thanh_toan,phuong_thuc_thanh_toan_id',
            'dia_chi_id' => 'nullable|exists:dia_chi,id_dia_chi',
            'hinh_thuc_giao_hang' => 'required|string',
            'san_pham' => 'required|array',
            'san_pham.*.bien_the_id' => 'required|exists:san_pham_bien_the,bien_the_id', // Kiểm tra ID biến thể phải tồn tại
            'san_pham.*.so_luong' => 'required|integer|min:1',
            'san_pham.*.don_gia' => 'required|numeric|min:0',
            // Thêm validate cho dia_chi_moi nếu có
            'dia_chi_moi.ho_ten' => 'nullable|string|max:255',
            'dia_chi_moi.sdt' => 'nullable|string|max:20',
            'dia_chi_moi.dia_chi' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction(); // Bắt đầu giao dịch cơ sở dữ liệu
        try {
            // Tạo đơn hàng chính, tạm thời chưa gán dia_chi_id
            $order = Order::create([
                'nguoi_dung_id' => $request->nguoi_dung_id,
                'tong_tien' => $request->tong_tien,
                'phuong_thuc_thanh_toan_id' => $request->phuong_thuc_thanh_toan_id,
                'hinh_thuc_giao_hang' => $request->hinh_thuc_giao_hang,
                'trang_thai' => 1, // Trạng thái mặc định là "Chờ xử lý"
                'ngay_tao' => now(), // Gán thời gian tạo cho bảng `don_hang`
            ]);

            // Xử lý địa chỉ giao hàng
            if (!$request->filled('dia_chi_id') && $request->has('dia_chi_moi')) {
                // Nếu không có dia_chi_id nhưng có thông tin địa chỉ mới được cung cấp
                $diaChiMoi = $request->input('dia_chi_moi');
                $newAddress = DiaChi::create([ // Sử dụng Model DiaChi
                    'nguoi_dung_id' => $request->nguoi_dung_id,
                    'ho_ten' => $diaChiMoi['ho_ten'],
                    'sdt' => $diaChiMoi['sdt'],
                    'dia_chi' => $diaChiMoi['dia_chi'],
                    // Giả định bảng `dia_chi` có các cột này, nếu không hãy bỏ đi
                    'ngay_tao' => now(), 
                    'ngay_sua' => now(),
                ]);
                $order->dia_chi_id = $newAddress->id_dia_chi; // Gán ID địa chỉ mới vào đơn hàng
                $order->save(); // Lưu lại đơn hàng với dia_chi_id
            } elseif ($request->filled('dia_chi_id')) {
                // Nếu đã có dia_chi_id được gửi lên (địa chỉ đã tồn tại)
                $order->dia_chi_id = $request->dia_chi_id;
                $order->save(); // Lưu lại đơn hàng với dia_chi_id
            }

            // Lặp qua các sản phẩm trong giỏ hàng và tạo chi tiết đơn hàng
            foreach ($request->san_pham as $sp) {
                ChiTietDonHang::create([ // <-- ĐÃ SỬ DỤNG MODEL ChiTietDonHang
                    'don_hang_id' => $order->id,
                    'san_pham_bien_the_id' => $sp['bien_the_id'], // <-- ĐÃ ĐỔI TÊN CỘT ĐỂ KHỚP VỚI DB
                    'so_luong' => $sp['so_luong'],
                    'don_gia' => $sp['don_gia'],
                ]);
            }

            DB::commit(); // Hoàn tất giao dịch
            return response()->json(['message' => 'Đơn hàng đã được tạo thành công', 'order_id' => $order->id], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác giao dịch nếu có lỗi
            // Log lỗi chi tiết để dễ dàng debug hơn
            Log::error("Lỗi tạo đơn hàng: " . $e->getMessage(), [
                'exception' => $e,
                'request_payload' => $request->all(),
            ]);
            return response()->json(['message' => 'Lỗi tạo đơn hàng', 'error' => $e->getMessage()], 500);
        }
    }
}
