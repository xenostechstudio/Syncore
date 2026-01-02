<?php

namespace App\Traits;

use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotes
{
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }

    public function addNote(string $content, bool $isInternal = true): Note
    {
        return $this->notes()->create([
            'user_id' => auth()->id(),
            'content' => $content,
            'is_internal' => $isInternal,
        ]);
    }
}
