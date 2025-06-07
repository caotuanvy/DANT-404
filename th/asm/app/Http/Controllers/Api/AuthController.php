<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    // Đăng ký
   public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'ho_ten' => 'required|string|max:255',
        'email' => 'required|email|unique:nguoi_dung,email',
        'sdt' => 'required|string|max:20',
        'mat_khau' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Đăng ký thất bại',
            'errors' => $validator->errors(),
        ], 422);
    }

    $user = User::create([
        'ho_ten' => $request->ho_ten,
        'email' => $request->email,
        'sdt' => $request->sdt,
        'mat_khau' => Hash::make($request->mat_khau),
        'vai_tro_id' => 2, // mặc định là user
        'trang_thai' => 1,
        'ngay_tao' => now(),
    ]);

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
        'message' => 'Đăng ký thành công',
        'user' => $user,
        'token' => $token,
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
                'vai_tro_id' => $user->vai_tro_id,
            ],
            'token' => $token,
        ]);
    }
}
