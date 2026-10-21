<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class JobApplication extends Model
{
     use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'employer_id',
        'applied_at',
        'application_file',
        'application_file_name',
        'resume_file',
        'resume_file_name',
        'certificates_file',
        'certificates_file_names',
        'status',
        'application_status_id',
    ];

     protected $casts = [
         'applied_at' => 'datetime',
     ];

     protected static function booted(): void
     {
         static::deleting(function (JobApplication $application) {
             $paths = $application->storedFilePaths();
             if ($paths === []) {
                 return;
             }

             foreach (['applications', 'public'] as $diskName) {
                 if (! Storage::disk($diskName)->delete($paths)) {
                     throw new RuntimeException("Unable to delete job application files from the [$diskName] disk.");
                 }
             }
         });
     }

     private function storedFilePaths(): array
     {
         $paths = [$this->application_file, $this->resume_file];
         $certificates = json_decode($this->certificates_file ?? '[]', true);

         if (is_array($certificates)) {
             $paths = array_merge($paths, $certificates);
         }

         return array_values(array_unique(array_filter($paths, function ($path) {
             return is_string($path)
                 && $path !== ''
                 && ! str_contains($path, "\0")
                 && ! str_contains($path, ':')
                 && ! preg_match('~(^|[\\\\/])\\.\\.([\\\\/]|$)~', $path)
                 && ! preg_match('~^[\\\\/]~', $path);
         })));
     }

     public function getApplicationFileLabelAttribute(): ?string
     {
         return $this->application_file_name ?: ($this->application_file ? basename($this->application_file) : null);
     }

     public function getResumeFileLabelAttribute(): ?string
     {
         return $this->resume_file_name ?: ($this->resume_file ? basename($this->resume_file) : null);
     }

     public function getCertificateFileLabelsAttribute(): array
     {
         $savedNames = $this->certificates_file_names ? json_decode($this->certificates_file_names, true) : [];
         if (!empty($savedNames)) {
             return array_values(array_filter($savedNames, fn ($name) => is_string($name) && $name !== ''));
         }

         $paths = $this->certificates_file ? json_decode($this->certificates_file, true) ?? [] : [];
         return array_values(array_filter(array_map(fn ($path) => $path ? basename($path) : null, $paths)));
     }

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

     public function applicationStatus()
     {
         return $this->belongsTo(ApplicationStatus::class);
     }

     public function latestStatusHistory()
     {
         return $this->hasOne(ApplicationStatusHistory::class)->latestOfMany();
     }

     public function statusHistories()
     {
         return $this->hasMany(ApplicationStatusHistory::class)->latest('created_at');
     }

     public function feedbacks()
     {
         return $this->hasMany(Feedback::class);
     }

     public function employerFeedback()
     {
         return $this->hasOne(Feedback::class)->where('feedback_type', Feedback::TYPE_EMPLOYER_TO_STUDENT);
     }

     public function studentFeedback()
     {
         return $this->hasOne(Feedback::class)->where('feedback_type', Feedback::TYPE_STUDENT_TO_EMPLOYER);
     }

     public function isPlaced(): bool
     {
         return $this->applicationStatus?->name === 'Placed';
     }
}
