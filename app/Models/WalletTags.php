<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTags extends Model
{
    use HasFactory;
    protected $fillable = [
        'tag_name',
        'description'
    ];
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransactions::class, 'wallet_id', 'id');
    }
}
