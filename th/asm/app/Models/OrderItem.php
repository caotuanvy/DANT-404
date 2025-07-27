<?php

namespace App\Models;

// Xóa dòng này, không nên import Controller vào Model
// use App\Http\Controllers\Api\SanPhamBienTheController;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'chi_tiet_don_hang'; // Tên bảng chi tiết đơn hàng
    protected $primaryKey = 'id'; // Khóa chính của bảng chi_tiet_don_hang
    public $timestamps = false; // Tắt timestamps nếu bảng không có created_at, updated_at

    protected $fillable = [
        'don_hang_id',
        'san_pham_bien_the_id',
        'so_luong',
        'don_gia',
        'thanh_tien', // <-- Rất quan trọng! Đảm bảo trường này có trong DB và $fillable
    ];

    /**
     * Mối quan hệ ngược với Order (đơn hàng cha).
     * Một OrderItem thuộc về một Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'don_hang_id', 'id');
    }

    /**
     * Mối quan hệ với SanPhamBienThe (biến thể sản phẩm).
     * Một OrderItem thuộc về một SanPhamBienThe.
     * Tên hàm `bien_the` được giữ lại vì nó đã được sử dụng trong các Controller.
     */
    public function bien_the()
    {
        return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id');
    }

    // Bạn có một mối quan hệ trùng lặp tên là `productVariant` trong code cũ.
    // Nếu nó cùng mục đích với `bien_the()`, hãy xóa nó đi để tránh nhầm lẫn.
    // public function productVariant()
    // {
    //     return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id');
    // }
}
