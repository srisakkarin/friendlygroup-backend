<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCategories extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'parent_id', 'order'];
    
    public function parent()
    {
        return $this->belongsTo(JobCategories::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(JobCategories::class, 'id', 'parent_id');
    }
    public function jobs()
    {
        return $this->hasMany(Job::class, 'id', 'category_id');
    }

}
