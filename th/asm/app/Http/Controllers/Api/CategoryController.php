<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DanhMucSanPham;

class CategoryController extends Controller
{
    public function index()
    {

        $categories = Category::select('category_id', 'ten_danh_muc', 'slug')->get();

        return response()->json($categories);
    }
}
