<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'is_active',
        'is_completed',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the user who created this maintenance schedule
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if maintenance is currently active based on schedule
     */
    public function isCurrentlyActive()
    {
        $now = Carbon::now();
        return $this->is_active && 
               $now->greaterThanOrEqualTo($this->start_datetime) && 
               $now->lessThanOrEqualTo($this->end_datetime);
    }

    /**
     * Check if there's any active maintenance scheduled for the system
     */
    public static function hasActiveMaintenance()
    {
        $now = Carbon::now();
        
        return static::where('is_active', true)
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now)
            ->exists();
    }

    /**
     * Get the current active maintenance schedule
     */
    public static function getCurrentActiveMaintenance()
    {
        $now = Carbon::now();
        
        return static::where('is_active', true)
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now)
            ->first();
    }

    /**
     * Scope for active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for upcoming schedules
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', Carbon::now());
    }

    /**
     * Scope for completed schedules
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }
}
