<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GioHang;

class GioHangController extends Controller
{
    public function getByNguoiDung($id)
    {
        $gioHang = GioHang::where('nguoi_dung_id', $id)->get();

        return response()->json($gioHang);
    }
    public function layGioHangTheoNguoiDung($nguoiDungId)
    {
        $gioHang = GioHang::where('nguoi_dung_id', $nguoiDungId)
            ->with(['chiTiet.sanPham.sanPham'])
            ->first();

        if (!$gioHang) {
            return response()->json([]);
        }

        // Trong map() ở Controller:
        $sanPhams = $gioHang->chiTiet->map(function ($item) {
            $bienThe = $item->sanPham; // SanPhamBienThe
            $sanPham = $bienThe->sanPham ?? null;

            $tenSanPham = $sanPham->ten_san_pham ?? 'Không rõ';
            $tenBienThe = $bienThe->ten_bien_the ?? null;

            return [
                'id' => $bienThe->bien_the_id,
                'name' => $tenSanPham . ($tenBienThe ? ' - ' . $tenBienThe : ''),
                'price' => $bienThe->gia,
                'originalPrice' => null,
                'quantity' => $item->so_luong,
                'image' => $sanPham->hinhAnhSanPham->first()->duongdan ?? null,
                'weight' => $bienThe->trong_luong ?? '',
            ];
        });
        
        return response()->json($sanPhams);
    }
}
