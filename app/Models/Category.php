<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'slug'];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get all legal cases that belong to this category.
     */
    public function legalCases()
    {
        return $this->belongsToMany(LegalCase::class, 'case_categories', 'category_id', 'legal_case_id')
            ->withPivot('description')
            ->withTimestamps();
    }
}
