<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class GiamGia extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     *
     * @var string
     */
    protected $table = 'giam_gia';

    /**
     * Khóa chính của bảng.
     *
     * @var string
     */
    protected $primaryKey = 'giam_gia_id';

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'ma_giam_gia',
        'ten_chuong_trinh',
        'loai_giam_gia',
        'gia_tri',
        'so_luong',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'gia_tri_don_hang_toi_thieu',
        'gia_tri_giam_toi_da',
        'trang_thai',
        'ap_dung_cho',
        'so_luong_da_dung' // Thêm để có thể cập nhật
    ];

    /**
     * Định dạng các thuộc tính ngày tháng.
     *
     * @var array
     */
    protected $casts = [
        'ngay_bat_dau' => 'datetime',
        'ngay_ket_thuc' => 'datetime',
        'trang_thai' => 'boolean',
    ];

    /**
     * Mối quan hệ nhiều-nhiều với người dùng.
     * Một mã giảm giá có thể được gửi cho nhiều người dùng.
     */
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'nguoi_dung_giam_gia', 'giam_gia_id', 'nguoi_dung_id')
            ->withPivot('da_su_dung', 'created_at', 'updated_at');
    }
}
