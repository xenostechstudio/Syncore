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
     * Validates MIME type and size before storing. Defaults to a
     * conservative whitelist of common doc/image types and 10 MB. Models
     * that need different limits override `getAttachmentMaxSizeBytes()`
     * and/or `getAllowedAttachmentMimeTypes()`.
     *
     * @throws \InvalidArgumentException When the file fails validation
     *         (oversized, disallowed MIME, or unreadable).
     */
    public function addAttachment(UploadedFile $file, ?string $description = null): Attachment
    {
        $this->validateAttachmentFile($file);

        $path = $file->store($this->getAttachmentPath(), 'public');

        return $this->attachments()->create([
            'filename'    => $file->getClientOriginalName(),
            'path'        => $path,
            'mime_type'   => $file->getMimeType(),
            'size'        => $file->getSize(),
            'description' => $description,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Throw if the file fails any of: readable, size cap, MIME whitelist.
     */
    protected function validateAttachmentFile(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new \InvalidArgumentException(
                "Uploaded file is not valid (upload error code: {$file->getError()})."
            );
        }

        $maxBytes = $this->getAttachmentMaxSizeBytes();
        $size = $file->getSize();
        if ($size !== false && $size > $maxBytes) {
            $maxMb = round($maxBytes / 1048576, 1);
            $gotMb = round($size / 1048576, 1);
            throw new \InvalidArgumentException(
                "Attachment exceeds the {$maxMb} MB size limit (got {$gotMb} MB)."
            );
        }

        $allowed = $this->getAllowedAttachmentMimeTypes();
        // Empty whitelist means "no MIME restriction" — explicit opt-out for
        // consumers that genuinely accept anything.
        if (! empty($allowed)) {
            $mime = $file->getMimeType();
            if (! in_array($mime, $allowed, true)) {
                throw new \InvalidArgumentException(
                    "Attachment MIME type '{$mime}' is not in the allowed list."
                );
            }
        }
    }

    /**
     * Default 10 MB cap. Override to widen or narrow per consumer.
     */
    protected function getAttachmentMaxSizeBytes(): int
    {
        return 10 * 1024 * 1024;
    }

    /**
     * Default whitelist — common document + image types. Override (return
     * `[]` for any-MIME) per consumer. This is the security-critical knob:
     * keep the default narrow to limit blast radius if upload endpoints
     * ever leak to unauthenticated callers.
     */
    protected function getAllowedAttachmentMimeTypes(): array
    {
        return [
            // Images
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
        ];
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
