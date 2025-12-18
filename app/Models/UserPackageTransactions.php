<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPackageTransactions extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'package_id', 'action', 'start_date', 'end_date', 'created_by_user_id', 'created_by_admin_id', 'status'];
    
    public function package()
    {
        return $this->belongsTo(Packages::class);
    }

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
