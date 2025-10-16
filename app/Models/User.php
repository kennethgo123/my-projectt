<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Specify the notification table name for Laravel's notification system
     */
    protected $notificationsTable = 'laravel_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'firm_id',
        'status',
        'profile_completed',
        'is_featured',
        'rejection_reason',
        'rejected_at',
        'is_super_admin',
        'is_staff',
        'deactivation_reason',
        'deactivated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_completed' => 'boolean',
            'is_featured' => 'boolean',
            'rejected_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function lawyerProfile()
    {
        return $this->hasOne(LawyerProfile::class);
    }

    public function lawFirmProfile()
    {
        return $this->hasOne(LawFirmProfile::class);
    }

    public function lawFirmLawyer()
    {
        return $this->hasOne(LawFirmLawyer::class, 'user_id');
    }

    public function firm()
    {
        return $this->belongsTo(User::class, 'firm_id');
    }

    public function lawyers()
    {
        return $this->hasMany(User::class, 'firm_id');
    }

    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }

    public function isClient()
    {
        return $this->role && $this->role->name === 'client';
    }

    public function isLawyer()
    {
        return $this->role && $this->role->name === 'lawyer';
    }

    public function isLawFirm()
    {
        return $this->role && $this->role->name === 'law_firm';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isDeactivated()
    {
        return $this->status === 'deactivated';
    }

    public function getProfile()
    {
        if (!$this->role) return null;
        return match ($this->role->name) {
            'client' => $this->clientProfile,
            'lawyer' => $this->lawFirmLawyer ?? $this->lawyerProfile,
            'law_firm' => $this->lawFirmProfile,
            default => null,
        };
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the cases associated with the user.
     * For lawyers, these are the cases they handle.
     * For clients, these are the cases they've opened.
     */
    public function cases()
    {
        if ($this->isLawyer()) {
            return $this->hasMany(LegalCase::class, 'lawyer_id');
        }
        return $this->hasMany(LegalCase::class, 'client_id');
    }

    /**
     * Get the notifications for this user (using Laravel's system with custom model)
     */
    public function notifications()
    {
        return $this->morphMany(\App\Extensions\DatabaseNotification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications for this user (using Laravel's system with custom model)
     */
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Get the original AppNotification records (custom system)
     * Keeping this distinct if AppNotification is a separate custom system.
     * If AppNotification and DatabaseNotification (via App\Extensions\DatabaseNotification)
     * are meant to be the SAME system, this method and its AppNotification model might be redundant.
     */
    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    /**
     * Get unread AppNotification records (custom system)
     */
    public function unreadAppNotifications()
    {
        return $this->appNotifications()->where('is_read', false);
    }

    /**
     * Get all messages sent by this user
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get all messages received by this user
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    
    /**
     * Get unread messages for this user
     */
    public function unreadMessages()
    {
        return $this->receivedMessages()->whereNull('read_at');
    }
    
    /**
     * Get all cases a lawyer is assigned to (through the case_lawyer table).
     * This includes cases where they're not the primary lawyer.
     */
    public function assignedCases()
    {
        return $this->belongsToMany(LegalCase::class, 'case_lawyer', 'user_id', 'legal_case_id')
            ->withPivot('role', 'notes', 'is_primary', 'assigned_by')
            ->withTimestamps();
    }
    
    /**
     * Get all conversations (unique users) this user has messaged with
     */
    public function conversations()
    {
        // Get users who sent messages to this user
        $senderIds = Message::where('receiver_id', $this->id)
            ->select('sender_id')
            ->distinct()
            ->pluck('sender_id');
            
        // Get users who received messages from this user
        $receiverIds = Message::where('sender_id', $this->id)
            ->select('receiver_id')
            ->distinct()
            ->pluck('receiver_id');
            
        // Combine the IDs
        $userIds = $senderIds->merge($receiverIds)->unique();
        
        return User::whereIn('id', $userIds)->get();
    }
    
    /**
     * Get conversation between this user and another user
     */
    public function getConversationWith($userId)
    {
        return Message::where(function($query) use ($userId) {
                $query->where('sender_id', $this->id)
                      ->where('receiver_id', $userId);
            })
            ->orWhere(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $this->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get the profile photo URL for the user, checking each profile type
     * to find a photo to display.
     */
    public function getProfilePhotoAttribute()
    {
        // Check lawyer profile
        if ($this->isLawyer() && $this->lawyerProfile && $this->lawyerProfile->photo_path) {
            return $this->lawyerProfile->photo_path;
        }
        
        // Check lawyer under firm profile
        if ($this->isLawyer() && $this->lawFirmLawyer && $this->lawFirmLawyer->photo_path) {
            return $this->lawFirmLawyer->photo_path;
        }
        
        // Check client profile
        if ($this->isClient() && $this->clientProfile && $this->clientProfile->photo_path) {
            return $this->clientProfile->photo_path;
        }
        
        // Check law firm profile
        if ($this->isLawFirm() && $this->lawFirmProfile && $this->lawFirmProfile->photo_path) {
            return $this->lawFirmProfile->photo_path;
        }
        
        // Fall back to the default profile photo
        return $this->profile_photo_path;
    }

    /**
     * Get the URL for the user's profile photo.
     * This overrides the default from HasProfilePhoto trait.
     */
    public function getProfilePhotoUrlAttribute()
    {
        $photo = $this->getProfilePhotoAttribute();
        
        if ($photo) {
            return \Storage::disk('public')->url($photo);
        }
        
        return $this->defaultProfilePhotoUrl();
    }

    /**
     * Get consultations where the user is a client
     */
    public function clientConsultations()
    {
        return $this->hasMany(Consultation::class, 'client_id');
    }
    
    /**
     * Get consultations where the user is a lawyer
     */
    public function lawyerConsultations()
    {
        return $this->hasMany(Consultation::class, 'lawyer_id');
    }

    /**
     * Get the ratings this user (as a client) has given to lawyers.
     */
    public function givenRatings(): HasMany
    {
        return $this->hasMany(LawyerRating::class, 'client_id');
    }

    /**
     * Get the ratings this user (as a lawyer) has received from clients.
     */
    public function receivedRatings(): HasMany
    {
        return $this->hasMany(LawyerRating::class, 'lawyer_id');
    }
    
    /**
     * Get the average rating for this lawyer
     * 
     * @return float
     */
    public function getAverageRatingAttribute(): float
    {
        $ratings = $this->receivedRatings()->where('is_visible', true);
        if ($ratings->count() === 0) {
            return 0;
        }
        return round($ratings->avg('rating'), 1);
    }
    
    /**
     * Get the count of ratings for this lawyer
     * 
     * @return int
     */
    public function getRatingCountAttribute(): int
    {
        return $this->receivedRatings()->where('is_visible', true)->count();
    }

    /**
     * Get the ratings this user (as a law firm) has received from clients.
     */
    public function receivedLawFirmRatings(): HasMany
    {
        return $this->hasMany(LawFirmRating::class, 'law_firm_id');
    }

    /**
     * Get the average law firm rating for this user
     * 
     * @return float
     */
    public function getLawFirmAverageRatingAttribute(): float
    {
        $ratings = $this->receivedLawFirmRatings()->where('is_visible', true);
        if ($ratings->count() === 0) {
            return 0;
        }
        return round($ratings->avg('rating'), 1);
    }
    
    /**
     * Get the count of law firm ratings for this user
     * 
     * @return int
     */
    public function getLawFirmRatingCountAttribute(): int
    {
        return $this->receivedLawFirmRatings()->where('is_visible', true)->count();
    }

    /**
     * Get all subscriptions for this user
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the user's active subscription if any
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereDate('ends_at', '>', now())
                      ->orWhereNull('ends_at');
            })
            ->latest()
            ->withoutGlobalScopes();
    }

    /**
     * Get all featured slots for this user
     */
    public function featuredSlots()
    {
        return $this->hasMany(UserFeaturedSlot::class);
    }

    /**
     * Check if the user has a subscription of the given plan name
     */
    public function hasSubscription($planName)
    {
        return $this->activeSubscription()
            ->whereHas('plan', function($query) use ($planName) {
                $query->where('name', $planName);
            })
            ->exists();
    }

    /**
     * Check if the user is a lawyer who belongs to a law firm
     */
    public function belongsToLawFirm()
    {
        return $this->isLawyer() && !is_null($this->firm_id);
    }

    /**
     * Get the active subscription of the law firm this lawyer belongs to
     */
    public function firmSubscription()
    {
        if (!$this->belongsToLawFirm() || !$this->firm) {
            return null;
        }
        
        return $this->firm->activeSubscription;
    }

    /**
     * Get the departments the user belongs to
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }
    
    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin()
    {
        return $this->is_super_admin;
    }
    
    /**
     * Check if user is a staff member (admin, department user, or super admin)
     */
    public function isStaff()
    {
        return $this->is_staff || $this->is_super_admin || $this->departments()->exists();
    }
    
    /**
     * Get the direct permissions assigned to the user
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
    
    /**
     * Check if user has a specific permission through their departments or direct assignments
     */
    public function hasPermission($permissionSlug)
    {
        // Super admins have all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // Check if the user has direct permission
        if ($this->permissions()->where('slug', $permissionSlug)->exists()) {
            return true;
        }
        
        // Check if any of the user's departments have this permission
        return $this->departments()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }
}
