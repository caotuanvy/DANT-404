<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SanPhamBienThe;
use App\Models\SanPham;

class SanPhamBienTheController extends Controller
{
    public function index($id)
    {
        $variants = SanPhamBienThe::where('san_pham_id', $id)->get();
        return response()->json($variants);
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'kich_thuoc' => 'required|string|max:255',
            'mau_sac' => 'required|string|max:255',
            'so_luong_ton_kho' => 'required|integer',
            'gia' => 'required|numeric',
        ]);

        $variant = SanPhamBienThe::create([
            'san_pham_id' => $id,
            'kich_thuoc' => $validated['kich_thuoc'],
            'mau_sac' => $validated['mau_sac'],
            'so_luong_ton_kho' => $validated['so_luong_ton_kho'],
            'gia' => $validated['gia'],
            'ngay_tao' => now(),
        ]);

        return response()->json($variant, 201);
    }

    public function destroy($id)
    {
        $variant = SanPhamBienThe::findOrFail($id);
        $variant->delete();

        return response()->json(['message' => 'Xóa biến thể thành công']);
    }
}

