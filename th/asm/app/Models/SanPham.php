<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanPham extends Model
{
    protected $table = 'san_pham';
    protected $primaryKey = 'san_pham_id';
    public $timestamps = false;

    protected $fillable = [
        'ten_san_pham', 'gia', 'ten_danh_muc_id', "mo_ta","noi_bat","khuyen_mai", 'slug',
    ];

   public function danhMuc()
{
    return $this->belongsTo(Category::class, 'ten_danh_muc_id', 'category_id');
}
 public function hinhAnhSanPham()
    {
        return $this->hasMany(HinhAnhSanPham::class, 'san_pham_id', 'san_pham_id');
    }
    public function bienThe()
{
    return $this->hasMany(SanPhamBienThe::class, 'san_pham_id', 'san_pham_id');
}

}

