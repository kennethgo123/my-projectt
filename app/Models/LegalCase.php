<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCase extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'case_number',
        'title',
        'description',
        'status',
        'client_id',
        'lawyer_id',
        'service_id',
        'consultation_id',
        'contract_path',
        'signature_path',
        'signed_at',
        'case_completion',
        'closed_at',
        'archived',
        'priority',
        'contract_status',
        'rejection_reason', 
        'requested_changes_details',
        'lawyer_response_required',
        'case_label',
        'last_viewed_by_client_at',
        'is_confidential',
        'is_pro_bono',
        'pro_bono_set_at',
        'pro_bono_note'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contract_signed_at' => 'datetime',
        'phases' => 'array',
        'client_tasks' => 'array',
        'court_details' => 'array',
        'deadline' => 'datetime',
        'is_confidential' => 'boolean',
        'archived' => 'boolean',
        'closed_at' => 'datetime',
        'is_pro_bono' => 'boolean',
        'pro_bono_set_at' => 'datetime'
    ];

    /**
     * Attributes that should be hidden for client users
     */
    protected $hidden = ['label'];

    /**
     * Get the label attribute - only show label to lawyers and admins
     */
    public function getLabelAttribute($value)
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }
        
        // Only return label for lawyers, law firms, and admins
        if (in_array($user->role->name, ['lawyer', 'admin', 'law_firm'])) {
            return $value;
        }
        
        // For clients and other roles, hide the label 
        return null;
    }

    /**
     * Case Statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PENDING_CLIENT_RESPONSE = 'pending_client_response';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_CONTRACT_SENT = 'contract_sent';
    const STATUS_CONTRACT_REJECTED_BY_CLIENT = 'contract_rejected_by_client';
    const STATUS_CHANGES_REQUESTED_BY_CLIENT = 'changes_requested_by_client';
    const STATUS_CONTRACT_REVISED_SENT = 'contract_revised_sent';
    const STATUS_CONTRACT_SIGNED = 'contract_signed';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CASE_REQUEST_SENT_BY_CLIENT = 'case_request_sent_by_client';

    /**
     * Contract status constants
     */
    const CONTRACT_STATUS_PENDING = 'pending';
    const CONTRACT_STATUS_SENT = 'sent';
    const CONTRACT_STATUS_SIGNED = 'signed';
    const CONTRACT_STATUS_REJECTED = 'rejected';
    const CONTRACT_STATUS_NEGOTIATING = 'negotiating';
    const CONTRACT_STATUS_REVISED_SENT = 'revised_sent';

    /**
     * Case label constants
     */
    const LABEL_HIGH_PRIORITY = 'high_priority';
    const LABEL_MEDIUM_PRIORITY = 'medium_priority';
    const LABEL_LOW_PRIORITY = 'low_priority';

    /**
     * Case label constants for new system
     */
    const CASE_LABEL_HIGH_PRIORITY = 'high_priority';
    const CASE_LABEL_MEDIUM_PRIORITY = 'medium_priority';
    const CASE_LABEL_LOW_PRIORITY = 'low_priority';

    /**
     * Get the lawyer that owns the case.
     */
    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    /**
     * Get the client that owns the case.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the legal service associated with this case.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(LegalService::class, 'service_id');
    }

    /**
     * Get the consultation that originated this case.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    /**
     * Get the phases for this case.
     */
    public function phases(): HasMany
    {
        return $this->hasMany(CasePhase::class, 'legal_case_id');
    }

    /**
     * Get the events for this case.
     */
    public function events(): HasMany
    {
        return $this->hasMany(CaseEvent::class, 'legal_case_id');
    }

    /**
     * Get the tasks for this case.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(CaseTask::class, 'legal_case_id');
    }

    /**
     * Get the documents for this case.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(CaseDocument::class, 'legal_case_id');
    }

    /**
     * Generate a unique case number
     */
    public static function generateCaseNumber(): string
    {
        $prefix = 'CASE';
        $year = date('Y');
        $lastCase = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastCase ? (int)substr($lastCase->case_number, -4) + 1 : 1;
        return sprintf("%s-%s-%04d", $prefix, $year, $sequence);
    }

    /**
     * Get the contract actions for this case.
     */
    public function contractActions(): HasMany
    {
        return $this->hasMany(ContractAction::class);
    }

    /**
     * Get the updates for this case.
     */
    public function caseUpdates(): HasMany
    {
        return $this->hasMany(CaseUpdate::class, 'legal_case_id');
    }

    /**
     * Get the invoices for this case.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'legal_case_id');
    }

    /**
     * Get the lawyer ratings for this case.
     */
    public function lawyerRatings(): HasMany
    {
        return $this->hasMany(LawyerRating::class, 'legal_case_id');
    }

    /**
     * Get all lawyers assigned to this case.
     */
    public function assignedLawyers()
    {
        return $this->belongsToMany(User::class, 'case_lawyer', 'legal_case_id', 'user_id')
            ->withPivot('role', 'notes', 'is_primary', 'assigned_by')
            ->withTimestamps();
    }

    /**
     * Get all team lawyers assigned to this case.
     * This is an alias for assignedLawyers() to maintain backward compatibility.
     */
    public function teamLawyers()
    {
        return $this->belongsToMany(User::class, 'case_lawyer', 'legal_case_id', 'user_id')
            ->withPivot('role', 'notes', 'is_primary', 'assigned_by')
            ->withTimestamps();
    }

    /**
     * Get all case lawyer assignments with additional data.
     */
    public function caseLawyers(): HasMany
    {
        return $this->hasMany(CaseLawyer::class, 'legal_case_id');
    }

    /**
     * Check if the case is closed
     */
    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /**
     * Get the case categories with descriptions.
     */
    public function caseCategories()
    {
        return $this->hasMany(CaseCategory::class, 'legal_case_id');
    }

    /**
     * Get all categories associated with this case.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'case_categories', 'legal_case_id', 'category_id')
            ->withPivot('description')
            ->withTimestamps();
    }
} 