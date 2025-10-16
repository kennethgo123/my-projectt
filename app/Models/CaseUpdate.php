<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseUpdate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'legal_case_id',
        // 'case_phase_id', // Removed as per new approach
        'user_id',
        'title',
        'content',
        'visibility', // 'lawyer', 'client', 'both', 'law_firm'
        'update_type', // 'phase_update', 'case_closed', etc.
        'is_client_visible'
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'is_client_visible' => 'boolean'
    ];

    /**
     * Get the legal case that owns the update.
     */
    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }

    /**
     * Get the user who created the update.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->with(['lawyerProfile', 'lawFirmLawyer', 'clientProfile', 'lawFirmProfile']);
    }

    /**
     * Get the creator's full name from their profile
     */
    public function getCreatorNameAttribute()
    {
        if (!$this->user) {
            return 'Unknown User';
        }
        
        // First try to get name from lawFirmLawyer if user is a lawyer under a firm
        if ($this->user->firm_id && $this->user->lawFirmLawyer) {
            return $this->user->lawFirmLawyer->first_name . ' ' . $this->user->lawFirmLawyer->last_name;
        }
        
        // Next, check regular lawyer profile
        if ($this->user->lawyerProfile) {
            return $this->user->lawyerProfile->first_name . ' ' . $this->user->lawyerProfile->last_name;
        }
        
        // For law firms
        if ($this->user->lawFirmProfile) {
            return $this->user->lawFirmProfile->firm_name;
        }
        
        // For clients
        if ($this->user->clientProfile) {
            return $this->user->clientProfile->first_name . ' ' . $this->user->clientProfile->last_name;
        }
        
        // Fallback to user's name or Unknown
        return $this->user->name ?? 'Unknown User';
    }
} 