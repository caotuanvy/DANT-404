<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMucTinTuc;
use Illuminate\Http\Request;

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

     // Xóa danh mục tin tức
    public function destroy($id)
    {
        $danhMuc = DanhMucTinTuc::findOrFail($id);
        $danhMuc->delete();

        return response()->json([
            'message' => 'Xóa danh mục thành công'
        ]);
    }

    // Sửa danh mục tin tức
        public function update($id, Request $request)
    {
        $danhMuc = DanhMucTinTuc::findOrFail($id);

        $validated = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $danhMuc->ten_danh_muc = $validated['ten_danh_muc'];
        $danhMuc->mo_ta = $validated['mo_ta'] ?? null;

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
        ]);

        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $path = $file->store('Tintuc', 'public');
            $data['hinh_anh'] = $path;
        }

        // Thêm ngày tạo nếu bảng có trường ngay_tao
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
        'ngay_tao' => $danhMuc->ngay_tao,
        'ngay_sua' => $danhMuc->ngay_sua,
    ]);
}

}
