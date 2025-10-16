<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'legal_service_id',
    ];

    /**
     * Get the legal service that owns this category.
     */
    public function legalService(): BelongsTo
    {
        return $this->belongsTo(LegalService::class, 'legal_service_id');
    }
}
