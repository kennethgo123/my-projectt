<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BlockedTimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'reason',
        'consultation_id',
        'title',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the user that owns the blocked time slot.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the consultation that created this blocked time slot.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Check if a time slot conflicts with any blocked time slots for a user.
     *
     * @param int $userId
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @param int|null $excludeId
     * @return bool
     */
    public static function hasConflict($userId, Carbon $startTime, Carbon $endTime, $excludeId = null)
    {
        $query = self::where('user_id', $userId)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($sub) use ($startTime, $endTime) {
                    // New slot starts during existing slot
                    $sub->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })->orWhere(function ($sub) use ($startTime, $endTime) {
                    // New slot ends during existing slot
                    $sub->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })->orWhere(function ($sub) use ($startTime, $endTime) {
                    // New slot completely contains existing slot
                    $sub->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get all blocked time slots for a user on a specific date.
     *
     * @param int $userId
     * @param Carbon $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBlockedSlotsForDate($userId, Carbon $date)
    {
        return self::where('user_id', $userId)
            ->whereDate('start_time', $date->format('Y-m-d'))
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Create a blocked time slot for a consultation.
     *
     * @param Consultation $consultation
     * @return self
     */
    public static function createForConsultation(Consultation $consultation)
    {
        return self::create([
            'user_id' => $consultation->specific_lawyer_id ?: $consultation->lawyer_id,
            'start_time' => $consultation->start_time,
            'end_time' => $consultation->end_time,
            'reason' => 'consultation',
            'consultation_id' => $consultation->id,
            'title' => 'Consultation with ' . $consultation->client->getProfile()->full_name,
        ]);
    }
}
