<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawyerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'law_firm_id',
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
        'min_budget',
        'max_budget',
        'pricing_description',
        'description',
        'is_optimized',
        'about',
        'education',
        'experience',
        'achievements',
        'specializations',
        'languages',
        'website',
        'linkedin',
        'photo_path',
        'offers_online_consultation',
        'offers_inhouse_consultation',
        'lat',
        'lng'
    ];

    protected $casts = [
        'min_budget' => 'decimal:2',
        'max_budget' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_optimized' => 'boolean',
        'offers_online_consultation' => 'boolean',
        'offers_inhouse_consultation' => 'boolean',
        'show_office_address' => 'boolean',
        'languages' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->belongsToMany(LegalService::class, 'lawyer_legal_service')
            ->withPivot('price', 'experience_years')
            ->withTimestamps();
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function lawFirm()
    {
        return $this->belongsTo(LawFirmProfile::class, 'law_firm_id');
    }
} 