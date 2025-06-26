<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\SanPhamBienThe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;

class CartController extends Controller
{
public function index()
{
    // Lấy tất cả giỏ hàng
    $carts = \App\Models\Cart::all();

    // Lấy tất cả chi tiết giỏ hàng
    $cartItems = \App\Models\CartItem::all();

    return response()->json([
        'carts' => $carts,
        'cart_items' => $cartItems
    ]);
}
 public function add(Request $request)
    {
        $user = $request->user();
    // Nếu bảng users có khóa chính là nguoi_dung_id:
    $nguoiDungId = $user->nguoi_dung_id ?? $user->id;

    $validated = $request->validate([
        'san_pham_bien_the_id' => 'required|integer',
        'quantity' => 'required|integer|min:1'
    ]);

    $cart = \App\Models\Cart::firstOrCreate(
        ['nguoi_dung_id' => $nguoiDungId],
        ['ngay_tao' => now()]
    );

        // Tìm hoặc tạo chi tiết giỏ hàng
        $sanPhamBienThe = \App\Models\SanPhamBienThe::find($validated['san_pham_bien_the_id']);
        $gia = $sanPhamBienThe ? $sanPhamBienThe->gia : 0;

        $cartItem = \App\Models\CartItem::where('gio_hang_id', $cart->gio_hang_id)
            ->where('san_pham_bien_the_id', $validated['san_pham_bien_the_id'])
            ->first();

        if ($cartItem) {
            $cartItem->so_luong += $validated['quantity'];
            $cartItem->don_gia = $gia; // cập nhật giá nếu cần
            $cartItem->save();
        } else {
            $cartItem = \App\Models\CartItem::create([
                'gio_hang_id' => $cart->gio_hang_id,
                'san_pham_bien_the_id' => $validated['san_pham_bien_the_id'],
                'so_luong' => $validated['quantity'],
                'don_gia' => $gia,
                'ngay_tao' => now()
            ]);
        }
        $thanh_tien = $cartItem->don_gia * $cartItem->so_luong;

        return response()->json([
            'message' => 'Đã thêm vào giỏ hàng!',
            'cart_item' => [
                'gio_hang_chi_tiet_id' => $cartItem->gio_hang_chi_tiet_id,
                'gio_hang_id' => $cartItem->gio_hang_id,
                'san_pham_bien_the_id' => $cartItem->san_pham_bien_the_id,
                'so_luong' => $cartItem->so_luong,
                'don_gia' => $cartItem->don_gia,
                'thanh_tien' => $thanh_tien
            ]
        ]);
        return response()->json(['message' => 'Đã thêm vào giỏ hàng!', 'cart_item' => $cartItem]);
    }
}
