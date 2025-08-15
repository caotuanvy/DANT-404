<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BinhLuan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BinhLuanController extends Controller
{
    // Lấy danh sách bình luận (có lọc theo loại và trạng thái báo cáo)
    public function index(Request $request)
    {
        $query = BinhLuan::with([
            'nguoiDung:nguoi_dung_id,ho_ten',
            'sanPham:san_pham_id,ten_san_pham',
            'tinTuc:id'
        ])->orderByDesc('ngay_binh_luan');

        // Lọc theo loại bình luận (sản phẩm hoặc tin tức)
        if ($request->loai === 'san_pham') {
            $query->whereNotNull('san_pham_id');
        } elseif ($request->loai === 'tin_tuc') {
            $query->whereNotNull('tin_tuc_id');
        }

        // Lọc theo trạng thái báo cáo (0, 1, 2)
        if ($request->has('bao_cao')) {
            $query->where('bao_cao', $request->bao_cao);
        }

        // Sử dụng paginate để phân trang dữ liệu
        $binhLuans = $query->paginate(15);

        return response()->json($binhLuans);
    }

    // Toggle trạng thái hiển thị (ẩn/hiện)
    public function toggleTrangThai($id)
    {
        $binhLuan = BinhLuan::findOrFail($id);
        $binhLuan->trang_thai = $binhLuan->trang_thai == 1 ? 0 : 1;
        $binhLuan->save();

        return response()->json([
            'message' => 'Đã cập nhật trạng thái hiển thị.',
            'trang_thai_moi' => $binhLuan->trang_thai
        ]);
    }

    /**
     * Cập nhật trạng thái báo cáo của bình luận.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setBaoCao(Request $request, $id)
    {
        $binhLuan = BinhLuan::findOrFail($id);

        // Validate the incoming request for 'bao_cao' field
        $request->validate([
            'bao_cao' => 'required|integer|in:0,1,2', // Đảm bảo giá trị là 0, 1 hoặc 2
        ]);

        $binhLuan->bao_cao = $request->bao_cao;
        $binhLuan->save();

        return response()->json([
            'message' => 'Đã cập nhật trạng thái báo cáo thành công.',
            'bao_cao_moi' => $binhLuan->bao_cao
        ]);
    }

    // ... (các method hiện có)

/**
 * Lấy danh sách bình luận cho một tin tức cụ thể.
 *
 * @param  int  $tinTucId
 * @return \Illuminate\Http\JsonResponse
 */
public function getCommentsForNews($tinTucId, Request $request)
{
    $perPage = $request->input('per_page', 4);
    $sortBy = $request->input('sort_by', 'ngay_binh_luan');
    $sortOrder = $request->input('sort_order', 'desc');

    // Cập nhật dòng này: thêm 'anh_dai_dien' vào danh sách các trường cần lấy
    $comments = BinhLuan::with('nguoiDung:nguoi_dung_id,ho_ten,anh_dai_dien')
        ->where('tin_tuc_id', $tinTucId)
        ->where('trang_thai', 1)
        ->orderBy($sortBy, $sortOrder)
        ->select(['binh_luan_id', 'tin_tuc_id', 'nguoi_dung_id', 'noidung', 'ngay_binh_luan', 'luot_thich', 'luot_khong_thich', 'danh_gia', 'bao_cao'])
        ->paginate($perPage);

    return response()->json($comments);
}
/**
 * Thêm một bình luận mới cho tin tức.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 */
public function addCommentForNews(Request $request)
    {
        // Bước 1: Xác thực dữ liệu đầu vào
        $validatedData = $request->validate([
            'tin_tuc_id' => 'required|integer|exists:tintuc,id',
            'nguoi_dung_id' => 'required|integer|exists:nguoi_dung,nguoi_dung_id',
            'noidung' => 'required_without:file_anh|string|nullable|max:1000',
            'file_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'danh_gia' => 'nullable|integer|min:1|max:5', // Thêm validation cho danh_gia
        ]);

        $noidung_text = $validatedData['noidung'] ?? '';
        $html_content_for_image = '';
        $danh_gia = $validatedData['danh_gia'] ?? null; // Lấy giá trị danh_gia

        if ($request->hasFile('file_anh')) {
            $fileAnh = $request->file('file_anh');
            $fileName = time() . '_' . $fileAnh->getClientOriginalName();
            $path = $fileAnh->storeAs('uploads/comment_images', $fileName, 'public');
            $imageUrl = Storage::url($path);
            $html_content_for_image = "<p><img src=\"{$imageUrl}\" alt=\"Ảnh bình luận\" style=\"max-width: 100%; height: auto;\"></p>";
        }

        $finalContent = $noidung_text;
        if (!empty($html_content_for_image)) {
            if (!empty($finalContent)) {
                $finalContent .= "\n" . $html_content_for_image;
            } else {
                $finalContent = $html_content_for_image;
            }
        }

        if (empty(trim(strip_tags($finalContent))) && is_null($danh_gia)) {
             return response()->json(['message' => 'Nội dung bình luận hoặc đánh giá không được để trống.'], 422);
        }

        try {
            DB::beginTransaction();
            $binhLuan = BinhLuan::create([
                'tin_tuc_id' => $validatedData['tin_tuc_id'],
                'nguoi_dung_id' => $validatedData['nguoi_dung_id'],
                'noidung' => $finalContent,
                'danh_gia' => $danh_gia, // Thêm giá trị đánh giá vào database
                'ngay_binh_luan' => now(),
                'trang_thai' => 1,
                'bao_cao' => 0,
            ]);
            DB::commit();

            $binhLuan->load('nguoiDung:nguoi_dung_id,ho_ten');

            return response()->json([
                'message' => 'Bình luận của bạn đã được gửi.',
                'binh_luan' => $binhLuan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Lỗi khi thêm bình luận: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'file_trace' => $e->getFile() . ' on line ' . $e->getLine()
            ]);
            return response()->json([
                'message' => 'Có lỗi xảy ra ở máy chủ khi thêm bình luận. Vui lòng thử lại!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
public function toggleLike($id)
{
    $binhLuan = BinhLuan::findOrFail($id);

    // Tăng lượt thích lên 1
    $binhLuan->luot_thich = ($binhLuan->luot_thich ?? 0) + 1;

    $binhLuan->save();

    return response()->json([
        'luot_thich' => $binhLuan->luot_thich,
    ]);
}

public function toggleDislike($id)
{
    $binhLuan = BinhLuan::findOrFail($id);

    // Tăng lượt không thích lên 1
    $binhLuan->luot_khong_thich = ($binhLuan->luot_khong_thich ?? 0) + 1;

    $binhLuan->save();

    return response()->json([
        'luot_khong_thich' => $binhLuan->luot_khong_thich
    ]);
}

public function getCommentStatistics($tinTucId)
    {
        // Giữ nguyên hàm này, không cần thay đổi
        $statistics = BinhLuan::select('danh_gia', DB::raw('count(*) as total'))
            ->where('tin_tuc_id', $tinTucId)
            ->where('trang_thai', 1)
            ->whereNotNull('danh_gia')
            ->groupBy('danh_gia')
            ->orderBy('danh_gia', 'desc')
            ->get();

        $formattedStats = [
            '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0
        ];
        foreach ($statistics as $stat) {
            $formattedStats[$stat->danh_gia] = $stat->total;
        }

        $totalReviews = array_sum($formattedStats);

        // Tính toán điểm đánh giá trung bình
        $weightedSum = 0;
        foreach ($statistics as $stat) {
            $weightedSum += $stat->danh_gia * $stat->total;
        }
        $averageRating = $totalReviews > 0 ? round($weightedSum / $totalReviews, 1) : 0;

        return response()->json([
            'total' => $totalReviews,
            'average' => $averageRating,
            'stats' => $formattedStats
        ]);
    }

public function getCommentsByRating(Request $request, $tinTucId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'page' => 'nullable|integer',
            'sort_by' => 'nullable|string|in:ngay_binh_luan,luot_thich,luot_khong_thich',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $rating = $request->input('rating');
        $sortBy = $request->input('sort_by', 'ngay_binh_luan'); // Mặc định: 'ngay_binh_luan'
        $sortOrder = $request->input('sort_order', 'desc'); // Mặc định: 'desc'

        $comments = BinhLuan::with('nguoiDung:nguoi_dung_id,ho_ten')
            ->where('tin_tuc_id', $tinTucId)
            ->where('trang_thai', 1)
            ->where('danh_gia', $rating)
            ->orderBy($sortBy, $sortOrder) // Áp dụng sắp xếp động
            ->paginate(5);

        // API trả về dữ liệu phân trang. Frontend sẽ cần xử lý `current_page`, `last_page`, `data`
        return response()->json($comments);
    }

    // Bạn có thể cần một hàm để lấy tất cả bình luận (không cần lọc theo sao)
    // để dùng cho các nút "Mới nhất", "Phổ biến", "Cũ nhất" trên giao diện chính
    public function getCommentsByNewsId(Request $request, $tinTucId)
    {
        $request->validate([
            'sort_by' => 'nullable|string|in:ngay_binh_luan,luot_thich,luot_khong_thich',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $sortBy = $request->input('sort_by', 'ngay_binh_luan');
        $sortOrder = $request->input('sort_order', 'desc');

        $comments = BinhLuan::with('nguoiDung:nguoi_dung_id,ho_ten')
            ->where('tin_tuc_id', $tinTucId)
            ->where('trang_thai', 1)
            ->orderBy($sortBy, $sortOrder)
            ->paginate(10); // Phân trang cho trang chính

        return response()->json($comments);
    }


}
