<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
     use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'employer_id',
        'applied_at',
        'application_file',
        'resume_file',
        'certificates_file',
    ];

     protected $casts = [
         'applied_at' => 'datetime',
     ];

     public function job()
     {
         return $this->belongsTo(Job::class);
         
     }

      public function user()
     {
         return $this->belongsTo(User::class);
         
     }

     public function employer()
     {
         return $this->belongsTo(User::class, 'employer_id');
     }
}
