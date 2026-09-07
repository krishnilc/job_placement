<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'application_status_history';

    protected $fillable = [
        'job_application_id',
        'application_status_id',
        'changed_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function applicationStatus()
    {
        return $this->belongsTo(ApplicationStatus::class);
    }
}
