<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\HinhAnhSanPham;
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
                    'price' => $item->gia ?? 0,
                    'description' => $item->mo_ta,
                    'noi_bat' => $item->noi_bat,
                    'khuyen_mai' => $item->khuyen_mai,
                    'trang_thai' => $item->trang_thai,
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
        $validated = $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'ten_danh_muc_id' => 'nullable|integer|exists:danh_muc_san_pham,category_id',
            'mo_ta' => 'nullable|string',
            'images' => 'nullable',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'slug' => 'required|string|max:255|unique:san_pham,slug',

        ]);

        $product = SanPham::create([
            'ten_san_pham' => $validated['ten_san_pham'],
            'ten_danh_muc_id' => $validated['ten_danh_muc_id'] ?? null,
            'mo_ta' => $validated['mo_ta'] ?? '',
            'slug' => $validated['slug'],
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
