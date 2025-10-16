<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawFirmRating extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'law_firm_id',
        'legal_case_id',
        'rating',
        'feedback',
        'is_visible',
        'rated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'is_visible' => 'boolean',
        'rated_at' => 'datetime',
    ];

    /**
     * Get the client who submitted the rating.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the law firm that was rated.
     */
    public function lawFirm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'law_firm_id');
    }

    /**
     * Get the legal case associated with this rating.
     */
    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }
}
