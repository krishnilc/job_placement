<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'college_id',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
