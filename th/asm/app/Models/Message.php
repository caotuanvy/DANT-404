<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';
    protected $primaryKey = 'id';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
    ];

    /**
     * Mối quan hệ với conversation.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'id');
    }

    /**
     * Mối quan hệ với người gửi.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'nguoi_dung_id');
    }
}