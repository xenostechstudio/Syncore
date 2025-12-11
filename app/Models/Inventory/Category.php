<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'inventory_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }

    public function getFullPathAttribute(): string
    {
        $path = $this->name;
        $parent = $this->parent;
        
        while ($parent) {
            $path = $parent->name . ' / ' . $path;
            $parent = $parent->parent;
        }
        
        return $path;
    }
}
