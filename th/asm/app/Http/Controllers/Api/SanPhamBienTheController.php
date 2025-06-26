<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\SanPhamBienThe;
use Illuminate\Http\Request;

class SanPhamBienTheController extends Controller
{
    public function index($productId)
    {
        $variants = SanPhamBienThe::where('san_pham_id', $productId)->get();
        return response()->json($variants);
    }

    public function store(Request $request, $productId)
    {
        $product = SanPham::find($productId);
        if (!$product) return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);

        $validated = $request->validate([
            'ten_bien_the' => 'required|string|max:255',
            'kich_thuoc' => 'nullable|string|max:100',
            'mau_sac' => 'nullable|string|max:100',
            'so_luong_ton_kho' => 'required|integer|min:0',
            'gia' => 'required|numeric|min:0',
        ]);

        $variant = SanPhamBienThe::create([
            'san_pham_id' => $productId,
            'ten_bien_the' => $validated['ten_bien_the'],
            'kich_thuoc' => $validated['kich_thuoc'],
            'mau_sac' => $validated['mau_sac'],
            'so_luong_ton_kho' => $validated['so_luong_ton_kho'],
            'gia' => $validated['gia'],
            'ngay_tao' => now(),
        ]);

        return response()->json([
            'message' => 'Thêm biến thể thành công',
            'data' => $variant
        ], 201);
    }

    public function update(Request $request, $productId, $variantId)
    {
        $variant = SanPhamBienThe::where('san_pham_id', $productId)->find($variantId);
        if (!$variant) return response()->json(['message' => 'Không tìm thấy biến thể'], 404);

        $validated = $request->validate([
            'ten_bien_the' => 'nullable|string|max:255',
            'kich_thuoc' => 'nullable|string|max:100',
            'mau_sac' => 'nullable|string|max:100',
            'so_luong_ton_kho' => 'nullable|integer|min:0',
            'gia' => 'nullable|numeric|min:0',
        ]);

        $variant->update($validated);

        return response()->json([
            'message' => 'Cập nhật biến thể thành công',
            'data' => $variant
        ]);
    }


    public function destroy($id)
{
    $variant = SanPhamBienThe::find($id);
    if (!$variant) return response()->json(['message' => 'Không tìm thấy biến thể'], 404);

    $variant->delete();
    return response()->json(['message' => 'Xóa biến thể thành công']);
}

}
