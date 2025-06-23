<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tintuc extends Model
{
    protected $table = 'tintuc';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_danh_muc_tin_tuc',
        'tieude',
        'hinh_anh',
        'noidung',
        'ngay_dang',
        'noi_bat',
        'duyet_tin_tuc',
        'slug', // Thêm trường slug
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucTinTuc::class, 'id_danh_muc_tin_tuc');
    }
}
