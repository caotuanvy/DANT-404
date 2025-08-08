<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category; // Model cho danh mục con (ví dụ: bảng 'categories')
use App\Models\DanhMucCha; // Model cho danh mục cha (ví dụ: bảng 'danh_muc_cha')
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Đảm bảo đã import Str nếu bạn dùng nó ở đây

class ChildCategoryController extends Controller
{
    /**
     * Lấy danh sách các danh mục con của một danh mục cha cụ thể
     * và thông tin breadcrumb của danh mục cha.
     *
     * @param  int  $categoryId ID của danh mục cha (từ bảng danh_muc_cha).
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubcategories($categoryId)
    {
        try {
            // Tìm danh mục cha bằng model DanhMucCha
            // Đảm bảo primary key của DanhMucCha là 'id' hoặc được cấu hình đúng trong model
            $parentCategory = DanhMucCha::find($categoryId);

            if (!$parentCategory) {
                return response()->json(['message' => 'Danh mục cha không tồn tại.'], 404);
            }

            // Lấy tất cả các danh mục con của danh mục cha này
            // (Category model có cột danh_muc_cha_id trỏ tới id của DanhMucCha)
            $subcategories = Category::where('danh_muc_cha_id', $categoryId)->get();

            // Breadcrumb sẽ chỉ bao gồm chính danh mục cha
            $breadcrumb = [
                [
                    'category_id' => $parentCategory->id, // Sử dụng $parentCategory->id
                    'ten_danh_muc' => $parentCategory->ten_danh_muc,
                    'slug' => $parentCategory->slug ?? null, // Nếu DanhMucCha có slug, nếu không thì null
                ]
            ];

            return response()->json([
                'parent_category' => [
                    'category_id' => $parentCategory->id, // Sử dụng $parentCategory->id
                    'ten_danh_muc' => $parentCategory->ten_danh_muc,
                    'parent_id' => null, // Giả định DanhMucCha là cấp cao nhất hoặc không có cột parent_id
                ],
                'subcategories' => $subcategories->map(function ($subcategory) {
                    // THAY ĐỔI TẠI ĐÂY: Thêm 'image_url' vào dữ liệu trả về cho frontend
                    // Giả định 'slug' của Category model chứa tên file ảnh
                    $imageUrl = $subcategory->slug
                        ? url("/uploads/categories/{$subcategory->slug}")
                        : url("/uploads/categories/placeholder.jpg"); // Ảnh mặc định nếu không có slug

                    return [
                        'category_id' => $subcategory->category_id, // Đảm bảo đây là primary key của Category
                        'ten_danh_muc' => $subcategory->ten_danh_muc,
                        'slug' => $subcategory->slug,
                        'image_url' => $imageUrl, // Đã thêm trường này
                        'parent_id' => $subcategory->danh_muc_cha_id, // ID của danh mục cha hiện tại
                        'noi_bat' => $subcategory->noi_bat,
                        'trang_thai' => $subcategory->trang_thai,
                        // Thêm các trường khác nếu cần từ model Category
                    ];
                }),
                'breadcrumb' => $breadcrumb,
            ]);

        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy danh mục con (ChildCategoryController::getSubcategories): " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json(['error' => 'Không thể lấy danh mục con. Vui lòng thử lại sau.'], 500);
        }
    }

    /**
     * Gỡ bỏ một danh mục con khỏi danh mục cha của nó (không xóa khỏi database).
     * Cập nhật danh_muc_cha_id của danh mục con thành NULL.
     *
     * @param  int  $subcategoryId ID của danh mục con cần gỡ bỏ.
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachSubcategory($subcategoryId)
    {
        try {
            // Tìm danh mục con bằng primary key
            // RẤT QUAN TRỌNG: Đảm bảo model Category có protected $primaryKey = 'category_id';
            // nếu cột khóa chính của bạn là 'category_id' chứ không phải 'id'.
            $subcategory = Category::find($subcategoryId);

            if (!$subcategory) {
                return response()->json(['message' => 'Danh mục con không tồn tại.'], 404);
            }

            // Kiểm tra xem danh mục con này có thực sự thuộc về một danh mục cha không
            if ($subcategory->danh_muc_cha_id === null) {
                return response()->json(['message' => 'Danh mục con này đã không thuộc về danh mục cha nào rồi.'], 400);
            }

            // Cập nhật danh_muc_cha_id thành NULL
            // RẤT QUAN TRỌNG: Đảm bảo cột 'danh_muc_cha_id' trong bảng 'danh_muc_san_pham'
            // được phép nhận giá trị NULL (có thể NULLable trong migration).
            $subcategory->danh_muc_cha_id = null;
            $subcategory->save();

            return response()->json(['message' => 'Danh mục con đã được gỡ bỏ khỏi danh mục cha thành công.']);

        } catch (\Exception $e) {
            Log::error("Lỗi khi gỡ bỏ danh mục con (ChildCategoryController::detachSubcategory): " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json(['error' => 'Không thể gỡ bỏ danh mục con. Vui lòng thử lại sau.'], 500);
        }
    }
}
