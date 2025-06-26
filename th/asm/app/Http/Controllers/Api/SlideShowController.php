<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SlideShow;
use Illuminate\Support\Facades\Storage;

class SlideShowController extends Controller
{
    public function index()
    {
        $slides = SlideShow::all();
        return response()->json($slides);
    }


    public function show($id)
    {
        $slide = SlideShow::find($id);
        if (!$slide) {
            return response()->json(['message' => 'Slide không tồn tại'], 404);
        }
        return response()->json($slide);
    }


    public function update(Request $request, $id)
    {
        $slide = SlideShow::find($id);
        if (!$slide) {
            return response()->json(['message' => 'Slide không tồn tại'], 404);
        }


        $imageFields = ['Hinh_anh_1', 'Hinh_anh_2', 'Hinh_anh_3'];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store('slides', 'public');
                if ($slide->$field) {
                    Storage::disk('public')->delete($slide->$field);
                }
                $slide->$field = $path;
            }
        }
        if ($request->filled('Ten_slide')) {
            $slide->Ten_slide = $request->input('Ten_slide');
        }

        $slide->save();

        return response()->json(['message' => 'Cập nhật slide thành công', 'slide' => $slide]);
    }
}
