<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_case_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        // 'uploaded_by', // Removed to prevent conflict with polymorphic relation
        'uploaded_by_type',
        'uploaded_by_id',
        'is_shared'
    ];

    protected $casts = [
        'is_shared' => 'boolean'
    ];

    /**
     * Get the legal case that owns the document.
     */
    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }

    /**
     * Get the user who uploaded the document (polymorphic).
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->morphTo('uploaded_by');
    }

    // public function uploader() // Commented out as it conflicts with polymorphic setup and non-existent 'uploaded_by' column
    // {
    //     return $this->belongsTo(User::class, 'uploaded_by');
    // }
} 