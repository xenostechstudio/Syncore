<?php

namespace App\Models\Settings;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CompanyProfile extends Model
{
    use LogsActivity;

    protected array $logActions = ['created', 'updated'];

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

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('company_profile');
            Cache::forget('company_name');
        });
    }

    /**
     * Get the company profile (singleton pattern - only one record)
     */
    public static function getProfile(): self
    {
        return Cache::remember('company_profile', 3600, function () {
            return self::firstOrCreate(
                ['id' => 1],
                ['name' => 'Syncore']
            );
        });
    }

    /**
     * Get company name with fallback (cached)
     */
    public static function getCompanyName(): string
    {
        return Cache::remember('company_name', 3600, function () {
            $profile = self::first();
            return $profile?->name ?: 'Syncore';
        });
    }
}
