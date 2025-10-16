<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'law_firm_id',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'bio',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function lawFirm()
    {
        return $this->belongsTo(LawFirmProfile::class, 'law_firm_id');
    }
} 