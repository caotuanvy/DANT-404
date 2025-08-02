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
        // Bước 1: Xác thực dữ liệu đầu vào
        $validatedData = $request->validate([
            'tin_tuc_id' => 'required|integer|exists:tintuc,id',
            'nguoi_dung_id' => 'required|integer|exists:nguoi_dung,nguoi_dung_id',
            // Đã sửa tên trường từ 'image' sang 'file_anh' để khớp với frontend
            'noidung' => 'required_without:file_anh|string|nullable|max:1000',
            'file_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $noidung_text = $validatedData['noidung'] ?? ''; // Lấy nội dung văn bản
        $html_content_for_image = ''; // Biến để chứa thẻ <img> nếu có ảnh

        // Bước 2: Xử lý upload file ảnh (nếu có)
        // Đã sửa tên trường từ 'image' sang 'file_anh' để khớp với frontend
        if ($request->hasFile('file_anh')) {
            $fileAnh = $request->file('file_anh');
            // Tạo tên file duy nhất để tránh trùng lặp
            $fileName = time() . '_' . $fileAnh->getClientOriginalName();

            // Lưu file vào storage/app/public/uploads/comment_images
            $path = $fileAnh->storeAs('uploads/comment_images', $fileName, 'public');

            // Lấy đường dẫn công khai để lưu vào database
            $imageUrl = Storage::url($path);

            // Tạo thẻ <img> với đường dẫn ảnh và style cơ bản
            $html_content_for_image = "<p><img src=\"{$imageUrl}\" alt=\"Ảnh bình luận\" style=\"max-width: 100%; height: auto;\"></p>";
        }

        // Bước 3: Kết hợp nội dung văn bản và HTML của ảnh
        // Nếu có nội dung văn bản, thêm xuống dòng và sau đó là ảnh.
        // Nếu không có nội dung văn bản, chỉ là ảnh.
        $finalContent = $noidung_text;
        if (!empty($html_content_for_image)) {
            if (!empty($finalContent)) {
                $finalContent .= "\n" . $html_content_for_image; // Thêm xuống dòng nếu có cả text
            } else {
                $finalContent = $html_content_for_image; // Chỉ có ảnh
            }
        }

        // Đảm bảo rằng sau khi kết hợp, nội dung không rỗng (chỉ chứa khoảng trắng hoặc không có gì)
        // strip_tags để loại bỏ HTML và trim để loại bỏ khoảng trắng
        if (empty(trim(strip_tags($finalContent)))) {
            return response()->json(['message' => 'Nội dung bình luận không được để trống.'], 422);
        }

        // Bước 4: Lưu bình luận vào database
        try {
            DB::beginTransaction(); // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
            $binhLuan = BinhLuan::create([
                'tin_tuc_id' => $validatedData['tin_tuc_id'],
                'nguoi_dung_id' => $validatedData['nguoi_dung_id'],
                'noidung' => $finalContent, // Lưu toàn bộ nội dung HTML vào đây
                'ngay_binh_luan' => now(),
                'trang_thai' => 1, // Mặc định là hiển thị
                'bao_cao' => 0, // Mặc định là 0
            ]);
            DB::commit(); // Hoàn tất transaction

            // Tải lại mối quan hệ nguoiDung để trả về thông tin người dùng
            $binhLuan->load('nguoiDung:nguoi_dung_id,ho_ten');

            // Bước 5: Trả về kết quả cho frontend
            return response()->json([
                'message' => 'Bình luận của bạn đã được gửi.',
                'binh_luan' => $binhLuan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác transaction nếu có lỗi
            // Ghi lỗi chi tiết vào log file của Laravel để debug
            \Log::error('Lỗi khi thêm bình luận: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'file_trace' => $e->getFile() . ' on line ' . $e->getLine()
            ]);
            // Trả về lỗi 500 cho frontend
            return response()->json([
                'message' => 'Có lỗi xảy ra ở máy chủ khi thêm bình luận. Vui lòng thử lại!',
                'error' => $e->getMessage() // Chỉ trả về lỗi chi tiết trong môi trường dev
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
}
