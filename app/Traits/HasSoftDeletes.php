<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait HasSoftDeletes
{
    use SoftDeletes;

    public function archive(): bool
    {
        return $this->delete();
    }

    public function unarchive(): bool
    {
        return $this->restore();
    }

    public function scopeArchived($query)
    {
        return $query->onlyTrashed();
    }

    public function scopeWithArchived($query)
    {
        return $query->withTrashed();
    }

    public function isArchived(): bool
    {
        return $this->trashed();
    }
}
