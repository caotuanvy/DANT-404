<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Introduce extends Model
{
    protected $table = 'trang_tinh';
    protected $primaryKey = 'Trang_tinh_id';
    public $timestamps = false;

    protected $fillable = ['Ten_trang', 'Tieu_de_trang', 'Noi_dung_trang'];
}
