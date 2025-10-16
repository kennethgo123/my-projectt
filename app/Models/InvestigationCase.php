<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestigationCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'investigator_id',
        'status',
        'priority',
        'investigation_notes',
        'evidence_collected',
        'findings',
        'recommendations',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'evidence_collected' => 'array',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the report associated with this investigation.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the investigator assigned to this case.
     */
    public function investigator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }

    /**
     * Get the attachments for this investigation.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(InvestigationAttachment::class);
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'pending_review' => 'Pending Review',
            'completed' => 'Completed',
            'closed' => 'Closed',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get the priority label for display.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
            default => ucfirst($this->priority)
        };
    }

    /**
     * Get the priority color class for UI display.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'text-gray-600 bg-gray-100',
            'medium' => 'text-blue-600 bg-blue-100',
            'high' => 'text-orange-600 bg-orange-100',
            'urgent' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    /**
     * Get the status color class for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'assigned' => 'text-blue-600 bg-blue-100',
            'in_progress' => 'text-yellow-600 bg-yellow-100',
            'pending_review' => 'text-purple-600 bg-purple-100',
            'completed' => 'text-green-600 bg-green-100',
            'closed' => 'text-gray-600 bg-gray-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    /**
     * Mark the investigation as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    /**
     * Mark the investigation as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /**
     * Check if the investigation is completed and locked.
     */
    public function isLocked(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter by investigator.
     */
    public function scopeByInvestigator($query, int $investigatorId)
    {
        return $query->where('investigator_id', $investigatorId);
    }
}