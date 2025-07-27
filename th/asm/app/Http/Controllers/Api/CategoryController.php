<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product; // <-- Đảm bảo đã import Product model
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\DanhMucCha;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('danhMucCha')->get();

        $categories = $categories->map(function($cat) {
            return [
                'category_id' => $cat->category_id,
                'ten_danh_muc' => $cat->ten_danh_muc,
                'mo_ta' => $cat->mo_ta,
                'slug' => $cat->slug,
                'danh_muc_cha_id' => $cat->danh_muc_cha_id,
                'danh_muc_cha_name' => $cat->danhMucCha ? $cat->danhMucCha->ten_danh_muc : null,
                'image_url' => $cat->slug
                    ? url("/uploads/categories/{$cat->slug}")
                    : url("/uploads/categories/placeholder.jpg"),
                'trang_thai' => $cat->trang_thai,
                'ngay_tao' => $cat->ngay_tao,
                'ngay_sua' => $cat->ngay_sua,
            ];
        });

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'slug' => 'nullable|string|max:100',
            'ngay_tao' => 'nullable|date',
            'ngay_sua' => 'nullable|date',
            'danh_muc_cha_id' => 'nullable|integer|exists:danh_muc_cha,id',
            'trang_thai' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $filename);
            $data['slug'] = $filename;
        }

        $data['ngay_tao'] = $data['ngay_tao'] ?? now();
        $data['ngay_sua'] = $data['ngay_sua'] ?? now();
        $data['trang_thai'] = $data['trang_thai'] ?? 1;

        $category = Category::create($data);

        return response()->json([
            'message' => 'Thêm danh mục thành công',
            'data' => $category
        ], 201);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Xóa danh mục thành công'
        ]);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function update($id, Request $request)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'slug' => 'nullable|string|max:100',
            'ngay_sua' => 'nullable|date',
            'danh_muc_cha_id' => 'nullable|integer|exists:danh_muc_cha,id',
            'trang_thai' => 'boolean',
        ]);

        $category->ten_danh_muc = $validated['ten_danh_muc'];
        $category->mo_ta = $validated['mo_ta'] ?? null;
        $category->slug = $validated['slug'] ?? $category->slug;
        $category->ngay_sua = now();
        $category->danh_muc_cha_id = $validated['danh_muc_cha_id'] ?? null;
        $category->trang_thai = $validated['trang_thai'] ?? $category->trang_thai;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $filename);
            $category->slug = $filename;
        }

        $category->save();

        return response()->json([
            'message' => 'Cập nhật danh mục thành công',
            'data' => $category
        ]);
    }

    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();
        return response()->json(['message' => 'Khôi phục thành công!']);
    }

    public function toggleStatus($id)
    {
        try {
            $category = Category::findOrFail($id);

            $category->trang_thai = $category->trang_thai == 1 ? 0 : 1;
            $category->ngay_sua = now();
            $category->save();

            return response()->json([
                'message' => 'Cập nhật trạng thái danh mục thành công!',
                'trang_thai_moi' => $category->trang_thai
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy danh mục.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi cập nhật trạng thái.', 'error' => $e->getMessage()], 500);
        }
    }

    public function setStatus($id, Request $request)
    {
        if ($request->method() !== 'PUT') {
            return response()->json([
                'message' => 'Phải gọi bằng phương thức PUT!',
                'method' => $request->method()
            ], 405);
        }

        if (!in_array($request->input('trang_thai'), [0, 1], true)) {
            return response()->json(['message' => 'Giá trị trạng thái không hợp lệ!'], 400);
        }
        $category = \App\Models\Category::findOrFail($id);
        $category->trang_thai = $request->input('trang_thai');
        $category->save();

        return response()->json(['message' => 'Cập nhật trạng thái thành công!']);
    }

    public function updateStatus($id, Request $request)
    {
        $category = Category::findOrFail($id);
        $category->trang_thai = $request->input('trang_thai');
        $category->save();

        return response()->json(['message' => 'Cập nhật trạng thái thành công!']);
    }

    // PHƯƠNG THỨC NÀY ĐỂ LẤY SẢN PHẨM TRONG DANH MỤC
    public function getProductsByCategory($categoryId)
    {
        try {
            $category = Category::with(['products' => function($query) {
                // Tùy chỉnh việc tải sản phẩm nếu cần, ví dụ: chỉ lấy sản phẩm đang hoạt động
                // Hoặc bạn có thể thêm các điều kiện lọc khác
                $query->where('trang_thai', 1);
            }])->findOrFail($categoryId);

            $products = $category->products->map(function($product) {
                // Đảm bảo các tên trường ở đây khớp với frontend của bạn (ten_san_pham, gia_san_pham, mo_ta, image_url, trang_thai)
                return [
                    'product_id' => $product->product_id,
                    'ten_san_pham' => $product->ten_san_pham, // <-- Đảm bảo tên trường này khớp
                    'mo_ta' => $product->mo_ta,
                    'gia_san_pham' => $product->gia_san_pham, // <-- Đảm bảo tên trường này khớp
                    'so_luong_ton' => $product->so_luong_ton,
                    'slug' => $product->slug,
                    // Giả sử cột ảnh chính trong bảng `san_pham` là `main_image_url`
                    // hoặc bạn có thể dùng `slug` nếu bạn lưu tên file ảnh vào cột `slug` của sản phẩm
                    'image_url' => $product->slug ? url("/uploads/products/{$product->slug}") : url("/uploads/products/placeholder.jpg"),
                    'trang_thai' => $product->trang_thai,
                    // Thêm các trường khác của sản phẩm mà bạn muốn hiển thị
                ];
            });

            return response()->json([
                'category' => [
                    'category_id' => $category->category_id,
                    'ten_danh_muc' => $category->ten_danh_muc, // <-- Đảm bảo tên trường này khớp
                    // Thêm các thông tin danh mục khác nếu cần
                ],
                'products' => $products
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy danh mục.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi lấy sản phẩm theo danh mục.', 'error' => $e->getMessage()], 500);
        }
    }
}