<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhMucTinTuc extends Model
{
    protected $table = 'danh_muc_tin_tuc';
    protected $primaryKey = 'id_danh_muc_tin_tuc';

    public $incrementing = true;

    // Kiểu dữ liệu của khóa chính
    protected $keyType = 'int';

    // Không sử dụng timestamps mặc định (created_at, updated_at)
    public $timestamps = false;

    // Các trường có thể gán giá trị hàng loạt
    protected $fillable = [
        'ten_danh_muc',
        'mo_ta',
        'ngay_tao',
        'ngay_sua',
    ];
}
