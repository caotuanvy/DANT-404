<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiamGia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GiamGiaController extends Controller
{
    public function index()
    {
        return GiamGia::orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ma_giam_gia' => 'required|string|unique:giam_gia,ma_giam_gia|max:255',
            'ten_chuong_trinh' => 'required|string|max:255',
            'loai_giam_gia' => 'required|in:percentage,fixed_amount,free_ship',
            'gia_tri' => 'required|numeric|min:0',
            'so_luong' => 'required|integer|min:1',
            'ngay_bat_dau' => 'required|date',
            'ngay_ket_thuc' => 'required|date|after_or_equal:ngay_bat_dau',
            'gia_tri_don_hang_toi_thieu' => 'nullable|numeric|min:0',
            'gia_tri_giam_toi_da' => 'nullable|numeric|min:0',
            'trang_thai' => 'required|boolean',
            'ap_dung_cho' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $giamGia = GiamGia::create($request->all());
        return response()->json($giamGia, 201);
    }

    public function show(GiamGia $giamGia)
    {
        return response()->json($giamGia);
    }

    public function update(Request $request, GiamGia $giamGia)
    {
        $validator = Validator::make($request->all(), [
            'ma_giam_gia' => ['required', 'string', 'max:255', Rule::unique('giam_gia')->ignore($giamGia->giam_gia_id, 'giam_gia_id')],
            'ten_chuong_trinh' => 'required|string|max:255',
            'loai_giam_gia' => 'required|in:percentage,fixed_amount,free_ship',
            'gia_tri' => 'required|numeric|min:0',
            'so_luong' => 'required|integer|min:1',
            'ngay_bat_dau' => 'required|date',
            'ngay_ket_thuc' => 'required|date|after_or_equal:ngay_bat_dau',
            'gia_tri_don_hang_toi_thieu' => 'nullable|numeric|min:0',
            'gia_tri_giam_toi_da' => 'nullable|numeric|min:0',
            'trang_thai' => 'required|boolean',
            'ap_dung_cho' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $giamGia->update($request->all());
        return response()->json($giamGia);
    }

    public function toggleStatus(GiamGia $giamGia)
    {
        $giamGia->trang_thai = !$giamGia->trang_thai;
        $giamGia->save();

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công!',
            'coupon' => $giamGia
        ]);
    }

    public function sendToUsers(Request $request, GiamGia $giamGia)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:nguoi_dung,nguoi_dung_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $giamGia->users()->syncWithoutDetaching($request->input('user_ids'));

        return response()->json(['message' => 'Voucher đã được gửi thành công.']);
    }

    public function sendToAllUsers(GiamGia $giamGia)
    {
        $userIds = User::where('vai_tro_id', 0)->pluck('nguoi_dung_id');

        if ($userIds->isEmpty()) {
            return response()->json(['message' => 'Không có người dùng nào trong hệ thống.'], 404);
        }

        $giamGia->users()->syncWithoutDetaching($userIds);

        return response()->json(['message' => 'Voucher đã được gửi thành công cho tất cả người dùng.']);
    }

    public function searchUsers(Request $request)
    {
        $validator = Validator::make($request->all(), ['term' => 'required|string|min:3']);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $searchTerm = $request->input('term');

        $users = User::where(function ($query) use ($searchTerm) {
                $query->where('ho_ten', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            })
            ->where('vai_tro_id', 0)
            ->select('nguoi_dung_id', 'ho_ten', 'email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
     // Đảm bảo phương thức này dùng cho route /api/giam-gia/homepage
    public function getForHomepage()
    {
        $activeDiscounts = GiamGia::where('trang_thai', 1)
                                   ->where('ngay_ket_thuc', '>=', now())
                                   ->orderBy('ngay_ket_thuc', 'asc')
                                   // ->select([...]) // Nếu muốn chỉ lấy các trường cần thiết, bỏ comment này
                                   ->get();

        // Nếu muốn kiểm tra dữ liệu trả về, có thể log hoặc dd()
        // \Log::info('Homepage discounts:', $activeDiscounts->toArray());
        // dd($activeDiscounts);

        return response()->json($activeDiscounts);
    }
    public function claim(Request $request, GiamGia $giamGia)
{
    $userId = $request->input('nguoi_dung_id');
    $user = \App\Models\User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
    }

    // Kiểm tra trạng thái và hạn sử dụng
    if (!$giamGia->trang_thai || $giamGia->ngay_ket_thuc < now()) {
        return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'], 400);
    }

    // Kiểm tra đã lưu chưa
    $isAlreadyClaimed = $user->vouchers()->where('giam_gia.giam_gia_id', $giamGia->giam_gia_id)->exists();
    if ($isAlreadyClaimed) {
        return response()->json(['message' => 'Bạn đã lưu mã giảm giá này rồi.'], 409);
    }

    // Lưu vào bảng trung gian
    $user->vouchers()->attach($giamGia->giam_gia_id);

    return response()->json([
        'message' => 'Lưu mã giảm giá thành công!',
        'coupon' => $giamGia
    ]);
}

public function myVouchers(Request $request)
{
    // Lấy thông tin người dùng đã được xác thực qua token
    $user = $request->user();

    // Middleware đã đảm bảo user luôn tồn tại, nhưng kiểm tra lại cũng không thừa
    if (!$user) {
        return response()->json(['message' => 'Unauthorized.'], 401);
    }

    $myVouchers = $user->vouchers()
        ->where('giam_gia.trang_thai', 1)
        ->where('giam_gia.ngay_ket_thuc', '>=', now())
        ->orderBy('giam_gia.ngay_ket_thuc', 'asc')
        ->get();

    return response()->json($myVouchers);
}
}
