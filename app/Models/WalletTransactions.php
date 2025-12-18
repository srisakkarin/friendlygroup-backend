<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransactions extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'transaction_type', 'amount', 'balance_after_transaction', 'wallet_tag_id'
    ];
    public function user()
    {
        return $this->belongsTo(Users::class);
    }
    public function walletTag()
    {
        return $this->belongsTo(WalletTags::class);
    }
}
