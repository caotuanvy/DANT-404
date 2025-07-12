<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GioHangChiTiet;
use App\Models\OrderItem;
class SanPhamBienThe extends Model
{
    protected $table = 'san_pham_bien_the';

    protected $primaryKey = 'bien_the_id';

    public $timestamps = false;

    protected $fillable = [
        'san_pham_id',
        'ten_bien_the',
        'kich_thuoc',
        'mau_sac',
        'so_luong_ton_kho',
        'gia',
        'hinh_anh',
        'trong_luong',
        'ngay_tao',
        'ngay_sua',
        'chieu_dai',
        'chieu_rong',
        'chieu_cao',
    ];

    public function sanPham()
<<<<<<< HEAD
{
    return $this->belongsTo(SanPham::class, 'san_pham_id', 'san_pham_id')
                ->withDefault();
}

    
=======
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id', 'san_pham_id');
    }
    public function gioHangChiTiet()
{
    return $this->hasMany(GioHangChiTiet::class, 'san_pham_bien_the_id');
}
public function donHangChiTiet()
    {
        return $this->hasMany(OrderItem::class, 'san_pham_bien_the_id', 'bien_the_id');
    }
>>>>>>> e58a3daaf66b8e704172773499843bab5a76066e
}
