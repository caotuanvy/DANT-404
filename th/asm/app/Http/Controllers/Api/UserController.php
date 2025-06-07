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
}
