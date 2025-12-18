<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueSharingRule extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'action_key',
        'company_percent',
        'customer_percent',
        'calculate_with',
        'updated_by',
    ];
}
