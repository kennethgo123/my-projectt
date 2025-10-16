<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CasePhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_case_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'is_current',
        'is_completed',
        'order'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_completed' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Get the legal case that owns the phase.
     */
    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }

    /**
     * Get the tasks for the phase.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(CaseTask::class);
    }

    /**
     * Get the events for the phase.
     */
    public function events(): HasMany
    {
        return $this->hasMany(CaseEvent::class);
    }

    /*
     * Get the updates for the phase. (This relationship is removed as we are matching by title)
    public function updates(): HasMany
    {
        return $this->hasMany(CaseUpdate::class);
    }
    */
} 