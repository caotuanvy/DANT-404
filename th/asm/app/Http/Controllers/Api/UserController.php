<?php

namespace App\Http\Controllers\Api; // <-- CHỈ GIỮ LẠI DÒNG NÀY

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DiaChi; // <-- Đảm bảo dòng này được thêm vào và đúng namespace App\Models
use Illuminate\Validation\ValidationException; // Thêm dòng này để xử lý lỗi validate tốt hơn

class UserController extends Controller
{
    // Lấy tất cả người dùng
    public function index()
    {
        try {
            $users = User::with('diaChis')->get();

            $formattedUsers = $users->map(function($user) {
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
            return response()->json(['message' => 'Lỗi khi lấy danh sách người dùng.', 'error' => $e->getMessage()], 500);
        }
    }

    // Xóa người dùng theo ID
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'Tài khoản đã bị xóa'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Không tìm thấy người dùng hoặc có lỗi khi xóa.', 'error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'vai_tro_id' => 'required|integer', // Bỏ 'exists:vai_tro,vai_tro_id' nếu bạn chưa có bảng vai_tro
                'trang_thai' => 'required|integer|in:0,1',
            ]);

            $user->vai_tro_id = $request->vai_tro_id;
            $user->trang_thai = $request->trang_thai;
            $user->save();

            return response()->json(['message' => 'Cập nhật vai trò và trạng thái thành công', 'user' => $user], 200);
        } catch (ValidationException $e) { // Thêm dòng này để bắt lỗi validation
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi cập nhật người dùng.', 'error' => $e->getMessage()], 500);
        }
    }
}