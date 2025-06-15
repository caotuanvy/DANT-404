<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tintuc;

class TintucController extends Controller
{
    // Lấy danh sách tin tức
    public function index()
    {
        $tintucs = Tintuc::with('danhMuc')->get();
        return response()->json($tintucs);
    }

    // Thêm tin tức mới
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_danh_muc_tin_tuc' => 'required|integer',
            'tieude' => 'required|string|max:255',
            'noidung' => 'required|string',
            'ngay_dang' => 'nullable|date',
        ]);
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
        ]);
        $tintuc->update($data);
        return response()->json($tintuc);
    }

    // Xóa tin tức
    public function destroy($id)
    {
        $tintuc = Tintuc::findOrFail($id);
        $tintuc->delete();
        return response()->json(['message' => 'Đã xóa thành công']);
    }
}
