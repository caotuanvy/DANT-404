<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google_Client; 
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Đảm bảo đã import Auth

class GoogleAuthController extends Controller
{
    public function handleGoogleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string'
        ]);

        // Thay thế bằng CLIENT_ID của bạn từ Google Cloud Console
        // PHẢI LÀ CLIENT ID WEB APPLICATION
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID', '710318641931-iqof2cq4qcg68fnccru81o31n7gbdqc0.apps.googleusercontent.com')]);
        // Tốt hơn nên đặt client ID vào .env: GOOGLE_CLIENT_ID="710318641931-iqof2cq4qcg68fnccru81o31n7gbdqc0.apps.googleusercontent.com"

        $payload = $client->verifyIdToken($request->id_token);

        if (!$payload) {
            return response()->json(['message' => 'Token không hợp lệ'], 401);
        }

        $email = $payload['email'];
        $name = $payload['name'] ?? $email;

        // Tìm hoặc tạo người dùng
        // Lưu ý: Khi tạo mới, trang_thai sẽ là 0 (hoạt động)
        // Nếu người dùng đã tồn tại, trang_thai sẽ được lấy từ DB
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'ho_ten' => $name,
                'mat_khau' => Hash::make(Str::random(16)), 
                'sdt' => '', 
                'vai_tro_id' => 0, 
                'is_active' => 1, // Đăng nhập Google thường tự động kích hoạt tài khoản
                'trang_thai' => 0, // Mặc định là hoạt động khi tạo mới qua Google
            ]
        );

        // --- BẮT ĐẦU PHẦN THÊM MỚI/CẬP NHẬT ---
        // Kiểm tra tài khoản có bị admin vô hiệu hóa không
        // Theo quy ước của bạn: trang_thai = 1 nghĩa là đã bị vô hiệu hóa bởi admin
        if ($user->trang_thai === 1) { // Sử dụng === 1 để đảm bảo kiểu dữ liệu
            // Đăng xuất người dùng ngay lập tức nếu họ đã được Auth::login()
            // Auth::logout(); // Chỉ cần nếu bạn dùng session-based auth với Auth::login()
            
            return response()->json([
                'message' => 'Tài khoản Google của bạn đã bị vô hiệu hóa bởi quản trị viên. Vui lòng liên hệ bộ phận hỗ trợ.',
                'status' => 'disabled', // Cờ cho frontend để dễ dàng nhận biết
            ], 403); // 403 Forbidden - Lỗi quyền truy cập
        }

        // Đảm bảo bạn đã cài đặt và cấu hình Sanctum đúng cách
        $token = $user->createToken('google-login-token')->plainTextToken; // 'google-login-token' là tên token tùy ý

        return response()->json([
            'user' => [ // Trả về thông tin user đầy đủ hơn, bao gồm trang_thai
                'nguoi_dung_id' => $user->nguoi_dung_id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'sdt' => $user->sdt,
                'vai_tro_id' => $user->vai_tro_id,
                'is_active' => $user->is_active,
                'trang_thai' => $user->trang_thai, // THÊM TRƯỜNG NÀY VÀO PHẢN HỒI
            ],
            'token' => $token
        ]);
    }
}
