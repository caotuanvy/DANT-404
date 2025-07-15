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
}
