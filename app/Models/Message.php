<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'attachment_path',
        'read_at',
        'legal_case_id'
    ];
    
    protected $casts = [
        'read_at' => 'datetime',
    ];
    
    /**
     * Get the sender of this message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    /**
     * Get the receiver of this message
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    
    /**
     * Get the legal case associated with this message (if any)
     */
    public function legalCase()
    {
        return $this->belongsTo(LegalCase::class, 'legal_case_id');
    }
    
    /**
     * Scope a query to only include unread messages for a user.
     */
    public function scopeUnread($query, $userId)
    {
        return $query->where('receiver_id', $userId)
                     ->whereNull('read_at');
    }
    
    /**
     * Mark the message as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
    
    /**
     * Check if the message is read
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Format the timestamp for display in Philippine Standard Time
     * 
     * @param string $format The date format to use
     * @return string
     */
    public function formatTimePHT($format = 'g:i A')
    {
        return $this->created_at->setTimezone('Asia/Manila')->format($format);
    }

    /**
     * Get a human-readable timestamp for the message in Philippine time
     * 
     * @return string
     */
    public function getReadableTimestamp()
    {
        $date = $this->created_at->setTimezone('Asia/Manila');
        
        if ($date->isToday()) {
            return $date->format('g:i A');
        } elseif ($date->isYesterday()) {
            return 'Yesterday, ' . $date->format('g:i A');
        } else {
            return $date->format('M j, Y g:i A');
        }
    }
}
