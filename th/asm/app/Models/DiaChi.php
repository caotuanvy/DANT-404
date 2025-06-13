<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaChi extends Model
{
    // Tên bảng mà model này liên kết đến
    protected $table = 'dia_chi';

    // Khóa chính của bảng
    protected $primaryKey = 'id_dia_chi';

    // Laravel mặc định sẽ tự động quản lý các cột 'created_at' và 'updated_at'.
    // Vì bảng 'dia_chi' của bạn không có các cột này, chúng ta cần tắt chức năng này.
    public $timestamps = false;

    // Các thuộc tính có thể được gán hàng loạt (mass-assignable)
    protected $fillable = [
        'nguoi_dung_id',
        'dia_chi',
    ];

    /**
     * Định nghĩa mối quan hệ "thuộc về" (BelongsTo) với model User.
     * Một địa chỉ thuộc về một người dùng.
     */
    public function nguoiDung(): BelongsTo
    {
        // 'App\Models\User' là tên model mà nó liên kết tới.
        // 'nguoi_dung_id' là khóa ngoại trong bảng 'dia_chi' (bảng hiện tại).
        // 'nguoi_dung_id' là khóa chính trong bảng 'nguoi_dung' (bảng đích).
        return $this->belongsTo(User::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }
}