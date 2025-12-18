<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequestFiles extends Model
{
    use HasFactory;
    protected $table = 'job_request_files';
    protected $fillable = [
        'job_request_id',
        'file_path',
    ];

    public function jobRequest()
    {
        return $this->belongsTo(JobRequests::class, 'job_request_id');
    }
}
