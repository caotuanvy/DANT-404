<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlideHinhAnh extends Model
{
    protected $table = 'slide_hinh_anh';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = ['slide_id', 'duong_dan', 'dieu_huong', 'thu_tu'];

    public function slide()
    {
        return $this->belongsTo(Slide::class, 'slide_id', 'slide_id');
    }
}
