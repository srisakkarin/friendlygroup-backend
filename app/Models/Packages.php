<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packages extends Model
{
    use HasFactory;
    public function packageTransactions()
    {
        return $this->hasMany(UserPackageTransactions::class, 'package_id', 'id');
    }
}
