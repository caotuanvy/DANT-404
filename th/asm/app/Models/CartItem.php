<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Lấy từ CartItem.php

class CartItem extends Model
{
    use HasFactory; // Lấy từ CartItem.php

    protected $table = 'gio_hang_chi_tiet'; // Giữ nguyên tên bảng
    protected $primaryKey = 'gio_hang_chi_tiet_id'; // Giữ nguyên khóa chính
    public $timestamps = false; // Lấy từ GioHangChiTiet.php, có vẻ là thiết lập mong muốn

    protected $fillable = [
        'gio_hang_id',
        'san_pham_bien_the_id',
        'so_luong',
        'don_gia',
        'ngay_tao',
        'ngay_sua', // Lấy từ GioHangChiTiet.php
    ];

    /**
     * Mối quan hệ: Một chi tiết giỏ hàng thuộc về một giỏ hàng.
     * Lấy từ CartItem.php.
     */
    public function cart()
    {
        // Sử dụng Cart::class sau khi gộp file GioHang và Cart thành Cart
        return $this->belongsTo(Cart::class, 'gio_hang_id', 'gio_hang_id');
    }

    /**
     * Mối quan hệ: Một chi tiết giỏ hàng thuộc về một sản phẩm biến thể.
     * Lấy từ cả CartItem.php (sanPhamBienThe) và GioHangChiTiet.php (sanPham).
     * Nên chọn một tên rõ ràng hơn như sanPhamBienThe.
     */
    public function sanPhamBienThe()
    {
        return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id');
    }
}
