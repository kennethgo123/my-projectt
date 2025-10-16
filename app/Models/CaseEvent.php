<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_case_id',
        'title',
        'description',
        'start_datetime',
        'event_type',
        'location',
        'created_by',
        'is_completed',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the legal case that owns the event.
     */
    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 