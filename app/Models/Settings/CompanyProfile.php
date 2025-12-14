<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $table = 'company_profile';

    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'company_city',
        'company_country',
        'company_website',
        'tax_id',
        'logo_path',
    ];

    /**
     * Get the company profile (singleton pattern - only one record)
     */
    public static function getProfile(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            ['company_name' => 'Syncore']
        );
    }

    /**
     * Get company name with fallback
     */
    public static function getCompanyName(): string
    {
        $profile = self::first();
        return $profile?->company_name ?: 'Syncore';
    }
}
