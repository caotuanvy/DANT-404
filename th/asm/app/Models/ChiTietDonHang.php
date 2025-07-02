<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietDonHang extends Model
{
    // Tên bảng trong cơ sở dữ liệu
    protected $table = 'chi_tiet_don_hang'; 

    // Khóa chính của bảng
    protected $primaryKey = 'id'; 

    // Laravel sẽ tự động quản lý created_at và updated_at.
    // Nếu bảng của bạn không có 2 cột này, hãy đặt là false.
    // Dựa trên SQL dump, bạn không có created_at/updated_at trực tiếp trong bảng này,
    // nên có thể bạn sẽ muốn đặt là false nếu không thêm chúng.
    // public $timestamps = false; 

    // Các cột có thể được gán giá trị hàng loạt (mass assignable)
    protected $fillable = [
        'don_hang_id', 
        'san_pham_bien_the_id', 
        'so_luong', 
        'don_gia'
    ];

    /**
     * Định nghĩa mối quan hệ với bảng DonHang.
     * Một chi tiết đơn hàng thuộc về một đơn hàng.
     */
    public function donHang(): BelongsTo
    {
        // 'don_hang_id' là khóa ngoại trong bảng 'chi_tiet_don_hang'
        // 'id' là khóa chính trong bảng 'don_hang'
        return $this->belongsTo(Order::class, 'don_hang_id', 'id');
    }

    /**
     * Định nghĩa mối quan hệ với bảng SanPhamBienThe.
     * Một chi tiết đơn hàng liên quan đến một sản phẩm biến thể.
     */
    public function sanPhamBienThe(): BelongsTo
    {
        // 'san_pham_bien_the_id' là khóa ngoại trong bảng 'chi_tiet_don_hang'
        // 'bien_the_id' là khóa chính (local key) trong bảng 'san_pham_bien_the'
        return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id');
    }
}