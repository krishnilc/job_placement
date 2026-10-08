<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    public const REQUIRED_COMPLETION_FIELDS = [
        'date_of_birth',
        'gender',
        'marital_status',
        'residential_address',
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
        'availability',
    ];

    protected $fillable = [
        'user_id',
        'student_id',
        'date_of_birth',
        'gender',
        'marital_status',
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

    public function isComplete(): bool
    {
        foreach (self::REQUIRED_COMPLETION_FIELDS as $field) {
            if (blank($this->getAttribute($field))) {
                return false;
            }
        }

        return true;
    }
}
