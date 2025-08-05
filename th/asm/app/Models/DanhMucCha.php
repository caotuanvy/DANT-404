<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhMucCha extends Model
{
    use HasFactory;

    // Tên bảng trong cơ sở dữ liệu
    protected $table = 'danh_muc_cha';

    // Khóa chính
    protected $primaryKey = 'id';

    // Đảm bảo rằng Laravel không quản lý các cột created_at và updated_at
    public $timestamps = false;

    // Các trường có thể được gán giá trị hàng loạt
    protected $fillable = [
        'ten_danh_muc',
        'mo_ta',
        'image',
        'slug', // Đã thêm trường slug
        'trang_thai',
    ];

    /**
     * Lấy tất cả các danh mục con (từ bảng 'danh_muc_san_pham') thuộc về danh mục cha này.
     * Mối quan hệ: Một danh mục cha có nhiều danh mục sản phẩm (Category).
     * 'danh_muc_cha_id' là khóa ngoại trong bảng `danh_muc_san_pham`
     * 'id' là khóa chính của bảng `danh_muc_cha`
     */
    public function danhMucSanPhams()
    {
        return $this->hasMany(Category::class, 'danh_muc_cha_id', 'id');
    }
}
