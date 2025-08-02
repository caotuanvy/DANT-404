<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BinhLuan;

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
public function getCommentsForNews($tinTucId)
{
    $comments = BinhLuan::with('nguoiDung:nguoi_dung_id,ho_ten')
        ->where('tin_tuc_id', $tinTucId)
        ->where('trang_thai', 1)
        ->orderByDesc('ngay_binh_luan')
        ->select(['binh_luan_id', 'tin_tuc_id', 'nguoi_dung_id', 'noidung', 'ngay_binh_luan', 'luot_thich', 'luot_khong_thich']) // Thêm luot_thich
        ->get();

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
    $request->validate([
        'tin_tuc_id' => 'required|integer|exists:tintuc,id',
        'nguoi_dung_id' => 'required|integer|exists:nguoi_dung,nguoi_dung_id', // Sửa lại tên bảng
        'noidung' => 'required|string|max:1000',
    ]);

    $binhLuan = BinhLuan::create([
        'tin_tuc_id' => $request->tin_tuc_id,
        'nguoi_dung_id' => $request->nguoi_dung_id,
        'noidung' => $request->noidung,
        'ngay_binh_luan' => now(),
        'trang_thai' => 1, // Mặc định là hiển thị
        'bao_cao' => 0,
    ]);

    $binhLuan->load('nguoiDung:nguoi_dung_id,ho_ten');

    return response()->json([
        'message' => 'Bình luận của bạn đã được gửi và sẽ được hiển thị sau khi quản trị viên duyệt.',
        'binh_luan' => $binhLuan
    ], 201);
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
}
