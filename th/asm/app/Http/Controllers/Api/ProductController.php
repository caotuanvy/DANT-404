<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\HinhAnhSanPham;
use App\Models\SanPhamBienThe;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
            public function index()
        {
            try {
                $products = SanPham::with(['danhMuc', 'hinhAnhSanPham'])->withCount('bienThe')->withAvg('bienThe', 'gia')->get();

                $result = $products->map(function ($item) {
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
                        'gia_trung_binh' => $item->bien_the_avg_gia,
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
                DB::raw('MIN(img.duongdan) as hinh_anh'),
                DB::raw('SUM(CASE WHEN dh.trang_thai = 1 THEN ctdh.so_luong ELSE 0 END) as so_luong_ban'),
                DB::raw('MIN(sbt.gia) as gia'),
                DB::raw('SUM(sbt.so_luong_ton_kho) as tong_ton_kho'),
            )
            ->groupBy(
                'sp.san_pham_id',
                'sp.ten_san_pham',
                'sp.khuyen_mai'
            )
            ->orderByDesc('so_luong_ban')
            ->limit(4)
            ->get();
        $result = $topSelling->map(function ($item) {
            return [
                'san_pham_id'   => $item->san_pham_id,
                'ten_san_pham'  => $item->ten_san_pham,
                'so_luong_ban'  => $item->so_luong_ban,
                'hinh_anh'      => $item->hinh_anh,
                'gia'           => $item->gia,
                'khuyen_mai'    => $item->khuyen_mai,
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
            'dm.ten_danh_muc',
            DB::raw('MIN(img.duongdan) as hinh_anh'),
            DB::raw('SUM(CASE WHEN dh.trang_thai = 1 THEN ctdh.so_luong ELSE 0 END) as so_luong_ban'),
            DB::raw('MIN(sbt.gia) as gia'),
            DB::raw('SUM(sbt.so_luong_ton_kho) as tong_ton_kho')
        )
        ->where('sp.noi_bat', 1)
        ->groupBy(
            'sp.san_pham_id',
            'sp.ten_san_pham',
            'sp.khuyen_mai',
            'sp.mo_ta',
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


}
