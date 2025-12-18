<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequests extends Model
{
    use HasFactory;
    protected $table = 'job_requests';
    protected $fillable = [
        'user_id',
        'worker_profile_id',
        'job_id',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');
    }
    public function workerProfile()
    {
        return $this->belongsTo(WorkerProfile::class,'worker_profile_id');
    }
    public function job()
    {
        return $this->belongsTo(Job::class,'job_id');
    }
    public function files()
    {
        return $this->hasMany(JobRequestFiles::class, 'job_request_id');
    }

}
