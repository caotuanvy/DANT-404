<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioHang extends Model
{
    protected $table = 'gio_hang';
    protected $primaryKey = 'gio_hang_id';
    public $timestamps = false;

    protected $fillable = [
        'nguoi_dung_id',
        'ngay_tao',
        'ngay_sua',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }
    public function chiTiet()
{
    return $this->hasMany(GioHangChiTiet::class, 'gio_hang_id');
}
}
