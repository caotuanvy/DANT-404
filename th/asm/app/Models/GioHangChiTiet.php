<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioHangChiTiet extends Model
{
    protected $table = 'gio_hang_chi_tiet';
    protected $primaryKey = 'gio_hang_chi_tiet_id';
    public $timestamps = false;

    protected $fillable = [
        'gio_hang_id',
        'san_pham_bien_the_id',
        'so_luong',
        'don_gia',
        'ngay_tao',
        'ngay_sua',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPhamBienThe::class, 'san_pham_bien_the_id', 'bien_the_id');
    }
}
