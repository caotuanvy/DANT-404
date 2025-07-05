<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GioHang;
use App\Models\GioHangChiTiet;
use App\Models\SanPhamBienThe;
use Illuminate\Support\Facades\Auth;

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
     public function themVaoGioHang(Request $request)
{
    $validated = $request->validate([
        'san_pham_bien_the_id' => 'required|integer|exists:san_pham_bien_the,bien_the_id',
        'quantity' => 'required|integer|min:1',
    ]);

    $user = Auth::user();
    if (!$user) {
        return response()->json(['message' => 'Vui lòng đăng nhập.'], 401);
    }
    $gioHang = GioHang::where('nguoi_dung_id', $user->nguoi_dung_id)->first();
    if (!$gioHang) {
        $gioHang = GioHang::create([
            'nguoi_dung_id' => $user->nguoi_dung_id,
            'tong_tien' => 0,
            'trang_thai' => 'dang_xu_ly',
            'ngay_tao' => now(),
        ]);
    }

    $bienThe = SanPhamBienThe::find($validated['san_pham_bien_the_id']);
    if (!$bienThe) {
         return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
    }

    $chiTietTonTai = $gioHang->chiTiet()
                             ->where('san_pham_bien_the_id', $validated['san_pham_bien_the_id'])
                             ->first();

    if ($chiTietTonTai) {
        $soLuongMoi = $chiTietTonTai->so_luong + $validated['quantity'];
        if ($bienThe->so_luong_ton_kho < $soLuongMoi) {
            return response()->json(['message' => 'Số lượng tồn kho không đủ.'], 400);
        }
        $chiTietTonTai->so_luong = $soLuongMoi;
        $chiTietTonTai->save();
    } else {
        if ($bienThe->so_luong_ton_kho < $validated['quantity']) {
            return response()->json(['message' => 'Số lượng tồn kho không đủ.'], 400);
        }
        GioHangChiTiet::create([
            'gio_hang_id' => $gioHang->gio_hang_id,
            'san_pham_bien_the_id' => $validated['san_pham_bien_the_id'],
            'so_luong' => $validated['quantity'],
            'don_gia' => $bienThe->gia,
        ]);
    }

    $totalItems = $gioHang->chiTiet()->sum('so_luong');
    return response()->json(['message' => 'Đã thêm sản phẩm vào giỏ hàng!', 'totalItems' => $totalItems], 200);
}
}
