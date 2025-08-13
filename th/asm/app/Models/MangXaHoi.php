<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MangXaHoi extends Model
{
    use HasFactory;
    protected $table = 'mang_xa_hoi';
    protected $primaryKey = 'Mang_Xa_Hoi_id';
    public $timestamps = false;


    protected $fillable = [
        'Ten_mang_xa_hoi',
        'Link_mang_xa_hoi',
        'Icon',
        'is_active',
    ];


    protected $casts = [
        'is_active' => 'boolean',
    ];
}
