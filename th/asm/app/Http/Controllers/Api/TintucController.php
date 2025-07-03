<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tintuc;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

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
    // Lấy tất cả tin tức đã duyệt, sắp xếp mới nhất
    $tintucs = Tintuc::with('danhMuc')
        ->where('duyet_tin_tuc', 1)
        ->orderByDesc('ngay_dang')
        ->get();

    return response()->json($tintucs);
    }
    // tin tuc cong khai theo id
    public function chitietCongKhai($id)
    {
    $tintuc = Tintuc::with('danhMuc')->where('duyet_tin_tuc', 1)->findOrFail($id);

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
    // Lấy 5 tin tức có lượt xem cao nhất, đã duyệt
    $tins = Tintuc::where('luot_xem', 1)
        ->orderByDesc('luot_xem')
        ->take(5)
        ->get();

    return response()->json($tins);
    }
    // Tạo nội dung SEO cho tin tức
    public function generateSeoContent(Request $request): JsonResponse
{
    $validated = $request->validate([
        'tieude' => 'required|string|max:255',
        'noidung' => 'required|string|min:50',
    ]);

    $apiKey = config('services.gemini.api_key');
    if (!$apiKey) {
        return response()->json(['error' => 'GEMINI_API_KEY is not configured properly in config/services.php.'], 500);
    }

    $tieude = $validated['tieude'];
    $noidung = $validated['noidung'];

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

        $promptLong = "Bạn là một người viết bài SEO chuyên nghiệp cho website tin tức tại Việt Nam. "
            . "Hãy viết một bài mô tả chi tiết, hấp dẫn, có cấu trúc tốt cho tin tức với tiêu đề: \"{$tieude}\" và nội dung tóm tắt: \"{$noidung}\". "
            . "Bài viết nên có các phần: Giới thiệu, Nội dung chính, Phân tích, Kết luận, Lời kêu gọi hành động. "
            . "Sử dụng các thẻ tiêu đề H1, H2, H3 phù hợp. Độ dài bài viết khoảng 800-1100 từ.";
        $generatedTextLong = $callGeminiApi($promptLong, false, 60);

        // Chuyển đổi markdown sang HTML đơn giản
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
            'news_description_long' => $htmlContentLong
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Đã xảy ra lỗi khi tạo nội dung AI: ' . $e->getMessage()], 500);
    }
}



}
