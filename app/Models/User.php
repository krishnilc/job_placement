<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
        'mobile',
        'email_2',
        'mobile_2',
        'designation',
    ];

    /**
     * Profile attributes that live on the related profile tables.
     * Mapped to their relationship name for transparent access.
     *
     * @var array<string, string>
     */
    protected static $profileAttributeMap = [
        // student_profiles
        'student_id' => 'studentProfile',
        'date_of_birth' => 'studentProfile',
        'gender' => 'studentProfile',
        'residential_address' => 'studentProfile',
        'postal_address' => 'studentProfile',
        'city' => 'studentProfile',
        'country' => 'studentProfile',
        'high_school' => 'studentProfile',
        'high_school_graduation_year' => 'studentProfile',
        'college_id' => 'studentProfile',
        'degree' => 'studentProfile',
        'major' => 'studentProfile',
        'graduation_year' => 'studentProfile',
        'skills' => 'studentProfile',
        'bio' => 'studentProfile',
        'linkedin_url' => 'studentProfile',
        'facebook_url' => 'studentProfile',
        'availability' => 'studentProfile',
        // employer_profiles
        'company_name' => 'employerProfile',
        'company_address' => 'employerProfile',
        'website_url' => 'employerProfile',
        'company_description' => 'employerProfile',
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
            'email_verification_required' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function employerProfile()
    {
        return $this->hasOne(EmployerProfile::class);
    }

    public function organizationRequest()
    {
        return $this->hasOne(OrganizationRequest::class);
    }

    public function college()
    {
        return $this->hasOneThrough(College::class, StudentProfile::class, 'user_id', 'id', 'id', 'college_id');
    }

    /**
     * Transparently read profile attributes from the related profile model.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($value === null && isset(static::$profileAttributeMap[$key])) {
            $relation = static::$profileAttributeMap[$key];
            if ($this->role === 'employer' && in_array($key, ['postal_address', 'linkedin_url', 'facebook_url'], true)) {
                $relation = 'employerProfile';
            }
            // Only resolve via relationship when the column truly no longer exists on users
            if (!array_key_exists($key, $this->attributes)) {
                $related = $this->getRelationValue($relation);
                return $related ? $related->getAttribute($key) : null;
            }
        }

        return $value;
    }

    /**
     * Transparently write profile attributes to the related profile model.
     */
    public function setAttribute($key, $value)
    {
        if (isset(static::$profileAttributeMap[$key]) && !$this->hasUserColumn($key)) {
            $relation = static::$profileAttributeMap[$key];
            $related = $this->getRelationValue($relation);

            if (!$related) {
                $class = $relation === 'studentProfile' ? StudentProfile::class : EmployerProfile::class;
                $related = new $class();
                $related->user_id = $this->id;
                $this->setRelation($relation, $related);
            }

            $related->setAttribute($key, $value);
            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Persist any pending profile relation alongside the user.
     */
    public function save(array $options = [])
    {
        if ($this->exists && $this->email_verification_required && $this->isDirty('email')) {
            $this->email_verified_at = null;
        }

        $saved = parent::save($options);

        if ($saved) {
            foreach (['studentProfile', 'employerProfile'] as $relation) {
                if ($this->relationLoaded($relation) && ($related = $this->getRelation($relation))) {
                    if ($related->isDirty()) {
                        $related->user_id = $this->id;
                        $related->save();
                    }
                }
            }
        }

        return $saved;
    }

    public function needsEmailVerification(): bool
    {
        return $this->email_verification_required && !$this->hasVerifiedEmail();
    }

    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'management'], true);
    }

    public function isReadOnlyManagement(): bool
    {
        return $this->role === 'management';
    }

    /**
     * Whether the given attribute is still a real column on the users table.
     */
    protected function hasUserColumn(string $key): bool
    {
        return array_key_exists($key, $this->attributes)
            || in_array($key, $this->fillable, true);
    }
}
