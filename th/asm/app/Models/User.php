<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Tên bảng và khóa chính
    protected $table = 'nguoi_dung';
    protected $primaryKey = 'nguoi_dung_id';
    public $timestamps = false;


    protected $fillable = [
        'ho_ten',
        'email',
        'sdt',
        'mat_khau',
        'anh_dai_dien',
        'vai_tro_id',
        'trang_thai',
        'slug',
        'ngay_tao',
        'ngay_sua',
    ];


    protected $hidden = [
        'mat_khau',
    ];


    protected function casts(): array
    {
        return [
            'ngay_tao' => 'datetime',
            'ngay_sua' => 'datetime',
            'mat_khau' => 'hashed',
        ];
    }


    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    // // Ví dụ: Quan hệ với bảng vai trò (nếu có)
    // public function vaiTro()
    // {
    //     return $this->belongsTo(Role::class, 'vai_tro_id', 'vai_tro_id');
    // }

    // // Ví dụ: Quan hệ với bảng đơn hàng nếu có
    // public function donHangs()
    // {
    //     return $this->hasMany(DonHang::class, 'nguoi_dung_id', 'nguoi_dung_id');
    // }
}
