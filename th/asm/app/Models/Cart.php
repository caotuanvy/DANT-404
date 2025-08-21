<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Thêm HasFactory để hỗ trợ factories

class Cart extends Model
{
    use HasFactory; // Sử dụng HasFactory

    protected $table = 'gio_hang'; // Giữ nguyên tên bảng từ GioHang.php và Cart.php
    protected $primaryKey = 'gio_hang_id'; // Giữ nguyên khóa chính từ GioHang.php và Cart.php
    public $timestamps = false; // Giữ nguyên (cả hai đều là false)

    protected $fillable = [
        'nguoi_dung_id',
        'ngay_tao',
        'ngay_sua', // Lấy từ GioHang.php (chức năng cao cấp hơn)
    ];

    /**
     * Mối quan hệ: Một giỏ hàng thuộc về một người dùng.
     * Lấy từ GioHang.php.
     */
    public function nguoiDung()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }

    /**
     * Mối quan hệ: Một giỏ hàng có nhiều chi tiết giỏ hàng.
     * Lấy từ GioHang.php, đổi tên class chiTiet sang CartItem sau khi gộp.
     */
    public function chiTiet()
    {
        return $this->hasMany(CartItem::class, 'gio_hang_id', 'gio_hang_id'); // Sử dụng CartItem::class sau khi gộp
    }
    public function cartItems()
{
    // Sửa 'cart_id' thành tên cột đúng trong CSDL của bạn
    return $this->hasMany(CartItem::class, 'gio_hang_id', 'gio_hang_id');
}
}
