<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SanPhamBienThe;
use App\Models\Cart;
use App\Models\CartItem;


use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createVnpayPayment(Request $request)
    {
        try {
            $total = $request->input('total');
            $userId = $request->input('user_id');
            $cartItems = $request->input('cart');
            $addressId = $request->input('dia_chi_id');
            $phiVanChuyen = $request->input('phi_van_chuyen');
            $maGiamGia = $request->input('ma_giam_gia');
            $ghiChu = $request->input('ghi_chu');

            if (!$cartItems || !$total || !$userId) {
                return response()->json(['error' => 'Dữ liệu không hợp lệ'], 400);
            }
            $order = Order::create([
                'nguoi_dung_id' => $userId,
                'phuong_thuc_thanh_toan_id' => 2, // 2 = VNPAY
                'dia_chi_id' => $addressId,
                'phi_van_chuyen' => $phiVanChuyen,
                'tong_tien' => $total,
                'ma_giam_gia' => $maGiamGia,
                'ghi_chu' => $ghiChu,
                'trang_thai_don_hang' => 1, // 1 = Chờ xác nhận
                'ngay_dat' => now(),
            ]);

            // Lưu các sản phẩm vào chi tiết đơn hàng (order_items)
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'don_hang_id' => $order->id,
                    'san_pham_bien_the_id' => $item['san_pham_bien_the_id'],
                    'so_luong' => $item['so_luong'],
                    'don_gia' => $item['don_gia'],
                    'thanh_tien' => $item['thanh_tien']
                ]);
            }

            // BƯỚC 2: CHUẨN BỊ DỮ LIỆU VNPAY
            $vnp_TmnCode = env('VNP_TMNCODE');
            $vnp_HashSecret = env('VNP_HASHSECRET');
            $vnp_Url = env('VNPAY_URL');
            $vnp_Returnurl = "https://localhost:5174/paymentsuccess";

            // SỬ DỤNG ID CỦA ĐƠN HÀNG TẠM THỜI LÀM VNP_TXNREF
            $vnp_TxnRef = $order->id;
            $vnp_OrderInfo = 'Thanh toán đơn hàng #' . $vnp_TxnRef;
            $vnp_Amount = $total * 100;
            $vnp_Locale = 'vn';
            $vnp_IpAddr = $request->ip();

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
                "vnp_OrderType" => "other",
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            ksort($inputData);
            $query = http_build_query($inputData);
            $vnp_SecureHash = hash_hmac('sha512', $query, $vnp_HashSecret);
            $paymentUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

            return response()->json(['payment_url' => $paymentUrl]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Lỗi khi tạo VNPAY URL'], 500);
        }
    }
public function handleCallback(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASHSECRET');
        $inputData = $request->all();
        $secureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);

        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        $secureHashLocal = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHashLocal == $secureHash) {
            $orderId = $request->query('vnp_TxnRef');
            $responseCode = $request->query('vnp_ResponseCode');
            $order = Order::find($orderId);

            if ($order) {
                if ($responseCode == '00') {
                    $order->trang_thai_don_hang = 2;
                    $order->save();

                    foreach ($order->orderItems as $item) {
                        SanPhamBienThe::where('bien_the_id', $item->san_pham_bien_the_id)
                                      ->decrement('so_luong_ton_kho', $item->so_luong);
                    }


                   $cart = Cart::where('nguoi_dung_id', $order->nguoi_dung_id)->first();
                        if ($cart) {
                            $cart->cartItems()->delete();
                        }

                    return redirect("http://localhost:5174/paymentsuccess/{$orderId}?success=1");
                } else {
                    // Thanh toán thất bại, CẬP NHẬT TRẠNG THÁI
                    $order->trang_thai_don_hang = 5; // 5 = Đã hủy
                    $order->save();

                    // VNPAY không chuyển hướng lại URL với ID đơn hàng, bạn sẽ cần thêm nó vào đây
                    return redirect("http://localhost:5174/paymentsuccess/{$orderId}?success=0");
                }
            } else {
                Log::error("Không tìm thấy đơn hàng: " . $orderId);
                return redirect("http://localhost:5174/error");
            }
        } else {
            Log::error("Chữ ký VNPAY không hợp lệ");
            return redirect("http://localhost:5174/error");
        }
    }
    public function checkPayment(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASHSECRET');
        $inputData = $request->except('vnp_SecureHash');
        $secureHash = $request->query('vnp_SecureHash');
        ksort($inputData);
        $hashData = http_build_query($inputData);
        $secureHashLocal = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($secureHashLocal == $secureHash) {
            $orderId = $request->query('vnp_TxnRef');
            $responseCode = $request->query('vnp_ResponseCode');
            $order = Order::find($orderId);

            if ($order) {
                if ($responseCode == '00') {
                    $order->status = 'Đã thanh toán';
                    $order->save();

                    Log::info("Giao dịch VNPAY thành công cho đơn hàng: " . $orderId);

                    return response()->json([
                        'success' => true,
                        'message' => 'Thanh toán thành công!',
                        'order_id' => $orderId
                    ]);
                } else {
                    $order->status = 'Thanh toán thất bại';
                    $order->save();

                    Log::warning("Giao dịch VNPAY thất bại cho đơn hàng: " . $orderId . " với mã lỗi: " . $responseCode);

                    return response()->json([
                        'success' => false,
                        'message' => 'Thanh toán thất bại. Vui lòng thử lại.',
                        'order_id' => $orderId
                    ]);
                }
            } else {
                Log::error("Không tìm thấy đơn hàng: " . $orderId . " từ VNPAY callback.");
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.']);
            }
        } else {
            Log::error("Chữ ký VNPAY không hợp lệ.");
            return response()->json(['success' => false, 'message' => 'Chữ ký giao dịch không hợp lệ.']);
        }
    }
}
