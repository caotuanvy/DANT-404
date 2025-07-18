<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\SanPhamBienThe;
use App\Models\User;

class CartController extends Controller
{
    public function getCart(Request $request, $nguoiDungId = null)
    {
        if (is_null($nguoiDungId)) {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Vui lòng đăng nhập để xem giỏ hàng.'], 401);
            }
            $nguoiDungId = $user->nguoi_dung_id ?? $user->id;
        } else {
            $authenticatedUser = Auth::user();
            if ($authenticatedUser && ($authenticatedUser->nguoi_dung_id ?? $authenticatedUser->id) != $nguoiDungId && !($authenticatedUser->isAdmin ?? false)) {
                return response()->json(['message' => 'Bạn không có quyền xem giỏ hàng này.'], 403);
            }
        }

        try {
            DB::beginTransaction();

            $allCartsOfUser = Cart::where('nguoi_dung_id', $nguoiDungId)
                                  ->orderBy('gio_hang_id', 'desc')
                                  ->get();

            $mainCart = null;

            if ($allCartsOfUser->isEmpty()) {
                $mainCart = Cart::create([
                    'nguoi_dung_id' => $nguoiDungId,
                    'ngay_tao' => now(),
                    // 'tong_tien' và 'trang_thai' ĐÃ BỎ, KHÔNG LƯU VÀO DB
                ]);
            } else {
                $mainCart = $allCartsOfUser->shift();

                foreach ($allCartsOfUser as $duplicateCart) {
                    $duplicateCartItems = $duplicateCart->chiTiet()->get();

                    foreach ($duplicateCartItems as $dupItem) {
                        $existingItemInMainCart = $mainCart->chiTiet()
                            ->where('san_pham_bien_the_id', $dupItem->san_pham_bien_the_id)
                            ->first();

                        if ($existingItemInMainCart) {
                            $existingItemInMainCart->so_luong += $dupItem->so_luong;
                            $existingItemInMainCart->save();
                        } else {
                            $dupItem->gio_hang_id = $mainCart->gio_hang_id;
                            $dupItem->save();
                        }
                        $dupItem->delete();
                    }
                    $duplicateCart->delete();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi hợp nhất giỏ hàng cho người dùng $nguoiDungId: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return response()->json(['message' => 'Lỗi hệ thống khi tải giỏ hàng. Vui lòng thử lại.'], 500);
        }

        $cart = Cart::where('gio_hang_id', $mainCart->gio_hang_id)
                    ->with(['chiTiet.sanPhamBienThe.sanPham'])
                    ->first();

        if (!$cart) {
            return response()->json([], 200);
        }

        $formattedCartItems = $cart->chiTiet->map(function ($item) {
            $bienThe = $item->sanPhamBienThe;
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
                'total_item_price' => $item->so_luong * ($bienThe->gia ?? 0),
            ];
        });

        return response()->json([
            'cart_id' => $cart->gio_hang_id,
            'user_id' => $cart->nguoi_dung_id,
            'items' => $formattedCartItems,
            'total_items_count' => $cart->chiTiet->sum('so_luong'),
        ], 200);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'san_pham_bien_the_id' => 'required|integer|exists:san_pham_bien_the,bien_the_id',
            'quantity' => 'required|integer',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Vui lòng đăng nhập.'], 401);
        }

        $nguoiDungId = $user->nguoi_dung_id ?? $user->id;

