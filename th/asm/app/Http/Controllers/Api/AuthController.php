<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\KichHoatTaiKhoan;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; 


class AuthController extends Controller
{
    // Đăng ký
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dung,email',
            'mat_khau' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Đăng ký thất bại',
                'errors' => $validator->errors(),
            ], 422);
        }

        $activation_token = Str::random(60);

        $user = User::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'sdt' => $request->sdt,
            'mat_khau' => Hash::make($request->mat_khau),
            'vai_tro_id' => 0,
            'trang_thai' => 0,
            'ngay_tao' => now(),
            'activation_token' => $activation_token,
            'is_active' => 0,
        ]);

        // Gửi email kích hoạt
        $activationLink = config('app.frontend_url') . '/kich-hoat?token=' . $activation_token;
        Mail::to($user->email)->send(new KichHoatTaiKhoan($user, $activationLink));



        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để kích hoạt tài khoản.',
            'user' => $user,
        ]);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->mat_khau, $user->mat_khau)) {
            return response()->json(['message' => 'Sai email hoặc mật khẩu'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => [
                'nguoi_dung_id' => $user->nguoi_dung_id,
                'ho_ten' => $user->ho_ten,
                'email' => $user->email,
                'sdt' => $user->sdt,
                'vai_tro_id' => $user->vai_tro_id,
                'is_active' => $user->is_active,
            ],
            'token' => $token,
        ]);
    }
    public function activate($token)
    {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Liên kết không hợp lệ hoặc đã hết hạn.'], 400);
        }

        $user->is_active = 1;
        $user->activation_token = null;
        $user->save();

        return response()->json(['message' => 'Tài khoản của bạn đã được kích hoạt. Bạn có thể đăng nhập.']);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Địa chỉ email không tồn tại trong hệ thống của chúng tôi.'], 404);
        }

        // 1. Tạo mật khẩu ngẫu nhiên mạnh
        $newPassword = Str::random(8); // Bắt đầu với 8 ký tự
        $newPassword .= Str::upper(Str::random(1)); // Thêm 1 chữ hoa
        $newPassword .= Str::lower(Str::random(1)); // Thêm 1 chữ thường
        $newPassword .= mt_rand(0, 9); // Thêm 1 số
        $newPassword .= Str::random(1, '!@#$%^&*()_+{}[]:;<>,.?/~`')[0]; // Thêm 1 ký tự đặc biệt
        $newPassword = str_shuffle($newPassword); // Xáo trộn để ngẫu nhiên

        // 2. Cập nhật mật khẩu mới vào cơ sở dữ liệu
        // Do bạn đã có 'mat_khau' => 'hashed' trong $casts của User model, Laravel sẽ tự động hash mật khẩu
        $user->mat_khau = $newPassword; 
        $user->save();

        // 3. Gửi email chứa mật khẩu mới
        try {
            Mail::to($user->email)->send(new PasswordResetMail($newPassword));
            return response()->json(['message' => 'Mật khẩu mới đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư đến (bao gồm cả thư mục spam).'], 200);
        } catch (\Exception $e) {
            Log::error('Error sending password reset email to ' . $user->email . ': ' . $e->getMessage());
            return response()->json(['message' => 'Đã xảy ra lỗi khi gửi email. Vui lòng thử lại sau.'], 500);
        }
    }
}
