<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

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
        'facebook_id',
        'slug',
        'ngay_tao',
        'ngay_sua',
        'activation_token',
        'is_active',
        'fcm_token',
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
    public function order()
    {
        return $this->hasMany(Order::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }

    /**
     * Định nghĩa mối quan hệ "có nhiều" (HasMany) với model DiaChi.
     * Một người dùng có thể có nhiều địa chỉ.
     */
    public function diaChis(): HasMany
    {
        // 'App\Models\DiaChi' là tên model liên kết.
        // 'nguoi_dung_id' là khóa ngoại trong bảng 'dia_chi' (bảng đích).
        // 'nguoi_dung_id' là khóa chính trong bảng 'nguoi_dung' (bảng hiện tại).
        return $this->hasMany(DiaChi::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }
    // app/Models/User.php

public function diaChiMacDinh()
{
    return $this->hasOne(DiaChi::class, 'nguoi_dung_id', 'nguoi_dung_id')->latestOfMany('id_dia_chi');
}
public function vouchers()
    {
        return $this->belongsToMany(GiamGia::class, 'nguoi_dung_giam_gia', 'nguoi_dung_id', 'giam_gia_id');
    }

}
