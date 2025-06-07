<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Lấy tất cả người dùng
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Xóa người dùng theo ID
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Tài khoản đã bị xóa']);
    }
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'vai_tro_id' => 'required|integer|exists:vai_tro,vai_tro_id',
            'trang_thai' => 'required|integer',
        ]);

        $user->vai_tro_id = $request->vai_tro_id;
        $user->trang_thai = $request->trang_thai;
        $user->save();

        return response()->json(['message' => 'Cập nhật vai trò và trạng thái thành công', 'user' => $user]);
    }
}
