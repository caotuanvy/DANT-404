<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DanhMucCha;
use Illuminate\Support\Str;

class DanhMucChaController extends Controller
{
    public function index()
    {
        $data = \App\Models\DanhMucCha::all()->map(function($cat) {
            return [
                'id' => $cat->id,
                'ten_danh_muc' => $cat->ten_danh_muc,
                'mo_ta' => $cat->mo_ta,
                'image' => $cat->image,
                'image_url' => $cat->image
                    ? url("/uploads/danhmuccha/{$cat->image}")
                    : url("/uploads/categories/placeholder.jpg"),
                'trang_thai' => $cat->trang_thai,
                'created_at' => $cat->created_at,
                'updated_at' => $cat->updated_at,
            ];
        });

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $danhMuc = \App\Models\DanhMucCha::findOrFail($id);

        $data = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $danhMuc->ten_danh_muc = $data['ten_danh_muc'];
        $danhMuc->mo_ta = $data['mo_ta'] ?? $danhMuc->mo_ta;

        // Xử lý upload ảnh mới nếu có
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/danhmuccha'), $filename);
            $danhMuc->image = $filename;
        }

        $danhMuc->save();

        return response()->json([
            'message' => 'Cập nhật danh mục cấp 2 thành công',
            'data' => $danhMuc
        ]);
    }

    public function toggleStatus($id)
    {
        $danhMuc = DanhMucCha::findOrFail($id);
        $danhMuc->trang_thai = $danhMuc->trang_thai == 1 ? 0 : 1;
        $danhMuc->save();
        return response()->json(['trang_thai' => $danhMuc->trang_thai]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $danhMuc = DanhMucCha::create($data);

        // Xử lý upload ảnh
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/danhmuccha'), $filename);
            $danhMuc->image = $filename;
            $danhMuc->save();
        }

        return response()->json([
            'message' => 'Thêm danh mục cấp 2 thành công',
            'data' => $danhMuc
        ], 201);
    }

    public function show($id)
    {
        $cat = \App\Models\DanhMucCha::find($id);
        if (!$cat) {
            return response()->json(['message' => 'Không tìm thấy danh mục!'], 404);
        }
        return response()->json([
            'id' => $cat->id,
            'ten_danh_muc' => $cat->ten_danh_muc,
            'mo_ta' => $cat->mo_ta,
            'image' => $cat->image,
            'image_url' => $cat->image
                ? url('/uploads/danhmuccha/' . $cat->image)
                : url('/uploads/categories/placeholder.jpg'),
            'trang_thai' => $cat->trang_thai,
            'created_at' => $cat->created_at,
            'updated_at' => $cat->updated_at,
        ]);
    }
}
