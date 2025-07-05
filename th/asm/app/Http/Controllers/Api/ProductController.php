<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\HinhAnhSanPham;
use App\Models\SanPhamBienThe;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                        'gia' => $item->gia,
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
        $validatedData = $request->validate([
            'ten_san_pham'        => 'sometimes|required|string|max:255',
            'ten_danh_muc_id'     => 'nullable|integer|exists:danh_muc_san_pham,category_id',
            'mo_ta'               => 'nullable|string',
            'slug'                => ['sometimes', 'required', 'string', 'max:255', Rule::unique('san_pham', 'slug')->ignore($sanPham->san_pham_id, 'san_pham_id')],
            'noi_bat'             => 'sometimes|boolean',
            'trang_thai'          => 'sometimes|boolean',
            'khuyen_mai'          => 'nullable|numeric|min:0|max:100',
            'the'                 => 'nullable|string|max:255',
            'Tieu_de_seo'         => 'nullable|string|max:255',
            'Tu_khoa'             => 'nullable|string|max:255',
            'Mo_ta_seo'           => 'nullable|string',
            'images'              => 'nullable|array',
            'images.*'            => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'variants'            => 'sometimes|required|array|min:1',
            'variants.*.ten_bien_the'  => 'required|string|max:255',
            'variants.*.gia'          => 'required|numeric|min:0',
            'variants.*.so_luong_ton_kho' => 'required|integer|min:0',
            'variants.*.kich_thuoc'   => 'nullable|string|max:100',
            'variants.*.mau_sac'      => 'nullable|string|max:100',
            'variants.*.trong_luong'  => 'nullable|numeric|min:0',
            'variants.*.chieu_dai'    => 'nullable|numeric|min:0',
            'variants.*.chieu_rong'   => 'nullable|numeric|min:0',
            'variants.*.chieu_cao'    => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $sanPham->update($validatedData);
            if (isset($validatedData['variants'])) {
                $sanPham->bienThe()->delete();
                foreach ($validatedData['variants'] as $variantData) {
                    $sanPham->bienThe()->create($variantData);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    HinhAnhSanPham::create([
                        'san_pham_id' => $sanPham->san_pham_id,
                        'duongdan' => $path,
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
            'product_name' => 'required|string|max:255',

        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'GEMINI_API_KEY is not configured properly in config/services.php.'], 500);
        }

        $productName = $validated['product_name'];


        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}";

        $callGeminiApi = function (string $prompt, bool $isJson = false, int $timeout = 30) use ($apiUrl) {
            $ch = curl_init($apiUrl);
            $payload = ['contents' => [['parts' => [['text' => $prompt]]]]];
            if ($isJson) {
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

            $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (empty($generatedText)) {
                $safetyRating = $result['promptFeedback']['safetyRatings'][0]['category'] ?? null;
                if ($safetyRating) {
                    throw new \Exception('Nội dung bị chặn bởi bộ lọc an toàn của AI: ' . $safetyRating);
                }
                throw new \Exception('AI returned an empty response. Details: ' . json_encode($result));
            }

            return $generatedText;
        };

        try {
            $promptShort = "Bạn là một chuyên gia SEO cho website thương mại điện tử tại Việt Nam kết hợp tìm kiếm trên nền tảng google. Dựa vào thông tin sản phẩm sau: "
                         . "- Tên sản phẩm: \"{$productName}\" "
                         . ". Hãy tạo ra các nội dung sau, tối ưu cho công cụ tìm kiếm (bằng tiếng Việt): "
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
                        . "Hãy viết một mô tả chi tiết, đầy đủ thông tin, hấp dẫn và có cấu trúc tốt cho sản phẩm: \"{$productName}\". "
                        . "Nội dung được viết theo bố cục sau."
                        ."<H1> tự suy nghỉ tiêu đề chính phù hợp cho sản phẩm \"{$productName}\". </H1>"
                        . "<p>Nội dung /<p>."
                        . "<H2> tự suy nghỉ tiêu đề phụ liên quan đến \"{$productName}\". </H2>"
                        . "<p>Nội dung /<p>."
                        . "<H3> tự suy nghủ  tiêu đề con liên quan đến \"{$productName}\". </H3>"
                        . "<p>Nội dung /<p>."
                        . "Độ dài bài viết khoảng 800 đến 1100 từ. "
                        . "Nội dung nên bao gồm các phần như: Giới thiệu sản phẩm, Tính năng nổi bật, Thông số kỹ thuật (nếu có), Lợi ích sử dụng, Hướng dẫn sử dụng (nếu có), Lời kêu gọi hành động.";
            $generatedTextLong = $callGeminiApi($promptLong, false, 60);

            // --- CHUYỂN ĐỔI MARKDOWN SANG HTML THỦ CÔNG
            $htmlContentLong = nl2br($generatedTextLong);
            $htmlContentLong = preg_replace('/^#\s*(.*?)\n/m', '<h1>$1</h1>', $htmlContentLong);
            $htmlContentLong = preg_replace('/^##\s*(.*?)\n/m', '<h2>$1</h2>', $htmlContentLong);
            $htmlContentLong = preg_replace('/^###\s*(.*?)\n/m', '<h3>$1</h3>', $htmlContentLong);
            $htmlContentLong = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $htmlContentLong);
            $htmlContentLong = preg_replace('/^\s*\*\s*(.*?)\n/m', '<li>$1</li>', $htmlContentLong);
            if (strpos($htmlContentLong, '<li>') !== false) {
                 $htmlContentLong = '<ul>' . $htmlContentLong . '</ul>';
                 $htmlContentLong = str_replace(['<li><br />', '<br /></li>'], ['<li>', '</li>'], $htmlContentLong);
            }
            $htmlContentLong = str_replace('<br />', '', $htmlContentLong);
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

    // Tạo tên file mới
    $imageName = time().'.'.$request->file('file')->getClientOriginalExtension();

    // Di chuyển file vào thư mục public/uploads/editor_images
    $request->file('file')->move(public_path('uploads/editor_images'), $imageName);

    // Tạo URL công khai
    $url = asset('uploads/editor_images/' . $imageName);

    return response()->json(['location' => $url]);
}

}
