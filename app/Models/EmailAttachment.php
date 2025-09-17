<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmailAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_id',
        'gmail_attachment_id',
        'filename',
        'original_filename',
        'mime_type',
        'size_bytes',
        'content_id',
        'is_inline',
        'storage_path',
        'storage_disk',
        'file_hash',
        'is_downloaded',
        'downloaded_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_inline' => 'boolean',
        'is_downloaded' => 'boolean',
        'downloaded_at' => 'datetime',
    ];

    /**
     * Get the email that owns the attachment.
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSize(): string
    {
        if (!$this->size_bytes) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size_bytes;
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.1f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Get file extension.
     */
    public function getFileExtension(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * Check if attachment is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if attachment is a document.
     */
    public function isDocument(): bool
    {
        $documentTypes = [
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

        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Check if file exists in storage.
     */
    public function fileExists(): bool
    {
        return $this->storage_path && Storage::disk($this->storage_disk)->exists($this->storage_path);
    }

    /**
     * Get file URL for download.
     */
    public function getDownloadUrl(): ?string
    {
        if (!$this->fileExists()) {
            return null;
        }

        return Storage::disk($this->storage_disk)->url($this->storage_path);
    }

    /**
     * Get file contents.
     */
    public function getFileContents(): ?string
    {
        if (!$this->fileExists()) {
            return null;
        }

        return Storage::disk($this->storage_disk)->get($this->storage_path);
    }

    /**
     * Delete file from storage.
     */
    public function deleteFile(): bool
    {
        if (!$this->fileExists()) {
            return true;
        }

        return Storage::disk($this->storage_disk)->delete($this->storage_path);
    }

    /**
     * Find duplicate attachments by hash.
     */
    public static function findDuplicates(string $hash): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('file_hash', $hash)->get();
    }

    /**
     * Check if this is a duplicate based on hash and size.
     */
    public function isDuplicate(): bool
    {
        return static::where('file_hash', $this->file_hash)
                    ->where('size_bytes', $this->size_bytes)
                    ->where('id', '!=', $this->id)
                    ->exists();
    }

    /**
     * Scope: Downloaded attachments only.
     */
    public function scopeDownloaded($query)
    {
        return $query->where('is_downloaded', true);
    }

    /**
     * Scope: Inline attachments only.
     */
    public function scopeInline($query)
    {
        return $query->where('is_inline', true);
    }

    /**
     * Scope: Non-inline attachments only.
     */
    public function scopeRegular($query)
    {
        return $query->where('is_inline', false);
    }

    /**
     * Scope: Images only.
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope: Documents only.
     */
    public function scopeDocuments($query)
    {
        $documentTypes = [
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

        return $query->whereIn('mime_type', $documentTypes);
    }

    /**
     * Get attachment icon based on mime type.
     */
    public function getIcon(): string
    {
        if ($this->isImage()) {
            return 'fas fa-image';
        }

        $iconMap = [
            'application/pdf' => 'fas fa-file-pdf',
            'application/msword' => 'fas fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fas fa-file-word',
            'application/vnd.ms-excel' => 'fas fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fas fa-file-excel',
            'application/vnd.ms-powerpoint' => 'fas fa-file-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fas fa-file-powerpoint',
            'text/plain' => 'fas fa-file-alt',
            'text/csv' => 'fas fa-file-csv',
            'application/zip' => 'fas fa-file-archive',
            'application/x-rar-compressed' => 'fas fa-file-archive',
        ];

        return $iconMap[$this->mime_type] ?? 'fas fa-file';
    }
}
