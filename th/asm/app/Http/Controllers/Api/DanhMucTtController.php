<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMucTinTuc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'hinh_anh' => 'nullable|string|max:100',
        ]);

        $danhMuc->ten_danh_muc = $validated['ten_danh_muc'];
        $danhMuc->mo_ta = $validated['mo_ta'] ?? null;
        $danhMuc->hinh_anh = $validated['hinh_anh'] ?? $danhMuc->hinh_anh;
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
            // Lưu vào storage/app/public/Tintuc
            $path = $file->store('Tintuc', 'public');
            $data['hinh_anh'] = $path;
        }

        $danhMuc = DanhMucTinTuc::create($data);
        return response()->json($danhMuc, 201);


    $danhMuc->ngay_tao = now();
    $danhMuc->save();

    return response()->json([
        'message' => 'Thêm danh mục thành công',
        'data' => $danhMuc
    ], 201);
}


}
