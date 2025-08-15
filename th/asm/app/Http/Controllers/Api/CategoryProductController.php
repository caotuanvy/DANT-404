<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\Category; // Có thể cần để kiểm tra sự tồn tại của danh mục
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Đảm bảo đã import Str nếu dùng cho image_url

class CategoryProductController extends Controller
{
    public function getProductsByCategory($categoryId)
    {
        // ... (phương thức hiện tại của bạn để lấy sản phẩm theo danh mục)
        try {
            $category = Category::find($categoryId);

            if (!$category) {
                return response()->json(['message' => 'Danh mục không tồn tại.'], 404);
            }

            $products = SanPham::with(['danhMuc', 'hinhAnhSanPham', 'bienThe'])
                            ->where('ten_danh_muc_id', $categoryId)
                            ->withCount('bienThe')
                            ->get();

            $resultProducts = $products->map(function ($item) {
                $minPrice = null;
                $maxPrice = null;

                if ($item->bienThe->isNotEmpty()) {
                    $variantPrices = $item->bienThe->pluck('gia')->map(function($price){
                        return (float)$price;
                    });
                    $minPrice = $variantPrices->min();
                    $maxPrice = $variantPrices->max();
                } else {
                    $minPrice = (float)$item->gia;
                    $maxPrice = (float)$item->gia;
                }
                $avgPrice = $item->bienThe->avg('gia');

                $imageUrl = null;
                if ($item->hinhAnhSanPham->isNotEmpty()) {
                    $firstImage = $item->hinhAnhSanPham->first();
                    if (Str::startsWith($firstImage->duongdan, ['http://', 'https://'])) {
                        $imageUrl = $firstImage->duongdan;
                    } else {
                        $imageUrl = asset('storage/' . $firstImage->duongdan);
                    }
                } else {
                    $imageUrl = '/placeholder.jpg';
                }

                return [
                    'product_id' => $item->san_pham_id,
                    'product_name' => $item->ten_san_pham,
                    'description' => $item->mo_ta,
                    'noi_bat' => $item->noi_bat,
                    'khuyen_mai' => $item->khuyen_mai,
                    'trang_thai' => $item->trang_thai,
                    'ngay_bat_dau_giam_gia' => $item->ngay_bat_dau_giam_gia,
                    'ngay_ket_thuc_giam_gia' => $item->ngay_ket_thuc_giam_gia,
                    'the' => $item->the,
                    'Tieu_de_seo' => $item->Tieu_de_seo,
                    'Tu_khoa' => $item->Tu_khoa,
                    'Mo_ta_seo' => $item->Mo_ta_seo,
                    'so_bien_the' => $item->bien_the_count,
                    'gia' => (float)$item->gia,
                    'gia_san_pham' => (float)$item->gia,
                    'gia_trung_binh' => $avgPrice,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'slug' => $item->slug,
                    'danh_muc' => $item->danhMuc ? [
                        'category_id' => $item->danhMuc->category_id,
                        'ten_danh_muc' => $item->danhMuc->ten_danh_muc,
                    ] : null,
                    'images' => $item->hinhAnhSanPham->map(function ($image) {
                        if (Str::startsWith($image->duongdan, ['http://', 'https://'])) {
                            return $image->duongdan;
                        }
                        return asset('storage/' . $image->duongdan);
                    }),
                    'image_url' => $imageUrl,
                ];
            });

            return response()->json([
                'category' => [
                    'category_id' => $category->category_id,
                    'ten_danh_muc' => $category->ten_danh_muc,
                ],
                'products' => $resultProducts,
            ]);

        } catch (\Exception $e) {
            Log::error("Error in getProductsByCategory: " . $e->getMessage() . " at " . $e->getFile() . " line " . $e->getLine());
            return response()->json(['error' => 'Không thể lấy sản phẩm trong danh mục. Vui lòng thử lại sau.'], 500);
        }
    }

    /**
     * Gỡ liên kết một sản phẩm khỏi một danh mục cụ thể (không xóa sản phẩm).
     * Đặt ten_danh_muc_id của sản phẩm về null.
     *
     * @param  int  $categoryId ID của danh mục.
     * @param  int  $productId  ID của sản phẩm cần gỡ.
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeProductFromCategory($categoryId, $productId)
    {
        try {
            // Kiểm tra xem danh mục có tồn tại không (tùy chọn nhưng nên có)
            $category = Category::find($categoryId);
            if (!$category) {
                return response()->json(['message' => 'Danh mục không tồn tại.'], 404);
            }

            // Tìm sản phẩm theo ID và đảm bảo nó thuộc danh mục này
            $product = SanPham::where('san_pham_id', $productId)
                            ->where('ten_danh_muc_id', $categoryId)
                            ->first();

            if (!$product) {
                return response()->json(['message' => 'Sản phẩm không tìm thấy hoặc không thuộc danh mục này.'], 404);
            }

            // Gỡ liên kết danh mục bằng cách đặt ten_danh_muc_id về null
            $product->ten_danh_muc_id = null;
            $product->save();

            return response()->json(['message' => 'Sản phẩm đã được gỡ khỏi danh mục thành công.']);

        } catch (\Exception $e) {
            Log::error("Lỗi khi gỡ sản phẩm khỏi danh mục (CategoryProductController): " . $e->getMessage() . " tại " . $e->getFile() . " dòng " . $e->getLine());
            return response()->json(['error' => 'Không thể gỡ sản phẩm khỏi danh mục. Vui lòng thử lại sau.'], 500);
        }
    }
}
