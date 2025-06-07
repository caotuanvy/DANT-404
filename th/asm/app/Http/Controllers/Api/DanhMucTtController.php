<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMucTinTuc;

class DanhMucTtController extends Controller
{
    public function show()
    {
        $danhMuc = DanhMucTinTuc::all();

        return response()->json($danhMuc);
    }
}
