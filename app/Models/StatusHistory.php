<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'subject_kind',
        'subject_name',
        'event',
        'previous_status',
        'current_status',
        'changed_by',
        'changed_by_name',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function statusLabel(?string $status): string
    {
        if ($status === null) {
            return '-';
        }

        return $this->subject_kind === 'job'
            ? match ($status) {
                '0' => 'Pending Approval',
                '1' => 'Active',
                '2' => 'Blocked',
                default => $status,
            }
        : ($status === 'pending' ? 'Pending Approval' : ucfirst($status));
    }
}
