<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlideShow extends Model
{
    protected $table = 'slide_show';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['slide_id', 'loai_anh', 'duong_dan','dieu_huong'];

    public function slide()
    {
        return $this->belongsTo(Slide::class, 'slide_id', 'slide_id');
    }
}

