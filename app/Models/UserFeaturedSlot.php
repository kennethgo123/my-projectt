<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFeaturedSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'subscription_id', 'feature_starts_at',
        'feature_ends_at', 'is_active', 'rotation_order'
    ];
    
    protected $casts = [
        'feature_starts_at' => 'datetime',
        'feature_ends_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
