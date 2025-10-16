<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LawFirmLawyer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'law_firm_profile_id',
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'contact_number',
        'address',
        'office_address',
        'show_office_address',
        'google_maps_link',
        'city',
        'valid_id_type',
        'valid_id_file',
        'bar_admission_type',
        'bar_admission_file',
        'status',
        'min_budget',
        'max_budget',
        'about',
        'education',
        'experience',
        'achievements',
        'languages',
        'photo_path',
        'is_optimized',
        'offers_online_consultation',
        'offers_inhouse_consultation',
        'lat', 
        'lng'
    ];

    protected $casts = [
        'min_budget' => 'decimal:2',
        'max_budget' => 'decimal:2',
        'is_optimized' => 'boolean',
        'offers_online_consultation' => 'boolean',
        'offers_inhouse_consultation' => 'boolean',
        'show_office_address' => 'boolean',
        'languages' => 'array',
    ];

    public function lawFirm()
    {
        return $this->belongsTo(LawFirmProfile::class, 'law_firm_profile_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute()
    {
        return trim(implode(' ', [
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]));
    }
    
    public function services()
    {
        return $this->belongsToMany(LegalService::class, 'law_firm_lawyer_legal_service')
            ->withPivot('price', 'experience_years')
            ->withTimestamps();
    }

    /**
     * Get the office address attribute with fallback to law firm's office address if not set
     */
    public function getOfficeAddressAttribute($value)
    {
        // If the lawyer has their own office address, use that
        if (!empty($value)) {
            return $value;
        }
        
        // Otherwise, try to get the law firm's office address
        if ($this->lawFirm && !empty($this->lawFirm->office_address)) {
            return $this->lawFirm->office_address;
        }
        
        // Fallback to empty string if nothing is set
        return '';
    }
    
    /**
     * Get the show_office_address attribute with fallback to law firm's setting if not set
     */
    public function getShowOfficeAddressAttribute($value)
    {
        // If explicitly set for this lawyer, use that
        if (!is_null($value)) {
            return (bool)$value;
        }
        
        // Otherwise, inherit from law firm
        if ($this->lawFirm) {
            return (bool)$this->lawFirm->show_office_address;
        }
        
        // Default fallback
        return false;
    }
    
    /**
     * Get the latitude attribute with fallback to law firm's latitude if not set
     */
    public function getLatAttribute($value)
    {
        // If the lawyer has their own latitude, use that
        if (!is_null($value)) {
            return $value;
        }
        
        // Otherwise, try to get the law firm's latitude
        if ($this->lawFirm && !is_null($this->lawFirm->lat)) {
            return $this->lawFirm->lat;
        }
        
        // Fallback to null if nothing is set
        return null;
    }
    
    /**
     * Get the longitude attribute with fallback to law firm's longitude if not set
     */
    public function getLngAttribute($value)
    {
        // If the lawyer has their own longitude, use that
        if (!is_null($value)) {
            return $value;
        }
        
        // Otherwise, try to get the law firm's longitude
        if ($this->lawFirm && !is_null($this->lawFirm->lng)) {
            return $this->lawFirm->lng;
        }
        
        // Fallback to null if nothing is set
        return null;
    }
    
    /**
     * Get the Google Maps link attribute with fallback to law firm's Google Maps link if not set
     */
    public function getGoogleMapsLinkAttribute($value)
    {
        // If the lawyer has their own Google Maps link, use that
        if (!empty($value)) {
            return $value;
        }
        
        // Otherwise, try to get the law firm's Google Maps link
        if ($this->lawFirm && !empty($this->lawFirm->google_maps_link)) {
            return $this->lawFirm->google_maps_link;
        }
        
        // Fallback to empty string if nothing is set
        return '';
    }
} 