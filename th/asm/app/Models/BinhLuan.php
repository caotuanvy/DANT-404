<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinhLuan extends Model
{
    protected $table = 'binh_luan';
    protected $primaryKey = 'binh_luan_id';
    public $timestamps = false;

    protected $fillable = [
        'nguoi_dung_id',
        'san_pham_id',
        'tin_tuc_id',
        'noidung',
        'trang_thai',
        'ho_ten_khach',
        'ngay_binh_luan',
        'bao_cao',
        'luot_thich',
        'luot_khong_thich',
    ];

    // Tự động thêm vào JSON
    protected $appends = ['loai_binh_luan'];

    // Accessor: phân loại bình luận
    public function getLoaiBinhLuanAttribute()
    {
        if ($this->san_pham_id) return 'Sản phẩm';
        if ($this->tin_tuc_id) return 'Tin tức';
        return 'Không xác định';
    }

    // Quan hệ tới sản phẩm
    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    // Quan hệ tới tin tức
    public function tinTuc()
    {
        return $this->belongsTo(Tintuc::class, 'tin_tuc_id');
    }

    // Quan hệ tới người dùng
    public function nguoiDung()
    {        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }
}
