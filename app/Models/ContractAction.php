<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'legal_case_id',
        'action_type',
        'actor_type',
        'actor_id',
        'details',
        'signature_path',
        'lawyer_acknowledged',
        'lawyer_acknowledged_at',
        'acknowledged_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lawyer_acknowledged' => 'boolean',
        'lawyer_acknowledged_at' => 'datetime',
    ];

    /**
     * Get the legal case associated with this action.
     */
    public function legalCase()
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }

    /**
     * Get the actor (lawyer or client) who performed this action.
     */
    public function actor()
    {
        return $this->morphTo();
    }
} 