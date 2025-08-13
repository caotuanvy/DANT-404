<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google_Client; 
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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

        // LẤY URL ẢNH ĐẠI DIỆN TỪ PAYLOAD CỦA GOOGLE TẠI ĐÂY
        $picture = $payload['picture'] ?? null;

        // Tìm hoặc tạo người dùng
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'ho_ten' => $name,
                'mat_khau' => Hash::make(Str::random(16)), 
                'sdt' => '', 
                'vai_tro_id' => 0, 
                'is_active' => 1,
                'trang_thai' => 0,
                'anh_dai_dien' => $picture, // SỬ DỤNG ẢNH ĐẠI DIỆN CỦA GOOGLE
            ]
        );

        // --- BẮT ĐẦU PHẦN THÊM MỚI/CẬP NHẬT ---
        // Kiểm tra tài khoản có bị admin vô hiệu hóa không
        if ($user->trang_thai === 1) { 
            return response()->json([
                'message' => 'Tài khoản Google của bạn đã bị vô hiệu hóa bởi quản trị viên. Vui lòng liên hệ bộ phận hỗ trợ.',
                'status' => 'disabled',
            ], 403);
        }

        // CẬP NHẬT ẢNH ĐẠI DIỆN NẾU CHƯA TỒN TẠI
        if (!$user->anh_dai_dien && $picture) {
            $user->anh_dai_dien = $picture;
            $user->save();
        }
        
        $token = $user->createToken('google-login-token')->plainTextToken;

        return response()->json([
            'user' => [
                'nguoi_dung_id' => $user->nguoi_dung_id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'sdt' => $user->sdt,
                'vai_tro_id' => $user->vai_tro_id,
                'is_active' => $user->is_active,
                'anh_dai_dien' => $user->anh_dai_dien,
                'trang_thai' => $user->trang_thai, 
            ],
            'token' => $token
        ]);
    }
}
