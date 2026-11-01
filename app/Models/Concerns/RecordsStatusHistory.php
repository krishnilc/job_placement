<?php

namespace App\Models\Concerns;

use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait RecordsStatusHistory
{
    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'subject')
            ->orderByDesc('created_at')->orderByDesc('id');
    }

    public function save(array $options = [])
    {
        if (($this instanceof User && $this->role !== null && ! in_array($this->role, ['student', 'employer'], true))
            || ($this->exists && ! $this->isDirty('status'))) {
            return parent::save($options);
        }

        return $this->getConnection()->transaction(function () use ($options) {
            $creating = ! $this->exists;
            // Read the persisted status under a lock; another editor may have changed it since loading.
            $previous = $creating ? null : (string) $this->newQuery()
                ->whereKey($this->getKey())->lockForUpdate()->firstOrFail()->status;
            $saved = parent::save($options);
            if (! $saved) {
                return false;
            }

            // Read back database defaults and normalize integer job statuses to the audit string format.
            $persisted = $this->newQuery()->whereKey($this->getKey())->firstOrFail();
            $current = (string) $persisted->status;
            if ($creating || $previous !== $current) {
                $actor = Auth::user();
                $this->statusHistories()->create([
                    'subject_kind' => $this instanceof User ? $persisted->role : 'job',
                    'subject_name' => $this instanceof User ? $persisted->name : $persisted->title,
                    'event' => $creating ? 'created' : 'changed',
                    'previous_status' => $previous,
                    'current_status' => $current,
                    'changed_by' => $actor?->getAuthIdentifier(),
                    'changed_by_name' => $actor?->name,
                    'created_at' => now(),
                ]);
            }

            return true;
        });
    }
}
