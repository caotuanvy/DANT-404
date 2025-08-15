<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMucTinTuc;
use Illuminate\Http\Request;
use App\Models\Tintuc;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\DanhMuc;

class DanhMucTtController extends Controller
{
    public function index()
    {
        $danhMuc = DanhMucTinTuc::all();
        return response()->json($danhMuc);
    }

    public function show($id)
    {
        $danhMuc = DanhMucTinTuc::findOrFail($id);
        return response()->json($danhMuc);
    }

    // toggle trạng thái danh mục tin tức
    public function toggleStatus(Request $request, $id)
    {
        $danhMuc = DanhMucTinTuc::findOrFail($id);

        // Xác thực dữ liệu đầu vào: đảm bảo trang_thai là boolean và là trường bắt buộc
        $request->validate([
            'trang_thai' => 'required|boolean',
        ]);

        $danhMuc->trang_thai = $request->trang_thai;
        $danhMuc->save();

        $statusText = $request->trang_thai == 1 ? 'hiện' : 'ẩn';

        return response()->json([
            'message' => "Đã {$statusText} danh mục '{$danhMuc->ten_danh_muc}' thành công!",
            'danh_muc' => $danhMuc // Trả về tài nguyên đã được cập nhật
        ], 200); // Mã trạng thái 200 OK
    }

    // Sửa danh mục tin tức
    public function update($id, Request $request)
    {
        $danhMuc = DanhMucTinTuc::findOrFail($id);

        $validated = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'trang_thai' => 'nullable|in:0,1',
        ]);

        $danhMuc->ten_danh_muc = $validated['ten_danh_muc'];
        $danhMuc->mo_ta = $validated['mo_ta'] ?? null;

        if (isset($validated['trang_thai'])) {
            $danhMuc->trang_thai = $validated['trang_thai'];
        }

        // Xử lý upload file nếu có file mới
        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $path = $file->store('Tintuc', 'public');
            $danhMuc->hinh_anh = $path;
        }

        $danhMuc->ngay_sua = now();
        $danhMuc->save();

        return response()->json([
            'message' => 'Cập nhật danh mục thành công',
            'data' => $danhMuc
        ]);
    }

    // Thêm danh mục tin tức
    public function store(Request $request)
    {
        $data = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'trang_thai' => 'nullable|in:0,1',
        ]);

        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $path = $file->store('Tintuc', 'public');
            $data['hinh_anh'] = $path;
        }

        $data['ngay_tao'] = now();

        $danhMuc = DanhMucTinTuc::create($data);
        return response()->json($danhMuc, 201);
    }

    // Xem chi tiết danh mục tin tức
    public function xemChiTietDanhMucAdmin($id)
    {
        $danhMuc = DanhMucTinTuc::findOrFail($id);

        return response()->json([
            'id_danh_muc_tin_tuc' => $danhMuc->id_danh_muc_tin_tuc,
            'ten_danh_muc' => $danhMuc->ten_danh_muc,
            'mo_ta' => $danhMuc->mo_ta,
            'hinh_anh' => $danhMuc->hinh_anh,
            'trang_thai' => $danhMuc->trang_thai,
            'ngay_tao' => $danhMuc->ngay_tao,
            'ngay_sua' => $danhMuc->ngay_sua,
        ]);
    }

    // danh muc tin tuc cong khai theo danh muc
    public function tintucCongKhaiTheoDanhMuc($id_danh_muc)
    {
    $tintucs = Tintuc::with('danhMuc')
        ->where('id_danh_muc_tin_tuc', $id_danh_muc)
        //->where('duyet_tin_tuc', 1) // Giữ nguyên điều kiện duyệt
        ->where('trang_thai', 1) // CÁI NÀY LÀ MỚI THÊM VÀ RẤT QUAN TRỌNG
        ->orderByDesc('ngay_dang')
        ->get();

    return response()->json($tintucs);
    }
    public function danhMucCongKhai()
{
    $danhMucs = DanhMucTinTuc::where('trang_thai', 1)->get();
    return response()->json($danhMucs);
}
}