        try {
            DB::beginTransaction();

            // Tìm hoặc tạo giỏ hàng cho người dùng (Không gán tong_tien và trang_thai nữa)
            $cart = Cart::firstOrCreate(
                ['nguoi_dung_id' => $nguoiDungId],
                [
                    'ngay_tao' => now(),
                    // 'tong_tien' và 'trang_thai' ĐÃ BỎ, KHÔNG LƯU VÀO DB
                ]
            );

            $bienThe = SanPhamBienThe::find($validated['san_pham_bien_the_id']);
            if (!$bienThe) {
                DB::rollBack();
                return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
            }

            $cartItem = $cart->chiTiet()
                ->where('san_pham_bien_the_id', $validated['san_pham_bien_the_id'])
                ->first();

            $quantityChange = $validated['quantity'];
            $newQuantity = ($cartItem ? $cartItem->so_luong : 0) + $quantityChange;

            if ($newQuantity < 0) { // Đảm bảo số lượng không âm
                $newQuantity = 0;
            }

            if ($quantityChange > 0 && $bienThe->so_luong_ton_kho < $newQuantity) {
                DB::rollBack();
                return response()->json(['message' => 'Số lượng tồn kho không đủ. Chỉ còn ' . $bienThe->so_luong_ton_kho . ' sản phẩm.'], 400);
            }

            if ($newQuantity === 0) {
                if ($cartItem) {
                    $cartItem->delete();
                }
                $message = 'Sản phẩm đã được xóa khỏi giỏ hàng!';
            } elseif ($cartItem) {
                $cartItem->so_luong = $newQuantity;
                $cartItem->don_gia = $bienThe->gia;
                $cartItem->ngay_sua = now();
                $cartItem->save();
                $message = 'Cập nhật số lượng sản phẩm thành công!';
            } else {
                $cartItem = CartItem::create([
                    'gio_hang_id' => $cart->gio_hang_id,
                    'san_pham_bien_the_id' => $validated['san_pham_bien_the_id'],
                    'so_luong' => $newQuantity,
                    'don_gia' => $bienThe->gia,
                    'ngay_tao' => now(),
                ]);
                $message = 'Đã thêm sản phẩm vào giỏ hàng!';
            }

            $cart->ngay_sua = now();
            $cart->save();

            DB::commit();

            $responseCartItem = null;
            if ($cartItem && $newQuantity > 0) {
                $responseCartItem = [
                    'gio_hang_chi_tiet_id' => $cartItem->gio_hang_chi_tiet_id,
                    'gio_hang_id' => $cartItem->gio_hang_id,
                    'san_pham_bien_the_id' => $cartItem->san_pham_bien_the_id,
                    'so_luong' => $cartItem->so_luong,
                    'don_gia' => $cartItem->don_gia,
                    'thanh_tien' => $cartItem->don_gia * $cartItem->so_luong,
                ];
            }

            return response()->json([
                'message' => $message,
                'cart_item' => $responseCartItem,
                'total_items_in_cart' => $cart->chiTiet()->sum('so_luong'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi thêm/cập nhật sản phẩm vào giỏ hàng: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['message' => 'Không thể thêm/cập nhật sản phẩm. Vui lòng thử lại.'], 500);
        }
    }

    public function deleteItem($userId, $sanPhamBienTheId)
    {
        try {
            DB::beginTransaction();

            $cart = Cart::where('nguoi_dung_id', $userId)->first();

            if (!$cart) {
                DB::rollBack();
                return response()->json(['message' => 'Giỏ hàng không tồn tại.'], 404);
            }

            $cartItem = $cart->chiTiet()
                             ->where('san_pham_bien_the_id', $sanPhamBienTheId)
                             ->first();

            if (!$cartItem) {
                DB::rollBack();
                return response()->json(['message' => 'Sản phẩm không có trong giỏ hàng.'], 404);
            }

            $cartItem->delete();

            $cart->ngay_sua = now();
            $cart->save();

            DB::commit();

            return response()->json(['message' => 'Sản phẩm đã được xóa khỏi giỏ hàng thành công.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa sản phẩm khỏi giỏ hàng: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['message' => 'Không thể xóa sản phẩm khỏi giỏ hàng. Vui lòng thử lại.'], 500);
        }
    }

    public function getAllCartsAndItems()
    {
        $carts = Cart::with('chiTiet')->get();
        $cartItems = CartItem::all();

        return response()->json([
            'carts' => $carts,
            'cart_items' => $cartItems
        ]);
    }
}
