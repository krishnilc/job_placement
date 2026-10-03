<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'date_of_birth',
        'gender',
        'residential_address',
        'postal_address',
        'city',
        'country',
        'high_school',
        'high_school_graduation_year',
        'college_id',
        'degree',
        'major',
        'graduation_year',
        'skills',
        'bio',
        'linkedin_url',
        'facebook_url',
        'availability',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }
}
