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
    public function store(Request $request, $productId)
    {
        $request->validate([
            'images.*' => 'required|image|max:2048',
        ]);

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $hinhAnh = HinhAnhSanPham::create([
                    'san_pham_id' => $productId,
                    'duongdan' => $path,
                    'mo_ta' => null,
                    'ngay_tao' => now(),
                ]);
                $uploadedImages[] = asset('storage/' . $path);
            }
        }
        return response()->json([
            'message' => 'Upload ảnh thành công',
            'uploaded_images' => $uploadedImages,
        ]);
    }

    public function upload(Request $request, $sanPhamId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'mo_ta' => 'nullable|string',
        ]);

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

    public function destroy($id)
{
    $image = HinhAnhSanPham::findOrFail($id);
    Storage::delete('public/' . $image->duongdan);
    $image->delete();
    return response()->json(['message' => 'Xóa ảnh thành công']);
}

    public function getImagesByProduct($sanPhamId)
    {
        $images = HinhAnhSanPham::where('san_pham_id', $sanPhamId)->get();
        return response()->json([
            'data' => $images
        ]);
    }
}
