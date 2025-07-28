<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tintuc;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TintucController extends Controller
{
    // Lấy danh sách tin tức
    public function index()
    {
        $tintucs = Tintuc::with('danhMuc')->get();
        return response()->json($tintucs);
    }

        public function store(Request $request)
    {
    $data = $request->validate([
        'id_danh_muc_tin_tuc' => 'required|integer',
        'tieude' => 'required|string|max:255',
        'noidung' => 'required|string',
        'ngay_dang' => 'nullable|date',
        'noi_bat' => 'nullable|boolean',
        'duyet_tin_tuc' => 'nullable|boolean',
        'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        'slug' => 'nullable|string|max:225',
        'trang_thai' => 'nullable|boolean',
        'tieu_de_seo' => 'nullable|string|max:255',
        'mo_ta_seo' => 'nullable|string',
        'tu_khoa_seo' => 'nullable|string|max:255',
    ]);

    if ($request->hasFile('hinh_anh')) {
        $file = $request->file('hinh_anh');
        $path = $file->store('Tintuc', 'public');
        $data['hinh_anh'] = $path;
    }

    // Set mặc định nếu không truyền từ frontend
    $data['trang_thai'] = $data['trang_thai'] ?? 1;
    $data['luot_like'] = 0;
    $data['luot_xem'] = 0;

    $tintuc = Tintuc::create($data);
    return response()->json($tintuc, 201);
    }

    // Lấy chi tiết tin tức
    public function show($id)
    {
        $tintuc = Tintuc::with('danhMuc')->findOrFail($id);
        return response()->json($tintuc);
    }

    // Cập nhật tin tức
       public function update(Request $request, $id)
    {
    $tintuc = Tintuc::findOrFail($id);
    $data = $request->validate([
        'id_danh_muc_tin_tuc' => 'sometimes|integer',
        'tieude' => 'sometimes|string|max:255',
        'noidung' => 'sometimes|string',
        'ngay_dang' => 'nullable|date',
        'noi_bat' => 'nullable|boolean',
        'duyet_tin_tuc' => 'nullable|boolean',
        'trang_thai' => 'nullable|boolean',
        'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        'slug' => 'nullable|string|max:225',
        'tieu_de_seo' => 'nullable|string|max:255',
        'mo_ta_seo' => 'nullable|string',
        'tu_khoa_seo' => 'nullable|string|max:255',
    ]);

    if ($request->hasFile('hinh_anh')) {
        $file = $request->file('hinh_anh');
        $path = $file->store('Tintuc', 'public');
        $data['hinh_anh'] = $path;
    }

    $tintuc->update($data);
    return response()->json($tintuc);
    }

    // Xóa tin tức
   public function destroy($id)
    {
    $tintuc = Tintuc::findOrFail($id);
    $tintuc->trang_thai = 0;
    $tintuc->save();
    return response()->json(['message' => 'Tin tức đã được ẩn thành công']);
    }

    //xem chi tiet tintuc admin
    public function xemchitiettintucadmin($id)
    {
    $tintuc = Tintuc::with('danhMuc')->findOrFail($id);

    // Trả về đầy đủ các trường, bao gồm slug và cả danh mục liên quan
    return response()->json([
        'id' => $tintuc->id,
        'id_danh_muc_tin_tuc' => $tintuc->id_danh_muc_tin_tuc,
        'tieude' => $tintuc->tieude,
        'hinh_anh' => $tintuc->hinh_anh,
        'noidung' => $tintuc->noidung,
        'ngay_dang' => $tintuc->ngay_dang,
        'noi_bat' => $tintuc->noi_bat,
        'duyet_tin_tuc' => $tintuc->duyet_tin_tuc,
        'slug' => $tintuc->slug,
        'danhMuc' => $tintuc->danhMuc, // trả về cả thông tin danh mục nếu cần
    ]);
    }
    // tin tuc công khai
    public function tintucCongKhai()
    {
    // Lấy tất cả tin tức đã duyệt VÀ có trạng thái là 1 (đang hiển thị), sắp xếp mới nhất
    $tintucs = Tintuc::with('danhMuc')
        //->where('duyet_tin_tuc', 1)
        ->where('trang_thai', 1) // THÊM ĐIỀU KIỆN NÀY
        ->orderByDesc('ngay_dang')
        ->get();

    return response()->json($tintucs);
    }
    // tin tuc cong khai theo id
    public function chitietCongKhaiBySlug($slug)
    {
    // Lấy tin tức công khai theo slug
    $tintuc = Tintuc::with('danhMuc')
        ->where('slug', $slug)
        ->where('trang_thai', 1)
        ->first();

    if (!$tintuc) {
        return response()->json(['message' => 'Không tìm thấy tin tức'], 404);
    }

    return response()->json([
        'id' => $tintuc->id,
        'id_danh_muc_tin_tuc' => $tintuc->id_danh_muc_tin_tuc,
        'tieude' => $tintuc->tieude,
        'hinh_anh' => $tintuc->hinh_anh,
        'noidung' => $tintuc->noidung,
        'ngay_dang' => $tintuc->ngay_dang,
        'noi_bat' => $tintuc->noi_bat,
        'slug' => $tintuc->slug,
        'danhMuc' => $tintuc->danhMuc,
        'luot_like' => $tintuc->luot_like,
        'luot_xem' => $tintuc->luot_xem,
    ]);
   }
    // Lấy tin tức nổi bật
   public function tinNoiBat()
    {
    $tins = Tintuc::where('trang_thai', 1) // BỎ ĐIỀU KIỆN 'duyet_tin_tuc' NẾU KHÔNG CẦN
        // ->where('duyet_tin_tuc', 1) // BỎ DÒNG NÀY ĐI HOẶC COMMENT LẠI
        ->orderByDesc('luot_xem')
        ->take(5)
        ->get();
    // Trả về danh sách tin tức nổi bật
    return response()->json($tins);
    }
    // Tạo nội dung SEO cho tin tức
    public function generateSeoContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tieude' => 'required|string|max:255',
            'noidung' => 'required|string|min:50', // Sử dụng noidung như một phần của prompt cho nội dung dài
        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'GEMINI_API_KEY is not configured properly in config/services.php.'], 500);
        }

        $tieude = $validated['tieude'];
        $noidung = $validated['noidung']; // Đây là nội dung tóm tắt từ Vue (tieude)

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}";

        $callGeminiApi = function (string $prompt, bool $isJson = false, int $timeout = 60) use ($apiUrl) {
            $ch = curl_init($apiUrl);
            $payload = ['contents' => [['parts' => [['text' => $prompt]]]]];
            if ($isJson) {
                $payload['generationConfig'] = ['response_mime_type' => 'application/json'];
            } else {
                // Tăng maxOutputTokens cho nội dung HTML dài
                $payload['generationConfig'] = ['maxOutputTokens' => 4096]; // Đặt giá trị phù hợp, 4096 là khá lớn
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

            // Kiểm tra và xử lý safety feedback
            if (isset($result['promptFeedback']['safetyRatings'])) {
                foreach ($result['promptFeedback']['safetyRatings'] as $rating) {
                    if ($rating['probability'] !== 'NEGLIGIBLE') { // Có thể điều chỉnh ngưỡng này
                        throw new \Exception('Nội dung bị chặn bởi bộ lọc an toàn của AI: ' . $rating['category'] . ' (' . $rating['probability'] . ')');
                    }
                }
            }

            $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (empty($generatedText)) {
                throw new \Exception('AI returned an empty response. Details: ' . json_encode($result));
            }

            return $generatedText;
        };

        try {
            // Prompt SEO (JSON)
            $promptShort = "Bạn là chuyên gia SEO cho website tin tức tại Việt Nam. Dựa vào thông tin sau:\n"
                . "- Tiêu đề tin tức: \"{$tieude}\"\n"
                . "- Nội dung tóm tắt: \"{$noidung}\"\n"
                . "Hãy tạo ra các nội dung sau, tối ưu cho công cụ tìm kiếm (bằng tiếng Việt): "
                . "1. Tiêu đề SEO: Hấp dẫn, dưới 60 ký tự. "
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

            // Prompt yêu cầu trả về HTML thực sự
            $promptLong = "Bạn là một người viết bài SEO chuyên nghiệp cho website tin tức tại Việt Nam. "
                . "Hãy viết một bài mô tả chi tiết, hấp dẫn, có cấu trúc tốt cho tin tức với tiêu đề: \"{$tieude}\" và nội dung tóm tắt: \"{$noidung}\". "
                . "Bài viết nên có các phần: Giới thiệu, Nội dung chính, Phân tích, Kết luận, Lời kêu gọi hành động. "
                . "Sử dụng các thẻ HTML chuẩn như <h1>, <h2>, <h3>, <ul>, <li>, <p>, <strong>, <img>, ... "
                . "Đảm bảo thẻ <h1> chỉ xuất hiện một lần và là tiêu đề chính của bài viết. "
                . "Độ dài bài viết khoảng 800-1100 từ. "
                . "CHỈ TRẢ VỀ NỘI DUNG HTML CỦA PHẦN BODY, KHÔNG CÓ CÁC THẺ <html>, <head>, <body>. "
                . "KHÔNG TRẢ VỀ JSON, KHÔNG TRẢ VỀ MARKDOWN (như ```html), KHÔNG GIẢI THÍCH GÌ THÊM. "
                . "Bắt đầu trực tiếp với thẻ HTML đầu tiên của nội dung bài viết.";

            $htmlContentLong = $callGeminiApi($promptLong, false, 90); // Tăng timeout cho nội dung dài

            // Xử lý loại bỏ markdown code block nếu AI vẫn trả về
            if (preg_match('/```html\s*(.*?)\s*```/s', $htmlContentLong, $matches)) {
                $htmlContentLong = $matches[1];
            }

            // Loại bỏ các thẻ bao bọc không mong muốn ở đầu và cuối chuỗi HTML
            // Sử dụng regex để tìm và loại bỏ các thẻ như <ul>, <ol>, <div>, <p> nếu chúng nằm ở đầu hoặc cuối nội dung một cách không hợp lý.
            // Điều này giúp loại bỏ trường hợp AI tự thêm <ul> ở ngoài cùng như ví dụ của bạn.
            $htmlContentLong = preg_replace('/^\s*<(ul|ol|div|p)[^>]*>(.*?)<\/\1>\s*$/is', '$2', $htmlContentLong);
            $htmlContentLong = trim($htmlContentLong);

            return response()->json([
                'seo_title' => Str::limit($seoData['seo_title'] ?? '', 60),
                'seo_description' => Str::limit($seoData['seo_description'] ?? '', 160),
                'seo_keywords' => $seoData['seo_keywords'] ?? '',
                'news_description_long' => $htmlContentLong
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo nội dung AI: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Xử lý upload ảnh từ TinyMCE
     */
    public function uploadTinyMCEImage(Request $request): JsonResponse
    {
        // Kiểm tra xem có file nào được gửi lên không với tên 'file'
        // TinyMCE gửi file với trường 'file' theo mặc định
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Kiểm tra loại file có phải là ảnh không
            if ($file->isValid() && Str::startsWith($file->getMimeType(), 'image')) {
                try {
                    // Tạo tên file duy nhất để tránh trùng lặp
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    // Lưu file vào thư mục 'public/uploads/tinymce'
                    // Đảm bảo thư mục public/uploads/tinymce có quyền ghi
                    $path = $file->storeAs('public/uploads/tinymce', $filename, 'public'); // Thêm 'public' disk ở đây

                    // Lấy URL công khai của ảnh
                    $location = Storage::url($path);

                    // Trả về JSON theo định dạng TinyMCE mong đợi
                    return response()->json(['location' => $location]);

                } catch (\Exception $e) {
                    // Ghi log lỗi để dễ debug
                    \Log::error('TinyMCE upload error: ' . $e->getMessage());
                    return response()->json(['error' => 'Lỗi server khi lưu ảnh: ' . $e->getMessage()], 500);
                }
            } else {
                return response()->json(['error' => 'File không hợp lệ hoặc không phải là ảnh.'], 400);
            }
        } else {
            return response()->json(['error' => 'Không có file nào được tải lên.'], 400);
        }
    }
    // Lấy tin tức liên quan theo danh mục
   public function tinLienQuan($currentNewsId, $categoryId)
   {
    try {
        $relatedNews = Tintuc::with('danhMuc')
            ->where('id_danh_muc_tin_tuc', $categoryId)
            ->where('id', '!=', $currentNewsId)
            ->where('trang_thai', 1)
            ->orderBy('ngay_dang', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'tieude' => $item->tieude,
                    'hinh_anh' => $item->hinh_anh,
                    'noidung' => $item->noidung,
                    'ngay_dang' => $item->ngay_dang,
                    'danhMuc' => $item->danhMuc,
                ];
            });

        return response()->json($relatedNews);

    } catch (\Exception $e) {
        \Log::error("Lỗi khi lấy tin tức liên quan: " . $e->getMessage());
        return response()->json(['message' => 'Không thể tải tin tức liên quan.'], 500);
    }
   }
   

}
