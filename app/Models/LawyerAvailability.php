<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LawyerAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'has_lunch_break',
        'lunch_start_time',
        'lunch_end_time',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'has_lunch_break' => 'boolean',
    ];

    /**
     * Get the lawyer that owns the availability.
     */
    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get all available time slots for a specific lawyer and day of week
     * excluding lunch breaks and blocked time slots
     * 
     * @param int $lawyerId
     * @param string $dayOfWeek
     * @param Carbon|null $date
     * @return array
     */
    public static function getAvailableTimeSlots($lawyerId, $dayOfWeek, Carbon $date = null)
    {
        $availabilities = self::where('user_id', $lawyerId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->orderBy('start_time')
            ->get();

        $timeSlots = [];
        
        foreach ($availabilities as $availability) {
            $slots = self::generateTimeSlotsForAvailability($availability, $date);
            $timeSlots = array_merge($timeSlots, $slots);
        }

        return $timeSlots;
    }

    /**
     * Generate time slots for a specific availability, excluding lunch breaks and blocked slots
     * 
     * @param LawyerAvailability $availability
     * @param Carbon|null $date
     * @return array
     */
    private static function generateTimeSlotsForAvailability($availability, Carbon $date = null)
    {
        $slots = [];
        $slotDuration = 60; // 1 hour slots
        
        $startTime = Carbon::createFromFormat('H:i:s', $availability->start_time);
        $endTime = Carbon::createFromFormat('H:i:s', $availability->end_time);
        
        // If date is provided, use it for checking blocked slots
        $checkDate = $date ?: Carbon::today();
        
        $currentSlot = $startTime->copy();
        
        while ($currentSlot->copy()->addMinutes($slotDuration)->lte($endTime)) {
            $slotEnd = $currentSlot->copy()->addMinutes($slotDuration);
            
            // Skip if this slot conflicts with lunch break
            if ($availability->has_lunch_break && 
                $availability->lunch_start_time && 
                $availability->lunch_end_time) {
                
                $lunchStart = Carbon::createFromFormat('H:i:s', $availability->lunch_start_time);
                $lunchEnd = Carbon::createFromFormat('H:i:s', $availability->lunch_end_time);
                
                // Check if slot overlaps with lunch break
                if (self::timeSlotsOverlap($currentSlot, $slotEnd, $lunchStart, $lunchEnd)) {
                    $currentSlot->addMinutes($slotDuration);
                    continue;
                }
            }
            
            // Check if this slot is blocked
            $slotDateTime = $checkDate->copy()->setTime($currentSlot->hour, $currentSlot->minute);
            $slotEndDateTime = $checkDate->copy()->setTime($slotEnd->hour, $slotEnd->minute);
            
            if (!BlockedTimeSlot::hasConflict($availability->user_id, $slotDateTime, $slotEndDateTime)) {
                $slots[] = [
                    'time' => $currentSlot->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'datetime' => $slotDateTime->format('Y-m-d H:i:s'),
                    'end_datetime' => $slotEndDateTime->format('Y-m-d H:i:s'),
                    'display' => $currentSlot->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                ];
            }
            
            $currentSlot->addMinutes($slotDuration);
        }
        
        return $slots;
    }

    /**
     * Check if two time slots overlap
     * 
     * @param Carbon $start1
     * @param Carbon $end1
     * @param Carbon $start2
     * @param Carbon $end2
     * @return bool
     */
    private static function timeSlotsOverlap(Carbon $start1, Carbon $end1, Carbon $start2, Carbon $end2)
    {
        return $start1->lt($end2) && $end1->gt($start2);
    }
    
    /**
     * Get all days with available time slots for a specific lawyer
     * 
     * @param int $lawyerId
     * @return array
     */
    public static function getAvailableDays($lawyerId)
    {
        return self::where('user_id', $lawyerId)
            ->where('is_available', true)
            ->select('day_of_week')
            ->distinct()
            ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->pluck('day_of_week')
            ->toArray();
    }

    /**
     * Check if a lawyer has availability on a specific date
     * 
     * @param int $lawyerId
     * @param Carbon $date
     * @return bool
     */
    public static function hasAvailabilityOnDate($lawyerId, Carbon $date)
    {
        $dayOfWeek = $date->format('l'); // Get day name (Monday, Tuesday, etc.)
        
        return self::where('user_id', $lawyerId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->exists();
    }
    
    /**
     * Get available time slots for a specific date
     * 
     * @param int $lawyerId
     * @param Carbon $date
     * @return array
     */
    public static function getAvailableTimeSlotsForDate($lawyerId, Carbon $date)
    {
        $dayOfWeek = $date->format('l'); // Get day name (Monday, Tuesday, etc.)
        
        return self::getAvailableTimeSlots($lawyerId, $dayOfWeek, $date);
    }
    
    /**
     * Get dates in a given month that have availability
     * 
     * @param int $lawyerId
     * @param Carbon $month First day of the month
     * @return array Array of dates with availability
     */
    public static function getAvailableDatesInMonth($lawyerId, Carbon $month)
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();
        $availableDates = [];
        
        // Get all available days of week for the lawyer
        $availableDaysOfWeek = self::where('user_id', $lawyerId)
            ->where('is_available', true)
            ->pluck('day_of_week')
            ->unique()
            ->toArray();
            
        if (empty($availableDaysOfWeek)) {
            return [];
        }
        
        // Iterate through each day in the month
        $currentDate = $startOfMonth->copy();
        while ($currentDate->lte($endOfMonth)) {
            $dayName = $currentDate->format('l');
            
            // Check if this day of week has availability
            if (in_array($dayName, $availableDaysOfWeek)) {
                // Skip past dates
                if ($currentDate->gte(Carbon::today())) {
                    $availableDates[] = $currentDate->format('Y-m-d');
                }
            }
            
            $currentDate->addDay();
        }
        
        return $availableDates;
    }
    
    /**
     * Get lunch break times for display
     * 
     * @return string|null
     */
    public function getLunchBreakDisplayAttribute()
    {
        if (!$this->has_lunch_break || !$this->lunch_start_time || !$this->lunch_end_time) {
            return null;
        }

        $start = Carbon::createFromFormat('H:i:s', $this->lunch_start_time);
        $end = Carbon::createFromFormat('H:i:s', $this->lunch_end_time);
        
        return $start->format('g:i A') . ' - ' . $end->format('g:i A');
    }
}
