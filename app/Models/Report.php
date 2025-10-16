<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reported_type',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'reported_name',
        'service_date',
        'legal_matter_type',
        'category',
        'description',
        'supporting_documents',
        'timeline_of_events',
        'status',
        'admin_notes',
        'reviewed_at',
        'reviewed_by'
    ];

    protected $casts = [
        'supporting_documents' => 'array',
        'service_date' => 'date',
        'reviewed_at' => 'datetime'
    ];

    // Relationships
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function investigationCase()
    {
        return $this->hasOne(InvestigationCase::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    // Helper methods
    public function getCategoryLabelAttribute()
    {
        return match($this->category) {
            'professional_misconduct' => 'Professional Misconduct',
            'billing_disputes' => 'Billing Disputes',
            'communication_issues' => 'Communication Issues',
            'ethical_violations' => 'Ethical Violations',
            'competency_concerns' => 'Competency Concerns',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->category))
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending Review',
            'under_review' => 'Under Review',
            'resolved' => 'Resolved',
            'dismissed' => 'Dismissed',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }
}
