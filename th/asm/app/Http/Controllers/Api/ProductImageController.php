<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HinhAnhSanPham;
use App\Models\SanPham;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductImageController extends Controller
{
    // Upload ảnh cho 1 sản phẩm
    public function upload(Request $request, $sanPhamId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'mo_ta' => 'nullable|string',
        ]);

        // Kiểm tra sản phẩm tồn tại
        $product = SanPham::find($sanPhamId);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        DB::beginTransaction();
        try {
            $file = $request->file('image');
            $path = $file->store('products', 'public');

            $image = HinhAnhSanPham::create([
                'san_pham_id' => $sanPhamId,
                'duongdan' => $path,
                'mo_ta' => $request->mo_ta ?? '',
                'ngay_tao' => now()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Upload ảnh thành công',
                'data' => $image
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Upload ảnh thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Xóa ảnh sản phẩm theo id ảnh
    public function destroy($imageId)
    {
        $image = HinhAnhSanPham::find($imageId);
        if (!$image) {
            return response()->json(['message' => 'Ảnh không tồn tại'], 404);
        }

        DB::beginTransaction();
        try {
            // Xóa file ảnh trên storage
            if (Storage::disk('public')->exists($image->duongdan)) {
                Storage::disk('public')->delete($image->duongdan);
            }

            // Xóa bản ghi database
            $image->delete();

            DB::commit();

            return response()->json(['message' => 'Xóa ảnh thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Xóa ảnh thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Có thể thêm hàm lấy danh sách ảnh của 1 sản phẩm
    public function getImagesByProduct($sanPhamId)
    {
        $images = HinhAnhSanPham::where('san_pham_id', $sanPhamId)->get();

        return response()->json([
            'data' => $images
        ]);
    }
}
