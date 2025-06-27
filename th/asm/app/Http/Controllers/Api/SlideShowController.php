<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slide;
use App\Models\SlideHinhAnh;
use Illuminate\Support\Facades\Storage;

class SlideShowController extends Controller
{
    public function index()
    {
        $slides = Slide::with('hinhAnh')->get();
        return response()->json($slides);
    }

    public function show($slide_id)
    {
        $images = SlideHinhAnh::where('slide_id', $slide_id)->orderBy('thu_tu')->get();
        return response()->json($images);
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

            SlideHinhAnh::create([
                'slide_id' => $slide->slide_id,
                'duong_dan' => $path,
                'dieu_huong' => $dieuHuong,
                'thu_tu' => $index + 1,
            ]);
        }

        return response()->json(['message' => 'Thêm slide thành công']);
    }

    public function addImageToSlide(Request $request)
    {
        $request->validate([
            'slide_id' => 'required|exists:slide,slide_id',
            'hinh_anh' => 'required|image|max:3048',
            'dieu_huong' => 'nullable|string|max:255',
        ]);

        $path = $request->file('hinh_anh')->store('slides', 'public');

        SlideHinhAnh::create([
            'slide_id' => $request->slide_id,
            'duong_dan' => $path,
            'dieu_huong' => $request->dieu_huong,
            'thu_tu' => SlideHinhAnh::where('slide_id', $request->slide_id)->max('thu_tu') + 1,
        ]);

        return response()->json(['message' => 'Thêm ảnh cho slide thành công']);
    }

    public function updateImage(Request $request, $id)
{
    $request->validate([
        'hinh_anh' => 'nullable|image|max:2048',
        'dieu_huong' => 'nullable|string|max:255',
    ]);

    $image = SlideHinhAnh::findOrFail($id);

    // Cập nhật ảnh mới nếu có
    if ($request->hasFile('hinh_anh')) {
        if ($image->duong_dan && Storage::disk('public')->exists($image->duong_dan)) {
            Storage::disk('public')->delete($image->duong_dan);
        }

        $path = $request->file('hinh_anh')->store('slides', 'public');
        $image->duong_dan = $path;
    }

    // Cập nhật điều hướng nếu có
    if ($request->filled('dieu_huong')) {
        $image->dieu_huong = $request->dieu_huong;
    }

    $image->save();

    return response()->json(['message' => 'Cập nhật thành công']);
}

        public function deleteImage($id)
        {
            $image = SlideHinhAnh::findOrFail($id);

            if ($image->duong_dan && Storage::disk('public')->exists($image->duong_dan)) {
                Storage::disk('public')->delete($image->duong_dan);
            }

            $image->delete();

            return response()->json(['message' => 'Xóa ảnh thành công']);
        }

    public function destroy($slide_id)
    {
        $images = SlideHinhAnh::where('slide_id', $slide_id)->get();

        foreach ($images as $image) {
            if ($image->duong_dan && Storage::disk('public')->exists($image->duong_dan)) {
                Storage::disk('public')->delete($image->duong_dan);
            }
        }

        SlideHinhAnh::where('slide_id', $slide_id)->delete();
        Slide::where('slide_id', $slide_id)->delete();

        return response()->json(['message' => 'Xoá slide thành công']);
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

    public function updateLink(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:slide_hinh_anh,id',
            'dieu_huong' => 'required|string|max:255',
        ]);

        $updated = SlideHinhAnh::where('id', $request->id)
            ->update(['dieu_huong' => $request->dieu_huong]);

        return $updated
            ? response()->json(['message' => 'Cập nhật link điều hướng thành công'])
            : response()->json(['message' => 'Không tìm thấy ảnh slide'], 404);
    }
}
