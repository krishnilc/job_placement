<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'email', 'website_url', 'description',
        'linkedin_url', 'facebook_url',
    ];

    public static function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
    }

    public static function nameKey(string $name): string
    {
        return hash('sha256', static::normalizeName($name));
    }

    protected static function booted(): void
    {
        static::saving(function (Organization $organization) {
            $organization->name = trim(preg_replace('/\s+/u', ' ', $organization->name));
            $organization->name_key = static::nameKey($organization->name);
        });
    }

    public function employerProfiles(): HasMany
    {
        return $this->hasMany(EmployerProfile::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
