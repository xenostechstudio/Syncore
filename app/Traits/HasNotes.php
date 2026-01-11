<?php

namespace App\Traits;

use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * HasNotes Trait
 * 
 * Provides polymorphic note functionality for models.
 * Allows models to have associated notes with user attribution.
 * 
 * Usage:
 * ```php
 * class MyModel extends Model
 * {
 *     use HasNotes;
 * }
 * 
 * // Add a note
 * $model->addNote('This is a note', true);
 * 
 * // Get all notes
 * $notes = $model->notes;
 * ```
 * 
 * @package App\Traits
 */
trait HasNotes
{
    /**
     * Get all notes for this model.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->latest();
    }

    /**
     * Add a note to this model.
     *
     * @param string $content The note content
     * @param bool $isInternal Whether the note is internal (default: true)
     * @return Note The created note instance
     */
    public function addNote(string $content, bool $isInternal = true): Note
    {
        return $this->notes()->create([
            'user_id' => auth()->id(),
            'content' => $content,
            'is_internal' => $isInternal,
        ]);
    }

    /**
     * Check if the model has any notes.
     *
     * @return bool
     */
    public function hasNotes(): bool
    {
        return $this->notes()->exists();
    }

    /**
     * Get the count of notes for this model.
     *
     * @return int
     */
    public function notesCount(): int
    {
        return $this->notes()->count();
    }

    /**
     * Get only internal notes.
     *
     * @return MorphMany<Note>
     */
    public function internalNotes(): MorphMany
    {
        return $this->notes()->where('is_internal', true);
    }

    /**
     * Get only external/public notes.
     *
     * @return MorphMany<Note>
     */
    public function externalNotes(): MorphMany
    {
        return $this->notes()->where('is_internal', false);
    }
}
