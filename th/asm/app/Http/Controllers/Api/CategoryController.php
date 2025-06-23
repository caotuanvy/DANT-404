<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {

        $categories = Category::select('category_id', 'ten_danh_muc', 'mo_ta')->get();

        return response()->json($categories);
    }
    public function store(Request $request)
{
    $data = $request->validate([
        'ten_danh_muc' => 'required|string|max:255',
        'mo_ta' => 'nullable|string',
        'slug' => 'nullable|string|max:100',
        'ngay_tao' => 'nullable|date',
        'ngay_sua' => 'nullable|date',
    ]);

    $category = Category::create($data);

    return response()->json([
        'message' => 'Thêm danh mục thành công',
        'data' => $category
    ], 201);
}
public function destroy($id)
{
    $category = Category::findOrFail($id);
    $category->delete();

    return response()->json([
        'message' => 'Xóa danh mục thành công'
    ]);
}
public function show($id)
    {
    $category = Category::findOrFail($id);
    return response()->json($category);
    }
public function update($id, Request $request)
{
    $category = Category::findOrFail($id);

    $validated = $request->validate([
        'ten_danh_muc' => 'required|string|max:255',
        'mo_ta' => 'nullable|string',
        'slug' => 'nullable|string|max:100',
        'ngay_sua' => 'nullable|date',
    ]);

    $category->ten_danh_muc = $validated['ten_danh_muc'];
    $category->mo_ta = $validated['mo_ta'] ?? null;
    $category->slug = $validated['slug'] ?? $category->slug;
    $category->ngay_sua = now();
    $category->save();

    return response()->json([
        'message' => 'Cập nhật danh mục thành công',
        'data' => $category
    ]);
}
}