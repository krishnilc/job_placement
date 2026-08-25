<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'student_id',
        'mobile',
        'email_2',
        'mobile_2',
        'designation',
        'company_name',
        'company_address',
        'website_url',
        'company_description',
        'date_of_birth',
        'gender',
        'address',
        'residential_address',
        'postal_address',
        'city',
        'country',
        'high_school',
        'high_school_graduation_year',
        'university',
        'degree',
        'major',
        'graduation_year',
        'skills',
        'bio',
        'linkedin_url',
        'facebook_url',
        'availability',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
