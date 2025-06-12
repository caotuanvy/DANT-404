<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\KichHoatTaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


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

    // Đăng nhập
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
}
