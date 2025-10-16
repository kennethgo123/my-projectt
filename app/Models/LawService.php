<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function lawyers()
    {
        return $this->belongsToMany(LawyerProfile::class, 'lawyer_service')
            ->withTimestamps();
    }

    public function lawFirms()
    {
        return $this->belongsToMany(LawFirmProfile::class, 'law_firm_service')
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
} 