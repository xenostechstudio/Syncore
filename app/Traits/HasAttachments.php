<?php

namespace App\Traits;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * HasAttachments Trait
 * 
 * Provides polymorphic file attachment functionality for models.
 * Handles file upload, storage, and deletion with user attribution.
 * 
 * Usage:
 * ```php
 * class MyModel extends Model
 * {
 *     use HasAttachments;
 * }
 * 
 * // Add an attachment
 * $model->addAttachment($uploadedFile, 'Description');
 * 
 * // Get all attachments
 * $attachments = $model->attachments;
 * 
 * // Remove an attachment
 * $model->removeAttachment($attachmentId);
 * ```
 * 
 * @package App\Traits
 */
trait HasAttachments
{
    /**
     * Get all attachments for this model.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Add a file attachment to this model.
     *
     * @param UploadedFile $file The uploaded file
     * @param string|null $description Optional description for the attachment
     * @return Attachment The created attachment instance
     */
    public function addAttachment(UploadedFile $file, ?string $description = null): Attachment
    {
        $path = $file->store($this->getAttachmentPath(), 'public');

        return $this->attachments()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'description' => $description,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Remove an attachment from this model.
     * Also deletes the file from storage.
     *
     * @param int $attachmentId The attachment ID to remove
     * @return bool Whether the removal was successful
     */
    public function removeAttachment(int $attachmentId): bool
    {
        $attachment = $this->attachments()->find($attachmentId);

        if ($attachment) {
            Storage::disk('public')->delete($attachment->path);
            return $attachment->delete();
        }

        return false;
    }

    /**
     * Get the storage path for attachments.
     * Override this method to customize the storage location.
     *
     * @return string The storage path
     */
    protected function getAttachmentPath(): string
    {
        $modelName = strtolower(class_basename($this));
        return "attachments/{$modelName}/{$this->id}";
    }

    /**
     * Check if the model has any attachments.
     *
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    /**
     * Get the count of attachments for this model.
     *
     * @return int
     */
    public function attachmentCount(): int
    {
        return $this->attachments()->count();
    }

    /**
     * Get attachments by mime type.
     *
     * @param string $mimeType The mime type to filter by (e.g., 'image/jpeg')
     * @return MorphMany<Attachment>
     */
    public function attachmentsByType(string $mimeType): MorphMany
    {
        return $this->attachments()->where('mime_type', 'like', $mimeType . '%');
    }

    /**
     * Get image attachments only.
     *
     * @return MorphMany<Attachment>
     */
    public function imageAttachments(): MorphMany
    {
        return $this->attachments()->where('mime_type', 'like', 'image/%');
    }

    /**
     * Get document attachments (PDF, Word, Excel, etc.).
     *
     * @return MorphMany<Attachment>
     */
    public function documentAttachments(): MorphMany
    {
        return $this->attachments()->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
