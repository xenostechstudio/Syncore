<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $table = 'company_profile';

    protected $fillable = [
        'name',
        'legal_name',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_id',
        'logo',
        'currency',
        'timezone',
    ];

    /**
     * Get the company profile (singleton pattern - only one record)
     */
    public static function getProfile(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            ['name' => 'Syncore']
        );
    }

    /**
     * Get company name with fallback
     */
    public static function getCompanyName(): string
    {
        $profile = self::first();
        return $profile?->name ?: 'Syncore';
    }
}
