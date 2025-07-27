<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\HinhAnhSanPham;
use App\Models\SanPhamBienThe;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Scalar\Float_;
use App\Models\Order;
use App\Models\User;

class ProductController extends Controller
{

             public function index()
    {
        try {

            $products = SanPham::with(['danhMuc', 'hinhAnhSanPham', 'bienThe'])
                                ->withCount('bienThe')
                                ->get();

            $result = $products->map(function ($item) {
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
                    'gia' => $item->gia,
                    'gia_trung_binh' => $avgPrice,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
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
                $product = SanPham::with(['danhMuc', 'hinhAnhSanPham', 'bienThe'])->findOrFail($id);

                return response()->json([
                    'product_id' => $product->san_pham_id,
                    'product_name' => $product->ten_san_pham,
                    'price' => $product->gia,
                    'description' => $product->mo_ta,
                    'trang_thai' => $product->trang_thai,
                    'noi_bat' => $product->noi_bat,
                    'khuyen_mai' => $product->khuyen_mai,
                    'slug' => $product->slug,
                    'ngay_bat_dau_giam_gia' => $product->ngay_bat_dau_giam_gia,
                    'ngay_ket_thuc_giam_gia' => $product->ngay_ket_thuc_giam_gia,
                    'the' => $product->the,
                    'Tieu_de_seo' => $product->Tieu_de_seo,
                    'Tu_khoa' => $product->Tu_khoa,
                    'Mo_ta_seo' => $product->Mo_ta_seo,
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
                    'variants' => $product->bienThe->map(function ($variant) {
                        return [
                            'san_pham_bien_the_id' => $variant->bien_the_id,
                            'ten_bien_the' => $variant->ten_bien_the,
                            'mau_sac' => $variant->mau_sac,
                            'kich_thuoc' => $variant->kich_thuoc,
                            'gia' => $variant->gia,
                            'so_luong_ton_kho' => $variant->so_luong_ton_kho,
                        ];
                    }),
                ]);
            }
                public function store(Request $request)
    {

        $request->merge(['variants' => json_decode($request->input('variants', '[]'), true)]);
        $validatedData = $request->validate([
            'ten_san_pham'          => 'required|string|max:255',
            'ten_danh_muc_id'       => 'nullable|integer|exists:danh_muc_san_pham,category_id',
            'mo_ta'                 => 'nullable|string',
            'gia'                   => 'nullable|numeric|min:0',
            'noi_bat'               => 'nullable|boolean',
            'trang_thai'            => 'nullable|boolean',
            'slug'                  => 'required|string|max:255|unique:san_pham,slug',
            'the'                  => 'nullable|string|max:255',
            'Tieu_de_seo'            => 'nullable|string|max:255',
            'Tu_khoa'         => 'nullable|string|max:255',
            'Mo_ta_seo'      => 'nullable|string|max:500',
            'ngay_bat_dau_giam_gia' => 'nullable|date',
            'ngay_ket_thuc_giam_gia'=> 'nullable|date|after_or_equal:ngay_bat_dau_giam_gia',
            'khuyen_mai'            => 'nullable|numeric|min:0|max:100',
            'images'                => 'nullable|array',
            'images.*'              => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',


            'variants'              => 'required|array|min:1',
            'variants.*.ten_bien_the'     => 'required|string|max:255',
            'variants.*.kich_thuoc'       => 'nullable|string|max:100',
            'variants.*.mau_sac'          => 'nullable|string|max:100',
            'variants.*.gia'              => 'required|numeric|min:0',
            'variants.*.so_luong_ton_kho' => 'required|integer|min:0',
            'variants.*.trong_luong'        => 'nullable|numeric|min:0',
            'variants.*.chieu_dai'          => 'nullable|numeric|min:0',
            'variants.*.chieu_rong'         => 'nullable|numeric|min:0',
            'variants.*.chieu_cao'          => 'nullable|numeric|min:0',
            'variants.*.hinh_anh'           => 'nullable|string|max:255',
        ]);

        // Bắt đầu transaction để đảm bảo toàn vẹn dữ liệu
        DB::beginTransaction();
        try {
            // 2. Tạo sản phẩm chính
            $product = SanPham::create([
                'ten_san_pham'          => $validatedData['ten_san_pham'],
                'slug'                  => $validatedData['slug'],
                'ten_danh_muc_id'       => $validatedData['ten_danh_muc_id'] ?? null,
                'gia'                   => $validatedData['gia'] ?? null,
                'mo_ta'                 => $validatedData['mo_ta'] ?? null,
                'noi_bat'               => $validatedData['noi_bat'] ?? 0,
                'trang_thai'            => $validatedData['trang_thai'] ?? 1,
                'khuyen_mai'            => $validatedData['khuyen_mai'] ?? 0,
                'the'                  => $validatedData['the'] ?? null,
                'Tieu_de_seo'            => $validatedData['Tieu_de_seo'] ?? null,
                'Tu_khoa'         => $validatedData['Tu_khoa'] ?? null,
                'Mo_ta_seo'      => $validatedData['Mo_ta_seo'] ?? null,
                'ngay_bat_dau_giam_gia' => $validatedData['ngay_bat_dau_giam_gia'] ?? null,
                'ngay_ket_thuc_giam_gia'=> $validatedData['ngay_ket_thuc_giam_gia'] ?? null,

            ]);

            // 3. Tạo các biến thể liên quan
            foreach ($validatedData['variants'] as $variantData) {
                SanPhamBienThe::create([
                    'san_pham_id'       => $product->san_pham_id,
                    'ten_bien_the'      => $variantData['ten_bien_the'],
                    'kich_thuoc'        => $variantData['kich_thuoc'] ?? null,
                    'mau_sac'           => $variantData['mau_sac'] ?? null,
                    'gia'               => $variantData['gia'],
                    'so_luong_ton_kho'  => $variantData['so_luong_ton_kho'],
                    'trong_luong'       => $variantData['trong_luong'] ?? null,
                    'chieu_dai'         => $variantData['chieu_dai'] ?? null,
                    'chieu_rong'        => $variantData['chieu_rong'] ?? null,
                    'chieu_cao'         => $variantData['chieu_cao'] ?? null,

                ]);
            }

            // 4. Xử lý upload hình ảnh
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    HinhAnhSanPham::create([
                        'san_pham_id' => $product->san_pham_id,
                        'duongdan' => $path,
                    ]);
                }
            }


            DB::commit();

            return response()->json([
                'message' => 'Thêm sản phẩm và các biến thể thành công!',
                'data' => $product->load('variants', 'hinhAnhSanPham'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Đã xảy ra lỗi trong quá trình thêm sản phẩm.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
public function update(Request $request, $id)
    {
        $sanPham = SanPham::findOrFail($id);
        $request->merge(['variants' => json_decode($request->input('variants', '[]'), true)]);
        $originalSlug = $sanPham->slug;
        $incomingSlug = $request->input('slug');
        $cleanedIncomingSlug = Str::slug($incomingSlug);
        $rules = [
            'ten_san_pham'          => 'sometimes|required|string|max:255',
            'ten_danh_muc_id'       => 'nullable|integer|exists:danh_muc_san_pham,category_id',
            'mo_ta'                 => 'nullable|string',
            'noi_bat'               => 'sometimes|boolean',
            'trang_thai'            => 'sometimes|boolean',
            'khuyen_mai'            => 'nullable|numeric|min:0|max:100',
            'the'                   => 'nullable|string|max:255',
            'Tieu_de_seo'           => 'nullable|string|max:255',
            'Tu_khoa'               => 'nullable|string|max:255',
            'Mo_ta_seo'             => 'nullable|string',
            'images'                => 'nullable|array',
            'images.*'              => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'variants'              => 'sometimes|required|array|min:1',
            'variants.*.bien_the_id' => 'nullable|integer|exists:san_pham_bien_the,bien_the_id',
            'variants.*.ten_bien_the' => 'required|string|max:255',
            'variants.*.gia'            => 'required|numeric|min:0',
            'variants.*.so_luong_ton_kho' => 'required|integer|min:0',
            'variants.*.kich_thuoc'     => 'nullable|string|max:100',
            'variants.*.mau_sac'        => 'nullable|string|max:100',
            'variants.*.trong_luong'    => 'nullable|numeric|min:0',
            'variants.*.chieu_dai'      => 'nullable|numeric|min:0',
            'variants.*.chieu_rong'     => 'nullable|numeric|min:0',
            'variants.*.chieu_cao'      => 'nullable|numeric|min:0',
        ];
        if ($cleanedIncomingSlug === $originalSlug) {
            $rules['slug'] = 'sometimes|required|string|max:255';
        } else {
            $rules['slug'] = ['sometimes', 'required', 'string', 'max:255', Rule::unique('san_pham', 'slug')->ignore($sanPham->san_pham_id, 'san_pham_id')];
        }
        $request->merge(['slug' => $cleanedIncomingSlug]);


        $validatedData = $request->validate($rules);
        DB::beginTransaction();
        try {
            $productData = $request->except(['variants', 'images', '_method']);
            $productData['slug'] = $validatedData['slug'];

            $sanPham->update($productData);

            if (isset($validatedData['variants'])) {
                $submittedVariants = collect($validatedData['variants']);
                $existingVariantIds = $sanPham->bienThe->pluck('bien_the_id')->toArray();
                $submittedExistingVariantIds = $submittedVariants
                                                ->filter(fn($v) => isset($v['bien_the_id']) && !is_null($v['bien_the_id']))
                                                ->pluck('bien_the_id')
                                                ->toArray();
                $variantsToDeleteIds = array_diff($existingVariantIds, $submittedExistingVariantIds);

                foreach ($variantsToDeleteIds as $variantIdToDelete) {
                    $variant = SanPhamBienThe::find($variantIdToDelete);
                    if ($variant) {
                        if ($variant->donHangChiTiet()->exists()) {
                            throw new \Exception("Biến thể '{$variant->ten_bien_the}' (ID: {$variantIdToDelete}) đang tồn tại trong đơn hàng. Không thể xóa biến thể đã được đặt hàng.");
                        }
                        else if ($variant->gioHangChiTiet()->exists()) {
                            throw new \Exception("Biến thể '{$variant->ten_bien_the}' (ID: {$variantIdToDelete}) đang tồn tại trong giỏ hàng. Vui lòng xóa nó khỏi giỏ hàng trước.");
                        }
                        else {
                            $variant->delete();
                        }
                    }
                }

                foreach ($submittedVariants as $variantData) {
                    if (isset($variantData['bien_the_id']) && !is_null($variantData['bien_the_id'])) {
                        $variant = SanPhamBienThe::find($variantData['bien_the_id']);
                        if ($variant) {
                            unset($variantData['bien_the_id']);
                            $variant->update($variantData);
                        }
                    } else {
                        $sanPham->bienThe()->create($variantData);
                    }
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    HinhAnhSanPham::create([
                        'san_pham_id' => $sanPham->san_pham_id,
                        'duongdan' => Storage::url($path),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Cập nhật sản phẩm thành công!',
                'data' => $sanPham->load('bienThe', 'hinhAnhSanPham')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi trong quá trình cập nhật sản phẩm.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function showSpDetail($slug)
    {
        $product = SanPham::with(['danhMuc', 'hinhAnhSanPham', 'bienThe'])
                          ->where('slug', $slug)
                          ->firstOrFail();
        $giaTrungBinh = $product->bienThe->avg('gia') ?: $product->gia;
        $tongTonKhoBienThe = $product->bienThe->sum('so_luong_ton_kho');
        return response()->json([
            'product_id' => $product->san_pham_id,
            'product_name' => $product->ten_san_pham,
            'description' => $product->mo_ta,
            'noi_bat' => (bool)$product->noi_bat,
            'khuyen_mai' => $product->khuyen_mai,
            'trang_thai' => (bool)$product->trang_thai,
            'ngay_bat_dau_giam_gia' => $product->ngay_bat_dau_giam_gia,
            'ngay_ket_thuc_giam_gia' => $product->ngay_ket_thuc_giam_gia,
            'the' => $product->the,
            'Tieu_de_seo' => $product->Tieu_de_seo,
            'Tu_khoa' => $product->Tu_khoa,
            'Mo_ta_seo' => $product->Mo_ta_seo,
            'so_bien_the' => $tongTonKhoBienThe,
            'gia' => $product->gia,
            'gia_trung_binh' => $giaTrungBinh,
            'slug' => $product->slug,
            'danh_muc' => $product->danhMuc ? [
                'category_id' => $product->danhMuc->category_id,
                'ten_danh_muc' => $product->danhMuc->ten_danh_muc,
            ] : null,
            'images' => $product->hinhAnhSanPham->map(function ($img) {

                if (Str::startsWith($img->duongdan, ['http://', 'https://'])) {
                    return $img->duongdan;
                }
                return asset('storage/' . $img->duongdan);
            })->toArray(),
            'variants' => $product->bienThe->map(function ($variant) {
                return [
                    'san_pham_bien_the_id' => $variant->bien_the_id,
                    'ten_bien_the' => $variant->ten_bien_the,
                    'mau_sac' => $variant->mau_sac,
                    'kich_thuoc' => $variant->kich_thuoc,
                    'gia' => $variant->gia,
                    'so_luong_ton_kho' => $variant->so_luong_ton_kho,
                    'trong_luong' => $variant->trong_luong,
                    'chieu_dai' => $variant->chieu_dai,
                    'chieu_rong' => $variant->chieu_rong,
                    'chieu_cao' => $variant->chieu_cao,
                ];
            })->toArray(),
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

public function getTopSelling()
    {
        $topSelling = DB::table('san_pham as sp')
            ->join('san_pham_bien_the as sbt', 'sp.san_pham_id', '=', 'sbt.san_pham_id')
            ->leftJoin('hinh_anh_san_pham as img', 'sp.san_pham_id', '=', 'img.san_pham_id')
            ->leftJoin('chi_tiet_don_hang as ctdh', 'sbt.bien_the_id', '=', 'ctdh.san_pham_bien_the_id')
            ->leftJoin('don_hang as dh', 'ctdh.don_hang_id', '=', 'dh.id')
            ->select(
                'sp.san_pham_id',
                'sp.ten_san_pham',
                'sp.khuyen_mai',
                'sp.Mo_ta_seo',
                'sp.slug',
                DB::raw('MIN(img.duongdan) as hinh_anh'),
                DB::raw('SUM(CASE WHEN dh.trang_thai = 1 THEN ctdh.so_luong ELSE 0 END) as so_luong_ban'),
                DB::raw('MIN(sbt.gia) as gia'),
                DB::raw('SUM(sbt.so_luong_ton_kho) as tong_ton_kho'),
            )
            ->where('sp.trang_thai', '!=', 0)
            ->groupBy(
                'sp.slug',
                'sp.san_pham_id',
                'sp.ten_san_pham',
                'sp.Mo_ta_seo',
                'sp.khuyen_mai'
            )
            ->orderByDesc('so_luong_ban')
            ->limit(4)
            ->get();
        $result = $topSelling->map(function ($item) {
            $maxLength = 50;
            return [
                'san_pham_id'   => $item->san_pham_id,
                'ten_san_pham'  => $item->ten_san_pham,
                'so_luong_ban'  => $item->so_luong_ban,
                'hinh_anh'      => $item->hinh_anh,
                'gia'           => $item->gia,
                'khuyen_mai'    => $item->khuyen_mai,
                'Mo_ta_seo'     => Str::limit($item->Mo_ta_seo, $maxLength, '...'),
                'slug'          => $item->slug,
                'so_luong_ton_kho'  => $item->tong_ton_kho,
                'tong_so_luong' => $item->tong_ton_kho + $item->so_luong_ban,
            ];
        });

        return response()->json($result);
    }
public function getFeatured()
{
    $featured = DB::table('san_pham as sp')
        ->join('san_pham_bien_the as sbt', 'sp.san_pham_id', '=', 'sbt.san_pham_id')
        ->leftJoin('hinh_anh_san_pham as img', 'sp.san_pham_id', '=', 'img.san_pham_id')
        ->leftJoin('chi_tiet_don_hang as ctdh', 'sbt.bien_the_id', '=', 'ctdh.san_pham_bien_the_id')
        ->leftJoin('don_hang as dh', 'ctdh.don_hang_id', '=', 'dh.id')
        ->leftJoin('danh_muc_san_pham as dm', 'sp.ten_danh_muc_id', '=', 'dm.category_id') // sửa đúng cột để join
        ->select(
            'sp.san_pham_id',
            'sp.ten_san_pham',
            'sp.khuyen_mai',
            'sp.mo_ta',
            'sp.Mo_ta_seo',
            'dm.ten_danh_muc',
            DB::raw('MIN(img.duongdan) as hinh_anh'),
            DB::raw('SUM(CASE WHEN dh.trang_thai = 1 THEN ctdh.so_luong ELSE 0 END) as so_luong_ban'),
            DB::raw('MIN(sbt.gia) as gia'),
            DB::raw('SUM(sbt.so_luong_ton_kho) as tong_ton_kho')
        )
        ->where('sp.noi_bat', 1)
        ->where('sp.trang_thai', '!=', 0)
        ->groupBy(
            'sp.san_pham_id',
            'sp.ten_san_pham',
            'sp.khuyen_mai',
            'sp.mo_ta',
            'sp.Mo_ta_seo',
            'dm.ten_danh_muc'
        )
        ->limit(8)
        ->get();

    $result = $featured->map(function ($item) {
        return [
            'san_pham_id'      => $item->san_pham_id,
            'ten_san_pham'     => $item->ten_san_pham,
            'ten_danh_muc'     => $item->ten_danh_muc,
            'mo_ta_ngan'       => $item->mo_ta,
            'so_luong_ban'     => $item->so_luong_ban,
            'hinh_anh'         => $item->hinh_anh,
            'gia'              => $item->gia,
            'khuyen_mai'       => $item->khuyen_mai,
            'Mo_ta_seo'    => $item->Mo_ta_seo,
            'tong_ton_kho'     => $item->tong_ton_kho,
            'tong_so_luong'    => $item->tong_ton_kho + $item->so_luong_ban,
            'diem_danh_gia'    => 4.8,
            'so_danh_gia'      => 100
        ];
    });

    return response()->json($result);
}
public function deactivate($id)
{
    $product = SanPham::find($id);
    if (!$product) {
        return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
    }
    $product->trang_thai = 0;
    $product->save();

    return response()->json(['message' => 'Vô hiệu hóa sản phẩm thành công']);
}
public function toggleStatus($id)
{
    $product = SanPham::find($id);
    if (!$product) {
        return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
    }
    $product->trang_thai = !$product->trang_thai;
    $product->save();

    $message = $product->trang_thai ? 'Kích hoạt sản phẩm thành công' : 'Vô hiệu hóa sản phẩm thành công';

    return response()->json(['message' => $message]);
}

 public function generateSeoContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name'      => 'required|string|max:255',
            'keywords'          => 'nullable|string|max:1000', // Từ khóa liên quan từ modal
            'detailed_content'  => 'nullable|string', // Nội dung chi tiết bổ sung từ modal
            'generate_specs'    => 'nullable|boolean', // Tùy chọn: Bao gồm thông số kỹ thuật
            'generate_manufacture' => 'nullable|boolean', // Tùy chọn: Bao gồm thông tin sản xuất
            'generate_export'   => 'nullable|boolean', // Tùy chọn: Bao gồm thông tin xuất khẩu
        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'GEMINI_API_KEY is not configured properly in config/services.php.'], 500);
        }

        $productName = $validated['product_name'];
        $keywords = $validated['keywords'] ?? ''; // Lấy từ khóa, mặc định rỗng nếu null
        $detailedContent = $validated['detailed_content'] ?? ''; // Lấy nội dung chi tiết, mặc định rỗng nếu null
        $generateSpecs = (bool)($validated['generate_specs'] ?? false);
        $generateManufacture = (bool)($validated['generate_manufacture'] ?? false);
        $generateExport = (bool)($validated['generate_export'] ?? false);


        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}";

        $callGeminiApi = function (string $prompt, bool $isJson = false, int $timeout = 30) use ($apiUrl) {
            $ch = curl_init($apiUrl);
            $payload = ['contents' => [['parts' => [['text' => $prompt]]]]];
            if ($isJson) {
                // Ensure generationConfig is an object if other fields exist,
                // or just add it as a key
                $payload['generationConfig'] = ['response_mime_type' => 'application/json'];
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new \Exception("cURL Error: " . $error);
            }
            if ($httpCode >= 400) {
                throw new \Exception("AI API HTTP Error: " . $httpCode . " - " . $response);
            }

            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from AI: " . $response);
            }

            // Gemini API's text response is under candidates[0].content.parts[0].text
            $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($generatedText)) {
                $safetyRatings = $result['promptFeedback']['safetyRatings'] ?? [];
                if (!empty($safetyRatings)) {
                    $blockedReason = implode(', ', array_column($safetyRatings, 'category'));
                    throw new \Exception('Nội dung bị chặn bởi bộ lọc an toàn của AI: ' . $blockedReason);
                }
                throw new \Exception('AI returned an empty response. Details: ' . json_encode($result));
            }

            return $generatedText;
        };

        try {
            // Xây dựng prompt cho phần SEO (Tiêu đề, Mô tả, Từ khóa)
            $promptShort = "Bạn là một chuyên gia SEO cho website thương mại điện tử tại Việt Nam kết hợp tìm kiếm trên nền tảng google. Dựa vào thông tin sản phẩm sau: "
                         . "- Tên sản phẩm: \"{$productName}\" ";
            if (!empty($keywords)) {
                $promptShort .= " - Các từ khóa liên quan: \"{$keywords}\" ";
            }
            if (!empty($detailedContent)) {
                $promptShort .= " - Thông tin chi tiết bổ sung: \"{$detailedContent}\" ";
            }

            $promptShort .= ". Hãy tạo ra các nội dung sau, tối ưu cho công cụ tìm kiếm (bằng tiếng Việt): "
                         . "1. Tiêu đề SEO: Hấp dẫn, dưới 60 ký tự."
                         . "2. Mô tả SEO (Meta Description): Một đoạn tóm tắt thu hút, dài từ 140-155 ký tự, có chứa từ khóa chính và kêu gọi hành động. "
                         . "3. Từ khóa SEO: Một danh sách 2-4 từ khóa liên quan, cách nhau bởi dấu phẩy. "
                         . "Chỉ trả về kết quả dưới dạng một đối tượng JSON hợp lệ với các key sau: \"seo_title\", \"seo_description\", \"seo_keywords\". Không thêm bất kỳ văn bản hay định dạng markdown nào khác.";

            $generatedTextShort = $callGeminiApi($promptShort, true);

            $jsonStringShort = $generatedTextShort;
            if (preg_match('/```json\s*(\{.*?\})\s*```/s', $generatedTextShort, $matches)) {
                $jsonStringShort = $matches[1];
            }

            $seoData = json_decode($jsonStringShort, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('AI returned an invalid JSON format for short content. Raw: ' . $generatedTextShort);
            }
            $promptLong = "Bạn là một người viết bài viết seo chuyên nghiệp cho website thương mại điện tử tại Việt Nam. "
                        . "Hãy viết một mô tả chi tiết, đầy đủ thông tin, hấp dẫn và có cấu trúc tốt cho sản phẩm: \"{$productName}\". ";

            if (!empty($keywords)) {
                $promptLong .= "Sử dụng các từ khóa sau: {$keywords}. ";
            }
            if (!empty($detailedContent)) {
                $promptLong .= "Dựa trên thông tin chi tiết bổ sung: {$detailedContent}. ";
            }

            if ($generateSpecs) {
                $promptLong .= "Bao gồm các thông số kỹ thuật hoặc đặc điểm nổi bật của sản phẩm. ";
            }
            if ($generateManufacture) {
                $promptLong .= "Thêm thông tin về quy trình sản xuất, chất liệu, hoặc nguồn gốc sản phẩm. ";
            }
            if ($generateExport) {
                $promptLong .= "Đề cập đến các tiêu chuẩn xuất khẩu, chứng nhận hoặc khả năng đáp ứng thị trường quốc tế (nếu phù hợp). ";
            }

            $promptLong .= "Nội dung được viết theo bố cục sau: "
                        . "<H1> Tiêu đề chính hấp dẫn (tự suy nghĩ phù hợp với sản phẩm \"{$productName}\"). </H1>"
                        . "<p> Nội dung giới thiệu sản phẩm, lợi ích tổng quan. </p>"
                        . "<H2> Tiêu đề phụ 1 (tự suy nghĩ liên quan đến \"{$productName}\", ví dụ: 'Tính năng nổi bật' hoặc 'Tại sao nên chọn?'). </H2>"
                        . "<p> Nội dung chi tiết về tính năng/lợi ích. </p>";

            if ($generateSpecs) {
                $promptLong .= "<H3> Thông số kỹ thuật/Đặc điểm chi tiết </H3>"
                             . "<p> Liệt kê các thông số, kích thước, chất liệu, v.v. (nếu có thể suy luận hoặc từ detailed_content). </p>";
            }
            if ($generateManufacture) {
                $promptLong .= "<H3> Quy trình sản xuất & Chất lượng </H3>"
                             . "<p> Mô tả ngắn gọn về cách sản phẩm được tạo ra, tiêu chuẩn chất lượng. </p>";
            }
            if ($generateExport) {
                $promptLong .= "<H3> Xuất khẩu & Tiêu chuẩn Quốc tế </H3>"
                             . "<p> Thông tin về thị trường xuất khẩu, chứng nhận quốc tế (nếu áp dụng). </p>";
            }
            $promptLong .= "<H2> Tiêu đề phụ cuối (tự suy nghĩ, ví dụ: 'Hướng dẫn sử dụng & Bảo quản' hoặc 'Cam kết của chúng tôi'). </H2>"
                        . "<p> Nội dung kết luận, hướng dẫn, hoặc lời kêu gọi hành động. </p>"
                        . "Độ dài bài viết khoảng 800 đến 1100 từ. ";
            $generatedTextLong = $callGeminiApi($promptLong, false, 60);
            $htmlContentLong = $generatedTextLong;
            $htmlContentLong = preg_replace('/^#\s*(.*?)\n/m', '<h1>$1</h1>', $htmlContentLong);
            $htmlContentLong = preg_replace('/^##\s*(.*?)\n/m', '<h2>$1</h2>', $htmlContentLong);
            $htmlContentLong = preg_replace('/^###\s*(.*?)\n/m', '<h3>$1</h3>', $htmlContentLong);
            $htmlContentLong = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $htmlContentLong);
            $htmlContentLong = preg_replace('/^\s*\*\s*(.*?)\n/m', '<li>$1</li>', $htmlContentLong);
            if (strpos($htmlContentLong, '<li>') !== false) {
                 $htmlContentLong = '<ul>' . $htmlContentLong . '</ul>';
                 $htmlContentLong = str_replace(['<li><br />', '<br /></li>'], ['<li>', '</li>'], $htmlContentLong);
            }
            $paragraphs = array_filter(explode("\n\n", $htmlContentLong));
            $finalHtmlContentLong = [];
            foreach ($paragraphs as $para) {
                if (preg_match('/<h[1-3]>|<ul|<li/', $para)) {
                    $finalHtmlContentLong[] = $para;
                } else {
                    $finalHtmlContentLong[] = '<p>' . nl2br(trim($para)) . '</p>';
                }
            }
            $htmlContentLong = implode('', $finalHtmlContentLong);
            return response()->json([
                'seo_title' => Str::limit($seoData['seo_title'] ?? '', 60),
                'seo_description' => Str::limit($seoData['seo_description'] ?? '', 160),
                'seo_keywords' => $seoData['seo_keywords'] ?? '',
                'product_description_long' => $htmlContentLong
            ]);

        } catch (\Exception $e) {

            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo nội dung AI: ' . $e->getMessage()], 500);
        }
    }

    public function showBySlug($slug)
{
    // Dùng firstOrFail() để tự động tìm hoặc trả về lỗi 404 nếu không thấy
    $product = \App\Models\SanPham::where('slug', $slug)
                                  ->with(['hinhAnhSanPham', 'bienThe', 'danhMuc'])
                                  ->firstOrFail();

    // Tái cấu trúc dữ liệu trả về để khớp với frontend
    $formattedProduct = [
        'product_id' => $product->san_pham_id,
        'product_name' => $product->ten_san_pham,
        'price' => $product->gia,
        'description' => $product->mo_ta,
        'trang_thai' => $product->trang_thai,
        'noi_bat' => $product->noi_bat,
        'khuyen_mai' => $product->khuyen_mai,
        'slug' => $product->slug,
        'the' => $product->the,
        'Tieu_de_seo' => $product->tieu_de_seo,
        'Tu_khoa' => $product->tu_khoa_seo,
        'Mo_ta_seo' => $product->mo_ta_seo,
        'danh_muc' => $product->danhMuc,
        'images' => $product->hinhAnhSanPham->map(function ($img) {
            return [
                'id' => $img->hinh_anh_id,
                'image_path' => $img->duongdan,
                'url' => asset('storage/' . $img->duongdan),
            ];
        }),
        'variants' => $product->bienThe,
    ];

    return response()->json($formattedProduct);
}

   public function uploadEditorImage(Request $request)
{
    $request->validate([
        'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);
    $imageName = time().'.'.$request->file('file')->getClientOriginalExtension();
    $request->file('file')->move(public_path('uploads/editor_images'), $imageName);
    $url = asset('uploads/editor_images/' . $imageName);
    return response()->json(['location' => $url]);
}
public function getDetailsBySlugs(Request $request)
    {
        $slugs = $request->query('slugs');
        if (!$slugs) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn'], 400);
        }
        $slugsArray = explode(',', $slugs);
        $products = SanPham::whereIn('slug', $slugsArray)
                           ->with(['bienThe', 'hinhAnhSanPham'])
                           ->get();
        $products->transform(function ($product) {
            if ($product->gia > 0) {
                $product->gia_goc_display = number_format($product->gia) . 'đ';
            } elseif ($product->bienThe->isNotEmpty()) {
                $minPrice = $product->bienThe->min('gia');
                $maxPrice = $product->bienThe->max('gia');
                $product->gia_goc_display = $minPrice == $maxPrice
                    ? number_format($minPrice) . 'đ'
                    : 'Từ ' . number_format($minPrice) . 'đ đến ' . number_format($maxPrice) . 'đ';
            } else {
                $product->gia_goc_display = 'Liên hệ';
            }
            if ($product->bienThe->isNotEmpty()) {
                $product->so_luong_ton_kho = $product->bienThe->sum('so_luong_ton_kho');
                $product->trong_luong_display = $product->bienThe->pluck('trong_luong')->filter()->unique()->implode(' / ');
                $product->mau_sac_display = $product->bienThe->pluck('mau_sac')->filter()->unique()->implode(', ');
                $dimensions = $product->bienThe->map(function ($variant) {
                    if ($variant->chieu_dai && $variant->chieu_rong && $variant->chieu_cao) {
                        return "{$variant->chieu_dai}x{$variant->chieu_rong}x{$variant->chieu_cao} cm";
                    }
                    return null;
                })->filter()->unique()->implode(' / ');
                $product->kich_thuoc_display = $dimensions;
            } else {
                $product->trong_luong_display = null;
                $product->mau_sac_display = null;
                $product->kich_thuoc_display = null;
            }

            return $product;
        });
        return response()->json($products);
    }
   public function getRevenueStatistics(Request $request)
    {
        $type = $request->query('type', 'month');
        $year = $request->query('year', date('Y'));
        $month = $request->query('month', date('m'));
        $week = $request->query('week', date('W'));

        $query = Order::query()
            ->select(DB::raw('SUM(
                (SELECT SUM(ctdh.so_luong * ctdh.don_gia) FROM chi_tiet_don_hang ctdh WHERE ctdh.don_hang_id = don_hang.id)
                + don_hang.phi_van_chuyen
            ) as total_revenue, COUNT(don_hang.id) as total_orders')); // THÊM COUNT(don_hang.id) AS total_orders

        // Điều kiện trạng thái: 1 là đã thanh toán
        $query->where('don_hang.trang_thai', 1); // Đảm bảo khớp với giá trị trong DB của bạn

        switch ($type) {
            case 'week':
                $query->addSelect(DB::raw('DATE(don_hang.ngay_tao) as date_label'))
                      ->whereYear('don_hang.ngay_tao', $year)
                      ->whereRaw('WEEK(don_hang.ngay_tao, 1) = ?', [$week])
                      ->groupBy('date_label') // THÊM GROUP BY
                      ->orderBy('date_label', 'ASC');
                break;

            case 'month':
                $query->addSelect(DB::raw('DATE(don_hang.ngay_tao) as date_label'))
                      ->whereYear('don_hang.ngay_tao', $year)
                      ->whereMonth('don_hang.ngay_tao', $month)
                      ->groupBy('date_label') // THÊM GROUP BY
                      ->orderBy('date_label', 'ASC');
                break;

            case 'year':
                $query->addSelect(DB::raw('MONTH(don_hang.ngay_tao) as month_label'))
                      ->whereYear('don_hang.ngay_tao', $year)
                      ->groupBy('month_label') // THÊM GROUP BY
                      ->orderBy('month_label', 'ASC');
                break;

            default: // Default case (month)
                $query->addSelect(DB::raw('DATE(don_hang.ngay_tao) as date_label'))
                      ->whereYear('don_hang.ngay_tao', $year)
                      ->whereMonth('don_hang.ngay_tao', $month)
                      ->groupBy('date_label') // THÊM GROUP BY
                      ->orderBy('date_label', 'ASC');
                break;
        }

        $results = $query->get();
        $formattedData = $this->formatRevenueData($results, $type, $year, $month, $week);

        return response()->json([
            'status' => 'success',
            'data' => $formattedData,
            'filter_type' => $type,
            'year' => $year,
            'month' => $month,
            'week' => $week
        ]);
    }

    protected function formatRevenueData($results, $type, $year, $month, $week)
    {
        $labels = [];
        $revenueData = [];
        $orderData = [];
        $periodMap = []; // Map để lưu cả doanh thu và đơn hàng theo thời gian

        foreach ($results as $result) {
            $key = ($type === 'year') ? $result->month_label : $result->date_label;
            $periodMap[$key] = [
                'revenue' => $result->total_revenue,
                'orders' => $result->total_orders, // Lấy số lượng đơn hàng
            ];
        }

        if ($type === 'week') {
            $startOfWeek = (new \DateTime())->setISODate($year, $week, 1);
            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->format('Y-m-d');
                $labels[] = $startOfWeek->format('D, d/m');
                $revenueData[] = $periodMap[$date]['revenue'] ?? 0;
                $orderData[] = $periodMap[$date]['orders'] ?? 0;
                $startOfWeek->modify('+1 day');
            }
        } elseif ($type === 'month') {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%d-%02d-%02d', $year, $month, $day);
                $labels[] = sprintf('%02d/%02d', $day, $month);
                $revenueData[] = $periodMap[$date]['revenue'] ?? 0;
                $orderData[] = $periodMap[$date]['orders'] ?? 0;
            }
        } elseif ($type === 'year') {
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = 'Tháng ' . $m;
                $revenueData[] = $periodMap[$m]['revenue'] ?? 0;
                $orderData[] = $periodMap[$m]['orders'] ?? 0;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doanh thu',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ],
                [ // Thêm dataset cho số đơn hàng
                    'label' => 'Số đơn hàng',
                    'data' => $orderData,
                    'backgroundColor' => 'rgba(153, 102, 255, 0.6)', // Màu khác cho số đơn hàng
                    'borderColor' => 'rgba(153, 102, 255, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }
    public function getOverallStatistics(Request $request)
    {
        $successfulStatus = 1;
        $totalOverallRevenue = Order::query()
            ->where('trang_thai', $successfulStatus)
            ->sum(DB::raw('(SELECT SUM(ctdh.so_luong * ctdh.don_gia) FROM chi_tiet_don_hang ctdh WHERE ctdh.don_hang_id = don_hang.id) + don_hang.phi_van_chuyen'));
        $totalOrders = Order::query()
            ->where('trang_thai', $successfulStatus)
            ->count();
        $currentDate = new \DateTime();
        $previousMonthDate = (new \DateTime())->modify('-1 month');
        $currentYear = $currentDate->format('Y');
        $currentMonth = $currentDate->format('m');
        $previousYear = $previousMonthDate->format('Y');
        $previousMonth = $previousMonthDate->format('m');
        $currentMonthRevenue = Order::query()
            ->where('trang_thai', $successfulStatus)
            ->whereYear('ngay_tao', $currentYear)
            ->whereMonth('ngay_tao', $currentMonth)
            ->sum(DB::raw('(SELECT SUM(ctdh.so_luong * ctdh.don_gia) FROM chi_tiet_don_hang ctdh WHERE ctdh.don_hang_id = don_hang.id) + don_hang.phi_van_chuyen'));
        $previousMonthRevenue = Order::query()
            ->where('trang_thai', $successfulStatus)
            ->whereYear('ngay_tao', $previousYear)
            ->whereMonth('ngay_tao', $previousMonth)
            ->sum(DB::raw('(SELECT SUM(ctdh.so_luong * ctdh.don_gia) FROM chi_tiet_don_hang ctdh WHERE ctdh.don_hang_id = don_hang.id) + don_hang.phi_van_chuyen'));
        $currentMonthOrderCount = Order::query()
            ->where('trang_thai', $successfulStatus)
            ->whereYear('ngay_tao', $currentYear)
            ->whereMonth('ngay_tao', $currentMonth)
            ->count();
        $previousMonthOrderCount = Order::query()
            ->where('trang_thai', $successfulStatus)
            ->whereYear('ngay_tao', $previousYear)
            ->whereMonth('ngay_tao', $previousMonth)
            ->count();
        $avgOrderValue = $totalOrders > 0 ? $totalOverallRevenue / $totalOrders : 0;
        $previousMonthAvgOrderValue = $previousMonthOrderCount > 0 ? $previousMonthRevenue / $previousMonthOrderCount : 0;
        $totalCustomers = User::count();
        $calculateGrowth = function ($current, $previous) {
            if ($previous === 0) {
                return $current > 0 ? 100 : 0;
            }
            return round((($current - $previous) / $previous) * 100, 1);
        };

        return response()->json([
            'totalOverallRevenue' => $totalOverallRevenue,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'avgOrderValue' => $avgOrderValue,
            'currentMonthRevenue' => $currentMonthRevenue,

            'overallRevenueGrowth' => $calculateGrowth($totalOverallRevenue, $previousMonthRevenue), // So sánh tổng với tháng trước
            'orderCountGrowth' => $calculateGrowth($totalOrders, $previousMonthOrderCount), // So sánh tổng với tháng trước
            'avgOrderValueGrowth' => $calculateGrowth($avgOrderValue, $previousMonthAvgOrderValue),
            'currentMonthRevenueGrowth' => $calculateGrowth($currentMonthRevenue, $previousMonthRevenue),
        ]);
    }
}   
