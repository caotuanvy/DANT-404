<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tintuc extends Model
{
    protected $table = 'tintuc';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'trang_thai',
        'id_danh_muc_tin_tuc',
        'tieude',
        'hinh_anh',
        'noidung',
        'ngay_dang',
        'duyet_tin_tuc',
        'noi_bat',
        'slug',
        'luot_like',
        'luot_xem',
        'tieu_de_seo',
        'mo_ta_seo',
        'tu_khoa_seo',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucTinTuc::class, 'id_danh_muc_tin_tuc');
    }
}
