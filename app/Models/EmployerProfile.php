<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'company_name',
        'company_address',
        'website_url',
        'company_description',
        'postal_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function getAttribute($key)
    {
        $organizationFields = [
            'company_name' => 'name', 'company_address' => 'address',
            'website_url' => 'website_url', 'company_description' => 'description',
            'postal_address' => 'postal_address',
            'linkedin_url' => 'linkedin_url', 'facebook_url' => 'facebook_url',
        ];
        if (isset($organizationFields[$key]) && $this->organization_id) {
            return $this->organization?->getAttribute($organizationFields[$key]);
        }

        return parent::getAttribute($key);
    }
}
