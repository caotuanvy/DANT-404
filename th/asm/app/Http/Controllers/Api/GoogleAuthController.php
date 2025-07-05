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

        // Tìm hoặc tạo người dùng
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'ho_ten' => $name,
                'mat_khau' => Hash::make(Str::random(16)), 
                'sdt' => '', 
                'vai_tro_id' => 0, 
                'is_active' => 1
            ]
        );

        // Đăng nhập người dùng Laravel (nếu cần session-based auth)
        // Auth::login($user); // Có thể bỏ qua nếu chỉ dùng Sanctum token API

        // Tạo token Sanctum
        // Đảm bảo bạn đã cài đặt và cấu hình Sanctum đúng cách
        $token = $user->createToken('google-login-token')->plainTextToken; // 'google-login-token' là tên token tùy ý

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}