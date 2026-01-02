<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    protected $fillable = [
        'name',
        'sequence',
        'color',
        'probability',
        'is_won',
        'is_lost',
        'is_active',
    ];

    protected $casts = [
        'probability' => 'decimal:2',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public static function getDefaultStages(): array
    {
        return [
            ['name' => 'New', 'sequence' => 1, 'color' => 'zinc', 'probability' => 10],
            ['name' => 'Qualified', 'sequence' => 2, 'color' => 'blue', 'probability' => 25],
            ['name' => 'Proposition', 'sequence' => 3, 'color' => 'amber', 'probability' => 50],
            ['name' => 'Negotiation', 'sequence' => 4, 'color' => 'violet', 'probability' => 75],
            ['name' => 'Won', 'sequence' => 5, 'color' => 'emerald', 'probability' => 100, 'is_won' => true],
            ['name' => 'Lost', 'sequence' => 6, 'color' => 'red', 'probability' => 0, 'is_lost' => true],
        ];
    }
}
