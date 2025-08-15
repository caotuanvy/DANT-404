<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\Category;
use App\Models\DanhMucCha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ParentCategoryProductController extends Controller
{
    /**
     * Lấy sản phẩm của một danh mục cha, có hỗ trợ lọc, sắp xếp và phân trang.
     *
     * @param int $parentCategoryId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsByParentCategory($parentCategoryId, Request $request)
    {
        try {
            $parentCategory = DanhMucCha::find($parentCategoryId);

            if (!$parentCategory) {
                return response()->json(['message' => 'Danh mục cha không tồn tại.'], 404);
            }

            $childCategoriesFromParent = Category::where('danh_muc_cha_id', $parentCategoryId)->get();
            $childCategoryIdsFromParent = $childCategoriesFromParent->pluck('category_id')->toArray();

            if (empty($childCategoryIdsFromParent)) {
                return response()->json([
                    'category' => $parentCategory,
                    'products' => [],
                    'danh_muc_con' => $childCategoriesFromParent,
                    'pagination' => [
                        'total' => 0,
                        'per_page' => 15,
                        'current_page' => 1,
                        'last_page' => 1,
                        'from' => 0,
                        'to' => 0,
                    ],
                ]);
            }
            
            $selectedChildCategory = $request->input('child_categories'); 
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $keyword = $request->input('keyword');
            $sortBy = $request->input('sort_by'); 
            $perPage = $request->input('per_page', 30);
            $page = $request->input('page', 1);

            $query = SanPham::with(['danhMuc', 'hinhAnhSanPham']);
            
            // Lọc theo danh mục con
            if ($selectedChildCategory) {
                $query->where('ten_danh_muc_id', $selectedChildCategory);
            } else {
                $query->whereIn('ten_danh_muc_id', $childCategoryIdsFromParent);
            }

            // Áp dụng tìm kiếm theo từ khóa
            if ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('ten_san_pham', 'like', '%' . $keyword . '%')
                      ->orWhere('mo_ta', 'like', '%' . $keyword . '%')
                      ->orWhere('Tieu_de_seo', 'like', '%' . $keyword . '%');
                });
            }

            // Lọc theo khoảng giá
            if ($minPrice !== null) {
                $query->where('gia', '>=', (float) $minPrice);
            }
            if ($maxPrice !== null) {
                $query->where('gia', '<=', (float) $maxPrice);
            }
            
            // Áp dụng sắp xếp
            if ($sortBy === 'price_asc') {
                $query->orderBy('gia', 'asc');
            } elseif ($sortBy === 'price_desc') {
                $query->orderBy('gia', 'desc');
            } elseif ($sortBy === 'newest') {
                $query->latest(); 
            }

            $paginatedProducts = $query->paginate($perPage, ['*'], 'page', $page);

            // Cập nhật phần này để thêm thuộc tính khuyen_mai và mo_ta_ngan
            $resultProducts = $paginatedProducts->map(function ($item) {
                $imageUrl = '/placeholder.jpg';
                if ($item->hinhAnhSanPham && $item->hinhAnhSanPham->isNotEmpty()) {
                    $firstImage = $item->hinhAnhSanPham->first();
                    if ($firstImage && $firstImage->duongdan) {
                        $imageUrl = asset('storage/' . $firstImage->duongdan);
                    }
                }
                
                // Giả định bạn có thuộc tính `khuyen_mai` và `mo_ta_ngan` trong model SanPham
                // Ép kiểu về float và xử lý giá trị null/undefined để tránh lỗi
                $discountPercentage = (float)($item->khuyen_mai ?? 0);
                $originalPrice = (float)($item->gia ?? 0);
                $discountedPrice = $originalPrice;

                if ($discountPercentage > 0) {
                    $discountedPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));
                }

                return [
                    'id' => $item->san_pham_id,
                    'ten_san_pham' => $item->ten_san_pham,
                    'gia_goc' => $discountPercentage > 0 ? $originalPrice : null,
                    'gia_da_giam' => $discountedPrice,
                    'khuyen_mai' => $discountPercentage,
                    'slug' => $item->slug,
                    'image_url' => $imageUrl,
                    'mo_ta' => $item->Mo_ta_seo,
                    'danh_muc' => $item->danhMuc,
                ];
            });

            // Thêm danh mục con vào phản hồi
            $parentCategory->danh_muc_con = $childCategoriesFromParent;

            // Trả về dữ liệu phân trang
            return response()->json([
                'category' => $parentCategory,
                'products' => $resultProducts,
                'pagination' => [
                    'total' => $paginatedProducts->total(),
                    'per_page' => $paginatedProducts->perPage(),
                    'current_page' => $paginatedProducts->currentPage(),
                    'last_page' => $paginatedProducts->lastPage(),
                    'from' => $paginatedProducts->firstItem(),
                    'to' => $paginatedProducts->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Error in getProductsByParentCategory: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Không thể lấy sản phẩm. Vui lòng thử lại sau.'], 500);
        }
    }
}
