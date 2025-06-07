<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\SanPham;
use Illuminate\Http\Request;
use App\Models\SanPhamBienThe;
use App\Models\HinhAnhSanPham;

class ProductController extends Controller
{
    // Lấy danh sách sản phẩm kèm biến thể
 public function index()
{
    try {
        $products = SanPham::with('danhMuc')->get();

        $result = $products->map(function($item) {
            return [
                'product_id' => $item->san_pham_id,
                'product_name' => $item->ten_san_pham,
                'price' => $item->gia ?? 0,
                'description' => $item->mo_ta,
                'noi_bat' => $item->noi_bat,
                'khuyen_mai' => $item->khuyen_mai,
                'danh_muc' => [
                    'category_id' => $item->danhMuc?->category_id,
                    'ten_danh_muc' => $item->danhMuc?->ten_danh_muc,
                ]
            ];
        });

        return response()->json($result);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function toggleNoiBat($id, Request $request)
{
    $product = SanPham::findOrFail($id);
    $product->noi_bat = $request->input('noi_bat');
    $product->save();

    return response()->json(['message' => 'Cập nhật nổi bật thành công']);
}


    // // Tạo sản phẩm mới
   public function store(Request $request)
{
   $validated = $request->validate([
    'ten_san_pham' => 'required|string|max:255',
    'ten_danh_muc_id' => 'nullable|integer|exists:danh_muc_san_pham,category_id',
    'mo_ta' => 'nullable|string',
    'images' => 'nullable',
    'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
]);


    // Tạo sản phẩm
    $product = SanPham::create([
        'ten_san_pham' => $validated['ten_san_pham'],
        'ten_danh_muc_id' => $validated['ten_danh_muc_id'] ?? null,
        'mo_ta' => $validated['mo_ta'] ?? '',
    ]);

    // Nếu có file ảnh, lưu ảnh và tạo bản ghi hình ảnh sản phẩm
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
// Xem chi tiết sản phẩm
public function show($id)
{
    $product = SanPham::with(['hinhAnhSanPham', 'danhMuc'])->find($id);
    if (!$product) {
        return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
    }

    return response()->json([
        'product_id' => $product->san_pham_id,
        'product_name' => $product->ten_san_pham,
        'price' => $product->gia ?? 0,
        'description' => $product->mo_ta,
        'category_id' => $product->ten_danh_muc_id,
        'images' => $product->hinhAnhSanPham->map(function ($img) {
            return [
                'image_id' => $img->id,
                'image_url' => '/storage/' . $img->duongdan,
            ];
        }),
    ]);
}


    // // Xem chi tiết sản phẩm (có biến thể)
    // public function show($id)
    // {
    //     $product = SanPham::with(['category', 'variants'])->find($id);
    //     if (!$product) {
    //         return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
    //     }
    //     return response()->json($product);
    // }

    // // Cập nhật sản phẩm
    public function update(Request $request, $id)
{
    $product = SanPham::find($id);
    if (!$product) return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);

    $validated = $request->validate([
        'ten_san_pham' => 'sometimes|required|string|max:255',
        'gia' => 'nullable|numeric|min:0',
        'ten_danh_muc_id' => 'nullable|integer|exists:danh_muc_san_pham,category_id',
        'mo_ta' => 'nullable|string',
        'noi_bat' => 'nullable|integer',
        'khuyen_mai' => 'nullable|integer',
    ]);

    $product->update($validated);

    return response()->json([
        'message' => 'Cập nhật sản phẩm thành công',
        'data' => $product
    ]);
}


    // // Xóa sản phẩm (cascades xóa biến thể)
    public function destroy($id)
{
    $product = SanPham::find($id);
    if (!$product) return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);

    // Nếu bạn muốn cascade xóa biến thể:
    $product->variants()->delete();

    $product->delete();

    return response()->json(['message' => 'Xóa sản phẩm thành công']);
}



    // // ======== Quản lý biến thể sản phẩm ========

    // // Lấy danh sách biến thể của sản phẩm
    // public function variants($productId)
    // {
    //     $product = SanPham::find($productId);
    //     if (!$product) return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);

    //     return response()->json($product->variants);
    // }

    // // Thêm biến thể mới cho sản phẩm
    // public function addVariant(Request $request, $productId)
    // {
    //     $product = SanPham::find($productId);
    //     if (!$product) return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);

    //     $validated = $request->validate([
    //         'kich_thuoc' => 'nullable|string|max:100',
    //         'mau_sac' => 'nullable|string|max:100',
    //         'so_luong_ton_kho' => 'required|integer|min:0',
    //         'gia' => 'required|numeric|min:0',
    //     ]);

    //     $variant = new SanPhamBienThe($validated);
    //     $variant->san_pham_id = $productId;
    //     $variant->save();

    //     return response()->json([
    //         'message' => 'Thêm biến thể thành công',
    //         'data' => $variant
    //     ], 201);
    // }

    // // Cập nhật biến thể
    // public function updateVariant(Request $request, $productId, $variantId)
    // {
    //     $variant = SanPhamBienThe::where('san_pham_id', $productId)->find($variantId);
    //     if (!$variant) return response()->json(['message' => 'Không tìm thấy biến thể'], 404);

    //     $validated = $request->validate([
    //         'kich_thuoc' => 'nullable|string|max:100',
    //         'mau_sac' => 'nullable|string|max:100',
    //         'so_luong_ton_kho' => 'nullable|integer|min:0',
    //         'gia' => 'nullable|numeric|min:0',
    //     ]);

    //     $variant->update($validated);

    //     return response()->json([
    //         'message' => 'Cập nhật biến thể thành công',
    //         'data' => $variant
    //     ]);
    // }

    // // Xóa biến thể
    // public function deleteVariant($productId, $variantId)
    // {
    //     $variant = SanPhamBienThe::where('san_pham_id', $productId)->find($variantId);
    //     if (!$variant) return response()->json(['message' => 'Không tìm thấy biến thể'], 404);

    //     $variant->delete();
    //     return response()->json(['message' => 'Xóa biến thể thành công']);
    // }
    public function variants($productId)
{
    $product = SanPham::find($productId);
    if (!$product) return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);

    return response()->json($product->variants);
}

public function addVariant(Request $request, $productId)
{
    $product = SanPham::find($productId);
    if (!$product) return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);

    $validated = $request->validate([
        'kich_thuoc' => 'nullable|string|max:100',
        'mau_sac' => 'nullable|string|max:100',
        'so_luong_ton_kho' => 'required|integer|min:0',
        'gia' => 'required|numeric|min:0',
    ]);

    $variant = new SanPhamBienThe($validated);
    $variant->san_pham_id = $productId;
    $variant->save();

    return response()->json([
        'message' => 'Thêm biến thể thành công',
        'data' => $variant
    ], 201);
}

public function updateVariant(Request $request, $productId, $variantId)
{
    $variant = SanPhamBienThe::where('san_pham_id', $productId)->find($variantId);
    if (!$variant) return response()->json(['message' => 'Không tìm thấy biến thể'], 404);

    $validated = $request->validate([
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

public function deleteVariant($productId, $variantId)
{
    $variant = SanPhamBienThe::where('san_pham_id', $productId)->find($variantId);
    if (!$variant) return response()->json(['message' => 'Không tìm thấy biến thể'], 404);

    $variant->delete();
    return response()->json(['message' => 'Xóa biến thể thành công']);
}
}
