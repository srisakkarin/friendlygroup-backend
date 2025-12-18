<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invites extends Model
{
    use HasFactory;
      protected $fillable = ['inviter_id', 'invitee_id'];

    public function inviter()
    {
        return $this->belongsTo(Users::class, 'inviter_id');
    }

    public function invitee()
    {
        return $this->belongsTo(Users::class, 'invitee_id')->with('images');
    }
}
