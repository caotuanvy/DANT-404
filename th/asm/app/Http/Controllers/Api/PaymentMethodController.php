<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
class PaymentMethodController extends Controller
{
    /**
     * Trả về danh sách tất cả các phương thức thanh toán.
     */
    public function index()
    {
        try {
            // Lấy các cột cần thiết từ CSDL
            $methods = PaymentMethod::select('phuong_thuc_thanh_toan_id as id', 'ten_pttt')
                ->get();
            return response()->json($methods);
        } catch (\Exception $e) {
            // Trả về thông báo lỗi cho client
            return response()->json(['message' => 'Không thể tải danh sách phương thức thanh toán'], 500);
        }
    }
}
