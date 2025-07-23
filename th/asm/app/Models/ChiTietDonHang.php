<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDonHang extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_don_hang';
    protected $primaryKey = 'id'; // Khóa chính của bảng chi_tiet_don_hang

    protected $fillable = [
        'don_hang_id',
        'san_pham_bien_the_id',
        'so_luong',
        'don_gia',
    ];

    // Đổi tên cột timestamp để khớp với database của bạn
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_sua';

    // Mối quan hệ với Đơn hàng (Order)
    public function order()
    {
        // 'don_hang_id' là khóa ngoại trong chi_tiet_don_hang
        // 'id' là khóa chính trong don_hang
        return $this->belongsTo(Order::class, 'don_hang_id', 'id');
    }

    // Mối quan hệ với Sản phẩm biến thể (SanPhamBienThe)
    // ĐÃ ĐỔI TÊN HÀM TỪ sanPhamBienThe() SANG bienThe() để khớp với eager loading trong OrderController
    // ĐÃ SỬA CỘT KHÓA NGOẠI VÀ KHÓA CHÍNH cho đúng
    public function bienThe() // <-- TÊN HÀM ĐÃ ĐƯỢC SỬA
    {
        // 'san_pham_bien_the_id' là khóa ngoại trong chi_tiet_don_hang
        // 'bien_the_id' là khóa chính trong san_pham_bien_the
        return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id'); // <-- KHÓA NGOẠI VÀ KHÓA CHÍNH ĐÃ ĐƯỢC SỬA
    }
}
