<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'gio_hang_chi_tiet';
    protected $primaryKey = 'gio_hang_chi_tiet_id';
    public $timestamps = false;

    protected $fillable = [
        'gio_hang_id',
        'san_pham_bien_the_id',
        'so_luong',
        'ngay_tao'
    ];
}
