<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SlideShow;
use Illuminate\Support\Facades\Storage;
use App\Models\Slide;



class SlideShowController extends Controller
{


    public function index()
    {
        $slides = Slide::with('hinhAnh')->get();
        return response()->json($slides);
    }

    public function show($slide_id)
    {
        $anh = SlideShow::where('slide_id', $slide_id)->get();
        return response()->json($anh);
    }

   public function update(Request $request)
{
    try {
        $validated = $request->validate([
            'slide_id' => 'required|integer|exists:slide,slide_id',
            'loai_anh' => 'required|string',
            'hinh_anh' => 'required|image|max:2048',
        ]);
        $slideShow = SlideShow::where('slide_id', $request->slide_id)
            ->where('loai_anh', $request->loai_anh)
            ->first();

        if (!$slideShow) {
            return response()->json(['message' => 'Slide ảnh không tồn tại'], 404);
        }
        if ($slideShow->duong_dan && Storage::exists($slideShow->duong_dan)) {
            Storage::delete($slideShow->duong_dan);
        }
        $path = $request->file('hinh_anh')->store('slides', 'public');

        SlideShow::where('slide_id', $request->slide_id)
            ->where('loai_anh', $request->loai_anh)
            ->update(['duong_dan' => $path]);

        return response()->json(['message' => 'Cập nhật ảnh thành công']);
    } catch (\Exception $e) {
    return response()->json([
        'message' => 'Đã xảy ra lỗi khi cập nhật ảnh',
        'error' => $e->getMessage()
    ], 500);
}
}


    public function store(Request $request)
{
    $request->validate([
        'ten_slide' => 'required|string|max:255',
        'hinh_anh.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $slide = Slide::create(['ten_slide' => $request->ten_slide]);

    foreach ($request->file('hinh_anh') as $index => $image) {
        $path = $image->store('slides', 'public');
        $dieuHuong = is_array($request->dieu_huong) && isset($request->dieu_huong[$index])
            ? $request->dieu_huong[$index]
            : null;

        SlideShow::create([
            'slide_id' => $slide->slide_id,
            'loai_anh' => 'Ảnh ' . ($index + 1),
            'duong_dan' => $path,
            'dieu_huong' => $dieuHuong,
        ]);
    }

    return response()->json(['message' => 'Thêm slide thành công']);
}

public function addImageToSlide(Request $request)
{
    try {
        $validated = $request->validate([
            'slide_id' => 'required|integer|exists:slide,slide_id',
            'loai_anh' => 'required|string',
            'hinh_anh' => 'required|image|max:2048',
        ]);
        $exists = SlideShow::where('slide_id', $request->slide_id)
            ->where('loai_anh', $request->loai_anh)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ảnh với loại này đã tồn tại cho slide này'], 409);
        }
        $path = $request->file('hinh_anh')->store('slides', 'public');
        SlideShow::create([
            'slide_id' => $request->slide_id,
            'loai_anh' => $request->loai_anh,
            'duong_dan' => $path,
]);

        return response()->json(['message' => 'Thêm ảnh cho slide hiện tại thành công']);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Đã xảy ra lỗi khi thêm ảnh',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    try {
        $images = SlideShow::where('slide_id', $id)->get();
        foreach ($images as $image) {
            if ($image->duong_dan && Storage::disk('public')->exists($image->duong_dan)) {
                Storage::disk('public')->delete($image->duong_dan);
            }
        }
        SlideShow::where('slide_id', $id)->delete();
        $slide = Slide::find($id);
        if ($slide) {
            $slide->delete();
        }

        return response()->json(['message' => 'Xoá slide thành công']);
    } catch (\Exception $e) {
       return response()->json([
            'message' => 'Xảy ra lỗi khi xoá slide',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getSlideTrangChu()
{
    $slide = Slide::with('hinhAnh')->where('hien_thi', true)->first();
    return response()->json($slide);
}
public function chonSlideHienThi(Request $request)
{
    $request->validate([
        'slide_id' => 'required|exists:slide,slide_id',
    ]);
    Slide::where('hien_thi', true)->update(['hien_thi' => false]);
    Slide::where('slide_id', $request->slide_id)->update(['hien_thi' => true]);
    return response()->json(['message' => 'Đã chọn slide hiển thị trang chủ']);
}
public function rename(Request $request)
{
    $request->validate([
        'slide_id' => 'required|integer',
        'ten_slide' => 'required|string|max:255',
    ]);

    $slide = Slide::findOrFail($request->slide_id);
    $slide->ten_slide = $request->ten_slide;
    $slide->save();

    return response()->json(['message' => 'Đã cập nhật tên slide']);
}
public function deleteImage($slide_id, $loai_anh)
{
    $slideShow = SlideShow::where('slide_id', $slide_id)
        ->where('loai_anh', $loai_anh)
        ->first();

    if (!$slideShow) {
        return response()->json(['message' => 'Không tìm thấy ảnh'], 404);
    }
    $duongDan = str_replace('storage/', '', $slideShow->duong_dan);
    if (Storage::disk('public')->exists($duongDan)) {
        Storage::disk('public')->delete($duongDan);
    }
    SlideShow::where('slide_id', $slide_id)
        ->where('loai_anh', $loai_anh)
        ->delete();

    return response()->json(['message' => 'Xóa ảnh thành công'], 200);
}
public function updateLink(Request $request)
{
    $request->validate([
        'slide_id' => 'required|integer',
        'loai_anh' => 'required|string|max:255',
        'dieu_huong' => 'required|string|max:255',
    ]);

    $updated = SlideShow::where('slide_id', $request->slide_id)
        ->where('loai_anh', $request->loai_anh)
        ->update(['dieu_huong' => $request->dieu_huong]);

    return $updated
        ? response()->json(['message' => 'Cập nhật link điều hướng thành công'])
        : response()->json(['message' => 'Không tìm thấy ảnh slide'], 404);
}


}
