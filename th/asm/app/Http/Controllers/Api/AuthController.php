<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\KichHoatTaiKhoan;
use App\Mail\SendOtpCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    // Đăng ký
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dung,email',
            'mat_khau' => 'required|string|min:8|confirmed', // 'confirmed' yêu cầu trường 'mat_khau_confirmation'
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
            'sdt' => $request->sdt, // Đảm bảo trường 'sdt' tồn tại trong form nếu bạn muốn lưu
            'mat_khau' => Hash::make($request->mat_khau),
            'vai_tro_id' => 0, // Giá trị mặc định
            'trang_thai' => 0, // Giá trị mặc định
            'ngay_tao' => now(),
            'activation_token' => $activation_token,
            'is_active' => 0,
        ]);

        // Gửi email kích hoạt
        // Đảm bảo config('app.frontend_url') đã được thiết lập trong file .env
        // Ví dụ: FRONTEND_URL=http://localhost:5173
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

        // 1. Kiểm tra tồn tại người dùng và mật khẩu
        if (!$user || !Hash::check($request->mat_khau, $user->mat_khau)) {
            return response()->json(['message' => 'Sai email hoặc mật khẩu'], 401);
        }

        // 2. Kiểm tra tài khoản đã kích hoạt chưa (di chuyển vào đây)
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email để kích hoạt hoặc yêu cầu gửi lại email kích hoạt.',
                'require_activation' => true, // Cờ cho frontend
            ], 403); // 403 Forbidden - Lỗi quyền truy cập
        }

        // 3. Tạo token nếu tất cả đều hợp lệ
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


    // Kích hoạt tài khoản qua email
    public function activate($token)
    {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Liên kết không hợp lệ hoặc đã hết hạn.'], 400);
        }

        $user->is_active = 1;
        $user->activation_token = null; // Xóa token sau khi kích hoạt
        $user->save();

        return response()->json(['message' => 'Tài khoản của bạn đã được kích hoạt. Bạn có thể đăng nhập.']);
    }

    // Gửi mã xác nhận OTP để đặt lại mật khẩu
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            // Trả về thông báo chung chung để tránh lộ thông tin email tồn tại hay không
            return response()->json(['message' => 'Nếu email tồn tại, mã xác nhận sẽ được gửi đến email đó.'], 200);
        }

        // Tạo mã xác nhận 6 chữ số
        $code = rand(100000, 999999);

        // Lưu mã vào cache trong 5 phút
        // Cache::put('key', $value, $minutes);
        Cache::put('reset_code_' . $user->email, $code, now()->addMinutes(5));

        // Gửi email chứa mã OTP
        Mail::to($user->email)->send(new SendOtpCodeMail($code));

        return response()->json(['message' => 'Mã xác nhận đã được gửi đến email.']);
    }

    // Xác minh mã OTP
    public function verifyResetCode(Request $request)
    {
        // 1. Định nghĩa các quy tắc validation và thông báo lỗi tùy chỉnh
        $rules = [
            'email' => 'required|email',
            'code' => 'required|digits:6', // Đảm bảo có quy tắc digits:6
        ];

        $messages = [
            'email.required' => 'Trường email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'code.required' => 'Trường mã xác nhận là bắt buộc.',
            'code.digits' => 'Mã xác nhận phải gồm 6 chữ số.', // <-- Thông báo bạn muốn!
        ];

        // 2. Chạy validation
        $validator = Validator::make($request->all(), $rules, $messages);

        // 3. Nếu validation thất bại, trả về lỗi
        if ($validator->fails()) {
            return response()->json([
                'message' => '',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng.'], 404);
        }

        // Lấy mã từ cache
        $cachedCode = Cache::get('reset_code_' . $user->email);

        if (!$cachedCode || $cachedCode != $request->code) {
            return response()->json(['message' => 'Mã xác nhận không hợp lệ hoặc đã hết hạn.'], 400); // 400 Bad Request
        }

        // Đánh dấu email đã xác minh OTP trong cache (có thời hạn 5 phút)
        Cache::put('otp_verified_' . $user->email, true, now()->addMinutes(5));

        // Xóa mã OTP sau khi xác minh thành công
        Cache::forget('reset_code_' . $user->email);

        return response()->json(['message' => 'Mã xác nhận hợp lệ. Bạn có thể đặt lại mật khẩu.'], 200);
    }

    // Đặt lại mật khẩu
    public function resetPassword(Request $request)
    {
        // Định nghĩa các quy tắc validation
        $rules = [
            'email' => 'required|email',
            'new_password' => 'required|string|min:8|confirmed',
        ];

        // Định nghĩa các thông báo lỗi tùy chỉnh bằng tiếng Việt
        $messages = [
            'email.required' => 'Trường email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'new_password.required' => 'Trường mật khẩu mới là bắt buộc.',
            'new_password.string' => 'Mật khẩu mới phải là chuỗi ký tự.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.', // <-- Đây là thông báo bạn cần!
            'new_password.confirmed' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.',
        ];

        // Chạy validation với thông báo lỗi tùy chỉnh
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => '',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng.'], 404);
        }

        // Kiểm tra cờ xác minh OTP từ Cache
        if (!Cache::get('otp_verified_' . $user->email)) {
            return response()->json(['message' => 'Bạn chưa xác thực mã OTP hoặc phiên đã hết hạn. Vui lòng bắt đầu lại quy trình quên mật khẩu.'], 403);
        }

        // KIỂM TRA MẬT KHẨU MỚI CÓ TRÙNG VỚI MẬT KHẨU CŨ KHÔNG
        if (Hash::check($request->new_password, $user->mat_khau)) {
            return response()->json(['message' => 'Mật khẩu mới không được trùng với mật khẩu cũ.'], 422);
        }

        // Hash mật khẩu mới trước khi lưu vào database
        $user->mat_khau = Hash::make($request->new_password);
        $user->save();

        // Xóa cờ xác minh OTP sau khi đặt lại mật khẩu thành công
        Cache::forget('otp_verified_' . $user->email);

        return response()->json(['message' => 'Mật khẩu đã được đặt lại thành công.'], 200);
    }
    // Gửi lại email kích hoạt nếu tài khoản chưa được kích hoạt
    public function resendActivationEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại.'], 404);
        }

        if ($user->is_active) {
            return response()->json(['message' => 'Tài khoản đã được kích hoạt.'], 400);
        }

        // Nếu chưa có token thì tạo mới
        if (!$user->activation_token) {
            $user->activation_token = Str::random(60);
            $user->save();
        }

        $activationLink = config('app.frontend_url') . '/kich-hoat?token=' . $user->activation_token;
        Mail::to($user->email)->send(new KichHoatTaiKhoan($user, $activationLink));

        return response()->json(['message' => 'Email kích hoạt đã được gửi lại.'], 200);
    }
}
