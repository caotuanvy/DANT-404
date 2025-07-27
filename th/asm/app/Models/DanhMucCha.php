<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Thêm dòng này
use Illuminate\Database\Eloquent\Model;

class DanhMucCha extends Model
{
    use HasFactory; // Sử dụng trait này

    protected $table = 'danh_muc_cha';
    protected $primaryKey = 'id'; // Khai báo rõ ràng khóa chính là 'id'
    protected $fillable = [
        'ten_danh_muc',
        'mo_ta',
        'image', // Thêm 'image' vào fillable nếu bạn muốn lưu tên ảnh
        'trang_thai', // Thêm 'trang_thai' vào fillable nếu bạn có trường này
    ];

    // Laravel mặc định sẽ tự động quản lý created_at và updated_at.
    // Nếu bạn không muốn sử dụng chúng, hãy thêm: public $timestamps = false;

    public function danhMucSanPhams()
    {
        return $this->hasMany(Category::class, 'danh_muc_cha_id', 'id');
    }
}