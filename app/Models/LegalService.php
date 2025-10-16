<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'status',
    ];

    public function lawyers()
    {
        return $this->belongsToMany(LawyerProfile::class, 'lawyer_legal_service')
            ->withPivot('price', 'experience_years')
            ->withTimestamps();
    }

    public function lawFirms()
    {
        return $this->belongsToMany(LawFirmProfile::class, 'law_firm_legal_service')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }
    
    /**
     * Get the categories for this legal service.
     */
    public function categories()
    {
        return $this->hasMany(ServiceCategory::class, 'legal_service_id');
    }
} 