<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\HinhAnhSanPham;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = SanPham::with(['danhMuc', 'hinhAnhSanPham'])->withCount('bienThe')->get();

            $result = $products->map(function ($item) {
                return [
                    'product_id' => $item->san_pham_id,
                    'product_name' => $item->ten_san_pham,
                    'price' => $item->gia ?? 0,
                    'description' => $item->mo_ta,
                    'noi_bat' => $item->noi_bat,
                    'khuyen_mai' => $item->khuyen_mai,
                    'so_bien_the' => $item->bien_the_count,
                    'slug' => $item->slug,
                    'danh_muc' => [
                        'category_id' => $item->danhMuc?->category_id,
                        'ten_danh_muc' => $item->danhMuc?->ten_danh_muc,
                    ],
                    'images' => $item->hinhAnhSanPham->map(fn($image) => asset('storage/' . $image->duongdan)),
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $product = SanPham::with(['danhMuc', 'hinhAnhSanPham'])->findOrFail($id);

        return response()->json([
            'product_id' => $product->san_pham_id,
            'product_name' => $product->ten_san_pham,
            'price' => $product->gia,
            'description' => $product->mo_ta,
            'noi_bat' => $product->noi_bat,
            'khuyen_mai' => $product->khuyen_mai,
            'slug' => $product->slug,
            'so_bien_the' => $product->bienThe->count(),
            'danh_muc' => $product->danhMuc ? [
                'category_id' => $product->danhMuc->category_id,
                'ten_danh_muc' => $product->danhMuc->ten_danh_muc,
            ] : null,
            'images' => $product->hinhAnhSanPham->map(function ($img) {
                return [
                    'id' => $img->hinh_anh_id,
                    'image_path' => $img->duongdan,
                    'url' => asset('storage/' . $img->duongdan),
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'ten_danh_muc_id' => 'nullable|integer|exists:danh_muc_san_pham,category_id',
            'mo_ta' => 'nullable|string',
            'images' => 'nullable',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $product = SanPham::create([
            'ten_san_pham' => $validated['ten_san_pham'],
            'ten_danh_muc_id' => $validated['ten_danh_muc_id'] ?? null,
            'mo_ta' => $validated['mo_ta'] ?? '',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                HinhAnhSanPham::create([
                    'san_pham_id' => $product->san_pham_id,
                    'duongdan' => $path,
                    'ngay_tao' => now(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Thêm sản phẩm thành công',
            'data' => $product,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = SanPham::find($id);
        if (!$product) return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);

        $validated = $request->validate([
            'ten_san_pham' => 'sometimes|required|string|max:255',
            'mo_ta' => 'nullable|string',
            'noi_bat' => 'nullable|boolean',
            'khuyen_mai' => 'nullable|numeric|min:0',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('san_pham', 'slug')->ignore($id, 'san_pham_id'),
            ],
            'ten_danh_muc_id' => 'nullable|integer|exists:danh_muc_san_pham,category_id',
        ]);

        $validated['ngay_sua'] = now();
        $product->update($validated);

        return response()->json([
            'message' => 'Cập nhật sản phẩm thành công',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $sanPham = SanPham::find($id);
        if (!$sanPham) return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);

        $sanPham->hinhAnhSanPham()->delete();
        $sanPham->delete();

        return response()->json(['message' => 'Xóa sản phẩm thành công']);
    }

    public function toggleNoiBat($id, Request $request)
    {
        $product = SanPham::findOrFail($id);
        $product->noi_bat = $request->input('noi_bat');
        $product->save();

        return response()->json(['message' => 'Cập nhật nổi bật thành công']);
    }
}
