<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'job_application_id',
        'given_by',
        'given_to',
        'feedback_type',
        'rating',
        'comments',
    ];

    const TYPE_EMPLOYER_TO_STUDENT = 'employer_to_student';
    const TYPE_STUDENT_TO_EMPLOYER = 'student_to_employer';

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function givenBy()
    {
        return $this->belongsTo(User::class, 'given_by');
    }

    public function givenTo()
    {
        return $this->belongsTo(User::class, 'given_to');
    }
}
