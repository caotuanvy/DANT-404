<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'gio_hang';
    protected $primaryKey = 'gio_hang_id';
    public $timestamps = false; // nếu không có created_at, updated_at

    protected $fillable = [
        'nguoi_dung_id',
        'ngay_tao'
    ];
}
