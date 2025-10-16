<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InvestigationAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'investigation_case_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'description',
        'attachment_type',
    ];

    /**
     * Get the investigation case this attachment belongs to.
     */
    public function investigationCase(): BelongsTo
    {
        return $this->belongsTo(InvestigationCase::class);
    }

    /**
     * Get the user who uploaded this attachment.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the attachment type label for display.
     */
    public function getAttachmentTypeLabelAttribute(): string
    {
        return match($this->attachment_type) {
            'evidence' => 'Evidence',
            'document' => 'Document',
            'image' => 'Image',
            'other' => 'Other',
            default => ucfirst($this->attachment_type)
        };
    }

    /**
     * Get the attachment type color class for UI display.
     */
    public function getAttachmentTypeColorAttribute(): string
    {
        return match($this->attachment_type) {
            'evidence' => 'text-red-600 bg-red-100',
            'document' => 'text-blue-600 bg-blue-100',
            'image' => 'text-green-600 bg-green-100',
            'other' => 'text-gray-600 bg-gray-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the download URL for this attachment.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('investigation.attachment.download', $this->id);
    }

    /**
     * Delete the physical file when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            if (Storage::disk('local')->exists($attachment->file_path)) {
                Storage::disk('local')->delete($attachment->file_path);
            }
        });
    }
}