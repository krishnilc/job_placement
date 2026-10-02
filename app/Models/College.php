<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class College extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    /**
     * Display label used in dropdowns, e.g. "College Name (CODE)".
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->code . ')';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}
