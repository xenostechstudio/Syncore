<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailConfiguration extends Model
{
    protected $table = 'email_configurations';

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'is_active',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the email configuration (singleton pattern - only one record)
     */
    public static function getConfiguration(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'mailer' => 'smtp',
                'port' => 587,
                'encryption' => 'tls',
                'is_active' => false,
            ]
        );
    }

    /**
     * Check if database configuration should be used
     */
    public static function shouldUseDatabase(): bool
    {
        $config = self::first();
        return $config && $config->is_active && $config->host;
    }

    /**
     * Get a config value with fallback to env
     */
    public static function get(string $key, $default = null)
    {
        $config = self::first();
        
        if ($config && $config->is_active) {
            $dbValue = match ($key) {
                'mail.default' => $config->mailer,
                'mail.mailers.smtp.host' => $config->host,
                'mail.mailers.smtp.port' => $config->port,
                'mail.mailers.smtp.username' => $config->username,
                'mail.mailers.smtp.password' => $config->getDecryptedPassword(),
                'mail.mailers.smtp.encryption' => $config->encryption,
                'mail.from.address' => $config->from_address,
                'mail.from.name' => $config->from_name,
                default => null,
            };

            if ($dbValue !== null && $dbValue !== '') {
                return $dbValue;
            }
        }

        return config($key, $default);
    }

    /**
     * Set password with encryption
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Crypt::encryptString($value);
        } else {
            $this->attributes['password'] = null;
        }
    }

    /**
     * Get decrypted password
     */
    public function getDecryptedPassword(): ?string
    {
        if ($this->attributes['password'] ?? null) {
            try {
                return Crypt::decryptString($this->attributes['password']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Apply configuration to Laravel's mail config at runtime
     */
    public static function applyToConfig(): void
    {
        if (!self::shouldUseDatabase()) {
            return;
        }

        $config = self::first();

        config([
            'mail.default' => $config->mailer ?: config('mail.default'),
            'mail.mailers.smtp.host' => $config->host ?: config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $config->port ?: config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => $config->username ?: config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $config->getDecryptedPassword() ?: config('mail.mailers.smtp.password'),
            'mail.mailers.smtp.encryption' => $config->encryption ?: config('mail.mailers.smtp.encryption'),
            'mail.from.address' => $config->from_address ?: config('mail.from.address'),
            'mail.from.name' => $config->from_name ?: config('mail.from.name'),
        ]);
    }
}
