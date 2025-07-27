<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $table = 'thong_bao';

    protected $fillable = [
        'loai_thong_bao',
        'mo_ta',
        'tin_bao',
        'da_xem',
        'ngay_tao',
    ];

    public $timestamps = false; // vì bảng không có updated_at, created_at
}
