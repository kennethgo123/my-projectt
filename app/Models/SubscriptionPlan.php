<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'monthly_price', 'annual_price', 'features', 'for_role'
    ];
    
    protected $casts = [
        'features' => 'array',
        'monthly_price' => 'float',
        'annual_price' => 'float',
    ];
    
    /**
     * Ensure features is always an array
     */
    public function getFeaturesAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value) ?: [];
        }
        
        return $value;
    }
    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
