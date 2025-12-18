<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [
        'user_id',
        'worker_profile_id',
        'title',
        'description',
        'starting_price',
        'status'
    ];
    use HasFactory;
    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }
    public function worker_profile()
    {
        return $this->belongsTo(WorkerProfile::class,'worker_profile_id');
    }
    public function images()
    {
        return $this->hasMany(JobImage::class,'job_id','id');
    }
    public function category()
    {
        return $this->belongsTo(JobCategories::class,'category_id');
    } 
}

