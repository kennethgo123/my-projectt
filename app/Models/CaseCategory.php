<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_case_id',
        'category_id',
        'description',
    ];

    /**
     * Get the case that owns this case-category relationship.
     */
    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }

    /**
     * Get the category associated with this case-category relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
