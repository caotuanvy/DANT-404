<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google_Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function handleGoogleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string'
        ]);

        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID', '710318641931-iqof2cq4qcg68fnccru81o31n7gbdqc0.apps.googleusercontent.com')]);

        try {
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json(['message' => 'Token không hợp lệ'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi xác thực token: ' . $e->getMessage()], 401);
        }
        
        $email = $payload['email'];
        $name = $payload['name'] ?? $email;
        $pictureUrl = $payload['picture'] ?? null;

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'ho_ten' => $name,
                'mat_khau' => Hash::make(Str::random(16)),
                'sdt' => '',
                'vai_tro_id' => 0,
                'is_active' => 1,
                'trang_thai' => 0,
            ]
        );

        if ($user->trang_thai === 1) {
            return response()->json([
                'message' => 'Tài khoản Google của bạn đã bị vô hiệu hóa bởi quản trị viên.',
                'status' => 'disabled',
            ], 403);
        }

        // --- CẬP NHẬT ẢNH ĐẠI DIỆN: TẢI VÀ LƯU VÀO SERVER CỦA BẠN ---
        // Chỉ tải ảnh nếu người dùng chưa có ảnh đại diện hoặc URL ảnh đại diện là từ Google
        if (!$user->anh_dai_dien || Str::startsWith($user->anh_dai_dien, 'https://lh3.googleusercontent.com')) {
            if ($pictureUrl) {
                try {
                    // Tải ảnh từ URL của Google
                    $response = Http::get($pictureUrl);
                    if ($response->successful()) {
                        $imageContent = $response->body();
                        // Tạo tên file duy nhất để tránh trùng lặp
                        $fileName = 'avatars/' . Str::slug($user->email) . '_' . time() . '.jpg';
                        // Lưu file vào thư mục public/avatars
                        Storage::disk('public')->put($fileName, $imageContent);

                        // Cập nhật đường dẫn mới vào database
                        $user->anh_dai_dien = url('/storage/' . $fileName);
                        $user->save();
                    }
                } catch (\Exception $e) {
                    // Xử lý lỗi nếu việc tải ảnh thất bại
                    Log::error('Failed to download Google avatar: ' . $e->getMessage());
                }
            }
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
                // Trả về đường dẫn ảnh đã được lưu trên server của bạn
                'anh_dai_dien' => $user->anh_dai_dien, 
                'trang_thai' => $user->trang_thai,
            ],
            'token' => $token
        ]);
    }
}
