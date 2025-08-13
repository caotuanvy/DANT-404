<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Đảm bảo đây là model User của bạn, trỏ tới bảng nguoi_dung
use App\Models\DiaChi; // Đảm bảo dòng này được thêm vào và đúng namespace App\Models
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException; // Thêm dòng này để xử lý lỗi validate tốt hơn
use Illuminate\Support\Facades\Log; // <<-- Đảm bảo đã import Log Facade
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Lấy tất cả người dùng
    public function index()
    {
        try {
            $users = User::with('diaChis')->get();

            $formattedUsers = $users->map(function($user) {
                // Đảm bảo quan hệ 'diaChis' có dữ liệu trước khi implode
                $addresses = $user->diaChis->pluck('dia_chi')->implode('; ');

                return [
                    'nguoi_dung_id' => $user->nguoi_dung_id,
                    'ho_ten' => $user->ho_ten,
                    'email' => $user->email,
                    'sdt' => $user->sdt,
                    'vai_tro_id' => $user->vai_tro_id,
                    'trang_thai' => $user->trang_thai,
                    'dia_chi' => $addresses,
                ];
            });

            return response()->json($formattedUsers);

        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách người dùng:', ['error' => $e->getMessage()]); // Thêm log
            return response()->json(['message' => 'Lỗi khi lấy danh sách người dùng.', 'error' => $e->getMessage()], 500);
        }
    }

    // Xóa người dùng theo ID
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            Log::info('Người dùng đã bị xóa. ID: ' . $id); // Thêm log
            return response()->json(['message' => 'Tài khoản đã bị xóa'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { // Bắt lỗi cụ thể hơn
            Log::warning('Không tìm thấy người dùng để xóa. ID: ' . $id); // Thêm log
            return response()->json(['message' => 'Không tìm thấy người dùng.'], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa người dùng ID: ' . $id, ['error' => $e->getMessage()]); // Thêm log
            return response()->json(['message' => 'Có lỗi khi xóa người dùng.', 'error' => $e->getMessage()], 500);
        }
    }

    // Cập nhật thông tin người dùng
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $rules = [
                'ho_ten' => 'nullable|string|max:255',
                'sdt' => [
                    'nullable',
                    'string',
                    'max:15',
                    'regex:/^0[0-9]{9,10}$/',
                    'unique:nguoi_dung,sdt,' . $id . ',nguoi_dung_id' // Kiểm tra trùng SĐT trừ chính nó
                ],
                'vai_tro_id' => 'nullable|integer',
                'trang_thai' => 'nullable|integer|in:0,1',
            ];

            $validatedData = $request->validate($rules);

            // Cập nhật các trường nếu tồn tại
            foreach (['ho_ten', 'sdt', 'vai_tro_id', 'trang_thai'] as $field) {
                if (array_key_exists($field, $validatedData)) {
                    $user->$field = $validatedData[$field];
                }
            }

            $user->save();
            Log::info('Thông tin người dùng đã được cập nhật. ID: ' . $user->nguoi_dung_id); // Thêm log

            return response()->json([
                'message' => 'Cập nhật thông tin người dùng thành công',
                'user' => $user
            ], 200);

        } catch (ValidationException $e) {
            Log::error('Lỗi Validation khi cập nhật người dùng:', ['errors' => $e->errors()]); // Thêm log
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Không tìm thấy người dùng để cập nhật. ID: ' . $id); // Thêm log
            return response()->json(['message' => 'Người dùng không tìm thấy.'], 404);
        } catch (\Exception $e) {
            Log::critical('Lỗi nghiêm trọng khi cập nhật người dùng ID: ' . $id, ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]); // Thêm log chi tiết
            return response()->json([
                'message' => 'Lỗi khi cập nhật người dùng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Đổi mật khẩu người dùng
    public function changePassword(Request $request, $id)
    {
        Log::info('--- CHANGE PASSWORD CONTROLLER REACHED ---'); // Dòng log quan trọng
        Log::info('Request Payload:', $request->all());

        try {
            // 1. Tìm người dùng
            $user = User::findOrFail($id);
            Log::info('User found for password change. User ID: ' . $user->nguoi_dung_id . ', Email: ' . $user->email);

            // 2. Validate dữ liệu đầu vào
            $request->validate([
                'old_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed', // 'confirmed' yêu cầu new_password_confirmation
            ], [
                'old_password.required' => 'Mật khẩu cũ không được để trống.',
                'new_password.required' => 'Mật khẩu mới không được để trống.',
                'new_password.min' => 'Mật khẩu mới phải có ít nhất :min ký tự.',
                'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            ]);
            Log::info('Validation for passwords passed.');

            // 3. Kiểm tra mật khẩu cũ
            Log::info('Old password from request: ' . $request->old_password);
            Log::info('Hashed password from DB (mat_khau): ' . $user->mat_khau); // <<-- Sử dụng mat_khau

            if (!Hash::check($request->old_password, $user->mat_khau)) { // <<-- SỬA THÀNH $user->mat_khau
                Log::warning('Password mismatch for user ID: ' . $user->nguoi_dung_id . '. Provided: ' . $request->old_password . ' | DB Hash: ' . $user->mat_khau);
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu cũ không đúng.'
                ], 401); // 401 Unauthorized
            }
            Log::info('Old password matched!');

            // 4. Kiểm tra mật khẩu mới có trùng mật khẩu cũ không (tùy chọn)
            if (Hash::check($request->new_password, $user->mat_khau)) { // <<-- SỬA THÀNH $user->mat_khau
                Log::warning('New password is the same as old password for user ID: ' . $user->nguoi_dung_id);
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu mới không được trùng với mật khẩu cũ.'
                ], 422); // 422 Unprocessable Entity
            }
            Log::info('New password is not the same as old password.');

            // 5. Cập nhật mật khẩu mới (Laravel sẽ tự động hash vì bạn có `casts: 'hashed'` trong Model)
            $user->mat_khau = $request->new_password; // <<-- SỬA THÀNH $user->mat_khau
            $user->save();
            Log::info('Password changed successfully for user ID: ' . $user->nguoi_dung_id);

            return response()->json([
                'success' => true,
                'message' => 'Mật khẩu đã được thay đổi thành công.'
            ], 200);

        } catch (ValidationException $e) {
            Log::error('Validation error in changePassword:', ['errors' => $e->errors(), 'user_id' => $id]);
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('User not found for password change, ID: ' . $id);
            return response()->json(['success' => false, 'message' => 'Người dùng không tìm thấy.'], 404);
        } catch (\Exception $e) {
            Log::critical('Unhandled exception in changePassword for user ID: ' . $id, ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi đổi mật khẩu.', 'error' => $e->getMessage()], 500);
        }
    }
    public function updateAvatar(Request $request, $id)
    {
        // Xác thực yêu cầu
        $request->validate([
            'anh_dai_dien' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('anh_dai_dien')) {
            // Xóa ảnh cũ nếu có
            if ($user->anh_dai_dien) {
                $oldAvatarPath = str_replace(url('/storage'), 'public', $user->anh_dai_dien);
                Storage::delete($oldAvatarPath);
            }

            // Lưu ảnh mới
            $path = $request->file('anh_dai_dien')->store('avatars', 'public');
            $user->anh_dai_dien = url('/storage/' . $path);
            $user->save();

            return response()->json([
                'message' => 'Cập nhật ảnh đại diện thành công!',
                'anh_dai_dien' => $user->anh_dai_dien
            ], 200);
        }

        return response()->json(['message' => 'Không có file ảnh được gửi.'], 400);
    }
}