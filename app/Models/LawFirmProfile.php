<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawFirmProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'firm_name',
        'contact_number',
        'email',
        'address',
        'office_address',
        'show_office_address',
        'google_maps_link',
        'city',
        'registration_number',
        'registration_file',
        'registration_type',
        'registration_certificate_file',
        'bir_certificate_file',
        'tax_id',
        'tax_file',
        'establishment_date',
        'min_budget',
        'max_budget',
        'is_optimized',
        'description',
        'about',
        'experience',
        'history',
        'achievements',
        'specializations',
        'languages',
        'website',
        'linkedin',
        'photo_path',
        'offers_online_consultation',
        'offers_inhouse_consultation',
        'allow_lawyer_availability',
        'lat',
        'lng'
    ];

    protected $casts = [
        'min_budget' => 'decimal:2',
        'max_budget' => 'decimal:2',
        'rating' => 'decimal:2',
        'establishment_date' => 'date',
        'is_optimized' => 'boolean',
        'offers_online_consultation' => 'boolean',
        'offers_inhouse_consultation' => 'boolean',
        'show_office_address' => 'boolean',
        'languages' => 'array',
        'allow_lawyer_availability' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->belongsToMany(LegalService::class, 'law_firm_legal_service')
            ->withTimestamps();
    }
    
    public function lawyers()
    {
        return $this->hasMany(LawyerProfile::class, 'law_firm_id');
    }
} 