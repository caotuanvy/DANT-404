<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BinhLuan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\SanPham;
use App\Models\Tintuc;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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


    public function getByTinTucId($tinTucId)
{
    $comments = BinhLuan::where('tin_tuc_id', $tinTucId)
                       ->with('nguoiDung') // Sửa lỗi ở đây
                       ->orderBy('ngay_binh_luan', 'desc')
                       ->get();

    return response()->json($comments);
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
public function getCommentsForNews(Request $request)
    {
        $request->validate([
            'tin_tuc_id' => 'sometimes|exists:tin_tuc,id',
            'san_pham_id' => 'sometimes|exists:san_pham,san_pham_id',
        ]);

        $perPage = $request->input('per_page', 4);
        $sortBy = $request->input('sort_by', 'ngay_binh_luan');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = BinhLuan::with('nguoiDung:nguoi_dung_id,ho_ten,anh_dai_dien')
            ->where('trang_thai', 1);

        if ($request->has('tin_tuc_id')) {
            $query->where('tin_tuc_id', $request->tin_tuc_id);
        } elseif ($request->has('san_pham_id')) {
            $query->where('san_pham_id', $request->san_pham_id);
        } else {
             return response()->json(['message' => 'Vui lòng cung cấp tin_tuc_id hoặc san_pham_id.'], 400);
        }

        $comments = $query->orderBy($sortBy, $sortOrder)
            ->select(['binh_luan_id', 'tin_tuc_id', 'san_pham_id', 'nguoi_dung_id', 'noidung', 'ngay_binh_luan', 'luot_thich', 'luot_khong_thich', 'danh_gia', 'bao_cao'])
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
        $validatedData = $request->validate([
            'tin_tuc_id' => 'sometimes|integer|exists:tintuc,id',
            'san_pham_id' => 'sometimes|integer|exists:san_pham,san_pham_id',
            'nguoi_dung_id' => 'required|integer|exists:nguoi_dung,nguoi_dung_id',
            'noidung' => 'required_without:file_anh|string|nullable|max:1000',
            'file_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'danh_gia' => 'nullable|integer|min:1|max:5',
        ]);

        if (!isset($validatedData['tin_tuc_id']) && !isset($validatedData['san_pham_id'])) {
             return response()->json(['message' => 'Vui lòng cung cấp tin_tuc_id hoặc san_pham_id.'], 400);
        }

        $noidung_text = $validatedData['noidung'] ?? '';
        $html_content_for_image = '';
        $danh_gia = $validatedData['danh_gia'] ?? null;

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
                'tin_tuc_id' => $validatedData['tin_tuc_id'] ?? null,
                'san_pham_id' => $validatedData['san_pham_id'] ?? null,
                'nguoi_dung_id' => $validatedData['nguoi_dung_id'],
                'noidung' => $finalContent,
                'danh_gia' => $danh_gia,
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
            \Log::error('Lỗi khi thêm bình luận: ' . $e->getMessage());
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

public function getCommentStatistics(Request $request)
    {
        $request->validate([
            'tin_tuc_id' => 'sometimes|exists:tin_tuc,id',
            'san_pham_id' => 'sometimes|exists:san_pham,san_pham_id',
        ]);

        $query = BinhLuan::select('danh_gia', DB::raw('count(*) as total'))
            ->where('trang_thai', 1)
            ->whereNotNull('danh_gia');

        if ($request->has('tin_tuc_id')) {
            $query->where('tin_tuc_id', $request->tin_tuc_id);
        } elseif ($request->has('san_pham_id')) {
            $query->where('san_pham_id', $request->san_pham_id);
        } else {
            return response()->json(['message' => 'Vui lòng cung cấp tin_tuc_id hoặc san_pham_id.'], 400);
        }

        $statistics = $query->groupBy('danh_gia')->orderBy('danh_gia', 'desc')->get();

        $formattedStats = ['5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];
        foreach ($statistics as $stat) {
            $formattedStats[$stat->danh_gia] = $stat->total;
        }

        $totalReviews = array_sum($formattedStats);
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

public function getCommentsByRating(Request $request)
    {
        $request->validate([
            'tin_tuc_id' => 'sometimes|integer|exists:tintuc,id',
            'san_pham_id' => 'sometimes|integer|exists:san_pham,san_pham_id',
            'rating' => 'required|integer|min:1|max:5',
            'page' => 'nullable|integer',
            'sort_by' => 'nullable|string|in:ngay_binh_luan,luot_thich,luot_khong_thich',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $rating = $request->input('rating');
        $sortBy = $request->input('sort_by', 'ngay_binh_luan');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = BinhLuan::with('nguoiDung:nguoi_dung_id,ho_ten')
            ->where('trang_thai', 1)
            ->where('danh_gia', $rating);

        if ($request->has('tin_tuc_id')) {
            $query->where('tin_tuc_id', $request->tin_tuc_id);
        } elseif ($request->has('san_pham_id')) {
            $query->where('san_pham_id', $request->san_pham_id);
        } else {
             return response()->json(['message' => 'Vui lòng cung cấp tin_tuc_id hoặc san_pham_id.'], 400);
        }

        $comments = $query->orderBy($sortBy, $sortOrder)->paginate(5);
        return response()->json($comments);
    }

    // Bạn có thể cần một hàm để lấy tất cả bình luận (không cần lọc theo sao)
    // để dùng cho các nút "Mới nhất", "Phổ biến", "Cũ nhất" trên giao diện chính
   public function getCommentsByNewsId(Request $request)
    {
        $request->validate([
            'tin_tuc_id' => 'sometimes|integer|exists:tintuc,id',
            'san_pham_id' => 'sometimes|integer|exists:san_pham,san_pham_id',
            'sort_by' => 'nullable|string|in:ngay_binh_luan,luot_thich,luot_khong_thich',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $sortBy = $request->input('sort_by', 'ngay_binh_luan');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = BinhLuan::with('nguoiDung:nguoi_dung_id,ho_ten')
            ->where('trang_thai', 1);

        if ($request->has('tin_tuc_id')) {
            $query->where('tin_tuc_id', $request->tin_tuc_id);
        } elseif ($request->has('san_pham_id')) {
            $query->where('san_pham_id', $request->san_pham_id);
        } else {
             return response()->json(['message' => 'Vui lòng cung cấp tin_tuc_id hoặc san_pham_id.'], 400);
        }

        $comments = $query->orderBy($sortBy, $sortOrder)->paginate(10);
        return response()->json($comments);
    }

    public function getCommentsByProductId($san_pham_id)
    {
        // Lấy tất cả bình luận cho một sản phẩm cụ thể
        $comments = BinhLuan::where('san_pham_id', $san_pham_id)
                            ->orderBy('created_at', 'desc') // Sắp xếp theo ngày mới nhất
                            ->get();

        return response()->json($comments);
    }

    public function getComments(Request $request)
    {
        // Lấy các tham số từ request
        $sanPhamId = $request->query('san_pham_id');
        $sortBy = $request->query('sort_by', 'ngay_binh_luan'); // Mặc định là 'ngay_binh_luan'
        $sortOrder = $request->query('sort_order', 'desc'); // Mặc định là 'desc'
        $perPage = $request->query('per_page', 4); // Mặc định là 4

        // Bắt đầu truy vấn bình luận
        $query = BinhLuan::query();

        // Thêm điều kiện lọc theo san_pham_id nếu có
        if ($sanPhamId) {
            $query->where('san_pham_id', $sanPhamId);
        }

        // Thêm sắp xếp và eager load quan hệ với người dùng
        $comments = $query->with('nguoiDung')
                          ->orderBy($sortBy, $sortOrder)
                          ->paginate($perPage);

        return response()->json($comments);
    }



}
