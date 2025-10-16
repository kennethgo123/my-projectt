<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'legal_services';

    protected $fillable = [
        'name',
        'description',
        'category',
        'status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_services')
            ->withPivot('price', 'experience_years')
            ->withTimestamps();
    }
} 