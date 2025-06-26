<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'hinh_anh',       // cần có
        'trong_luong',    // cần có
        'ngay_tao',
        'ngay_sua',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id', 'san_pham_id');
    }
}
