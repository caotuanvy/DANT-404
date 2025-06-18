<?php

namespace App\Models;

use App\Http\Controllers\Api\SanPhamBienTheController;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'chi_tiet_don_hang'; // Tên bảng chi tiết đơn hàng
    protected $primaryKey = 'id';
    public $timestamps = false; // Nếu không sử dụng created_at, updated_at

    protected $fillable = [
        'don_hang_id',
        'san_pham_bien_the_id',
        'so_luong',
        'don_gia',
    ];
    public function bien_the()
{
    return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id');
}


    // Quan hệ ngược với Order (đơn hàng cha)
    public function order()
    {
        return $this->belongsTo(Order::class, 'don_hang_id', 'id');
    }

    // Quan hệ với SanPhamBienThe (biến thể sản phẩm)
    public function productVariant()
    {
        return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id');
    }
}