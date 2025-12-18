<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftHistory extends Model
{
    use HasFactory;
    protected $fillable = ['sender_id', 'recipient_id', 'gift_id', 'amount', 'sent_at'];
    public function gift()
    {
        return $this->belongsTo(Gifts::class, 'gift_id');
    }
    public function sender()
    {
        return $this->belongsTo(Users::class, 'sender_id');
    }
    public function recipient()
    {
        return $this->belongsTo(Users::class, 'recipient_id');
    }
}
