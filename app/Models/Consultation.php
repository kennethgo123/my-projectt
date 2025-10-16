<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lawyer_id',
        'status',
        'consultation_type',
        'description',
        'preferred_dates',
        'selected_date',
        'start_time',
        'end_time',
        'meeting_link',
        'documents',
        'decline_reason',
        'consultation_results',
        'consultation_document_path',
        'meeting_notes',
        'is_completed',
        'can_start_case',
        'specific_lawyer_id',
        'assign_as_entity'
    ];

    protected $casts = [
        'preferred_dates' => 'array',
        'documents' => 'array',
        'selected_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_completed' => 'boolean',
        'can_start_case' => 'boolean'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    /**
     * Relationship for a specific lawyer assigned by a law firm.
     */
    public function specificLawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'specific_lawyer_id');
    }

    public function case()
    {
        return $this->hasOne(LegalCase::class, 'consultation_id');
    }

    /**
     * Get the blocked time slot associated with this consultation.
     */
    public function blockedTimeSlot(): HasOne
    {
        return $this->hasOne(BlockedTimeSlot::class);
    }

    /**
     * Create a legal case from this consultation.
     *
     * @param array $caseData Additional case data
     * @return \App\Models\LegalCase
     */
    public function createLegalCase(array $caseData = []): \App\Models\LegalCase
    {
        // Generate a unique case number
        $caseNumber = \App\Models\LegalCase::generateCaseNumber();
        
        // Default case data
        $defaultData = [
            'client_id' => $this->client_id,
            'lawyer_id' => $this->lawyer_id,
            'consultation_id' => $this->id,
            'case_number' => $caseNumber,
            'title' => $caseData['title'] ?? 'Case from consultation #' . $this->id,
            'description' => $caseData['description'] ?? $this->description,
            'status' => 'pending',
            'contract_status' => 'pending',
            'lawyer_response_required' => true
        ];
        
        // Merge with any additional data provided
        $mergedData = array_merge($defaultData, $caseData);
        
        // Create the legal case
        $legalCase = \App\Models\LegalCase::create($mergedData);
        
        // Mark the consultation as completed if needed
        if (!$this->is_completed) {
            $this->update([
                'is_completed' => true,
                'can_start_case' => false
            ]);
        }
        
        return $legalCase;
    }
} 