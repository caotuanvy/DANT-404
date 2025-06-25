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
            'slug' => 'nullable|string|max:225', // Thêm dòng này
        ]);

        if ($request->hasFile('hinh_anh')) {
            $file = $request->file('hinh_anh');
            $path = $file->store('Tintuc', 'public');
            $data['hinh_anh'] = $path;
        }

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
            'hinh_anh' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'slug' => 'nullable|string|max:225', // Thêm dòng này
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
        $tintuc->delete();
        return response()->json(['message' => 'Đã xóa thành công']);
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



}
