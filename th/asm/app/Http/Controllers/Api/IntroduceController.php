<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Introduce;
use Illuminate\Http\Request;


class IntroduceController extends Controller
{
    public function show($slug)
{
    $page = Introduce::where('Ten_trang', $slug)->first();

    if (!$page) {
        return response()->json(['message' => 'Không tìm thấy trang'], 404);
    }

    return response()->json($page);
}

public function index()
    {
        $pages = Introduce::all();
        return response()->json($pages);
    }
    public function update(Request $request)
{
    $page = Introduce::where('Ten_trang', $request->Ten_trang)->first();

    if (!$page) {
        return response()->json(['message' => 'Không tìm thấy trang'], 404);
    }

    $page->Tieu_de_trang = $request->Tieu_de_trang;
    $page->Noi_dung_trang = $request->Noi_dung_trang;
    $page->save();

    return response()->json(['message' => 'Cập nhật thành công']);
}
public function updateMeta(Request $request, $id)
{
    $request->validate([
        'Tieu_de_trang' => 'required|string',
        'Ten_trang' => 'required|string|alpha_dash|unique:trang_tinh,Ten_trang,' . $id . ',Trang_tinh_id',
    ]);

    $page = Introduce::find($id);
    if (!$page) {
        return response()->json(['message' => 'Không tìm thấy trang'], 404);
    }

    $page->Tieu_de_trang = $request->Tieu_de_trang;
    $page->Ten_trang = $request->Ten_trang;
    $page->save();

    return response()->json(['message' => 'Cập nhật tiêu đề & slug thành công']);
}

public function store(Request $request)
{
    $request->validate([
        'Tieu_de_trang' => 'required|string|max:255',
        'Ten_trang' => 'required|string|alpha_dash|unique:trang_tinh,Ten_trang'
    ]);

    $page = new Introduce();
    $page->Tieu_de_trang = $request->Tieu_de_trang;
    $page->Ten_trang = $request->Ten_trang;
    $page->Noi_dung_trang = '';
    $page->save();

    return response()->json(['message' => 'Tạo trang thành công']);
}
public function destroy($id)
{
    $page = Introduce::find($id);
    if (!$page) {
        return response()->json(['message' => 'Không tìm thấy trang'], 404);
    }

    $page->delete();

    return response()->json(['message' => 'Xóa trang thành công']);
}
}
