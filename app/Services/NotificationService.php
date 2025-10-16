<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\LawFirmRating;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GenericNotification;

class NotificationService
{
    /**
     * Create a new consultation request notification for a lawyer
     */
    public static function newConsultationRequest(Consultation $consultation)
    {
        try {
            $lawyer = User::find($consultation->lawyer_id);
            $client = User::find($consultation->client_id);
            
            if (!$lawyer || !$client) {
                Log::warning('Cannot create consultation request notification: lawyer or client not found', [
                    'consultation_id' => $consultation->id,
                    'lawyer_id' => $consultation->lawyer_id,
                    'client_id' => $consultation->client_id
                ]);
                return;
            }
            
            // Get client's full name from profile with better fallback
            $clientProfile = $client->clientProfile;
            
            if ($clientProfile && !empty($clientProfile->first_name) && !empty($clientProfile->last_name)) {
                $clientName = $clientProfile->first_name . ' ' . $clientProfile->last_name;
            } else if ($clientProfile && !empty($clientProfile->first_name)) {
                $clientName = $clientProfile->first_name;
            } else {
                // Last resort fallback to user name
                $clientName = $client->name;
            }
            
            $title = 'New Consultation Request';
            $content = "{$clientName} has requested a consultation with you.";
            $link = route('lawyer.consultations');
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $lawyer->id,
                'type' => 'consultation_request',
                'title' => $title,
                'content' => $content,
                'link' => $link
            ]);
            
            // Create Laravel database notification
            if ($lawyer instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $lawyer->notify(new \App\Notifications\GenericNotification(
                        'consultation_request',
                        $title,
                        $content,
                        $link
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($lawyer->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create consultation request notification: ' . $e->getMessage(), [
                'consultation_id' => isset($consultation) ? $consultation->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a consultation accepted notification for a client
     */
    public static function consultationAccepted(Consultation $consultation)
    {
        try {
            $client = User::find($consultation->client_id);
            $lawyer = User::find($consultation->lawyer_id);
            
            if (!$client || !$lawyer) {
                Log::warning('Cannot create consultation accepted notification: client or lawyer not found', [
                    'consultation_id' => $consultation->id,
                    'client_id' => $consultation->client_id,
                    'lawyer_id' => $consultation->lawyer_id
                ]);
                return;
            }
            
            // Get lawyer's full name from profile
            $lawyerProfile = $lawyer->lawyerProfile;
            $lawyerName = $lawyerProfile ? $lawyerProfile->first_name . ' ' . $lawyerProfile->last_name : $lawyer->name;
            
            $title = 'Consultation Request Accepted';
            $content = "{$lawyerName} has accepted your consultation request. Scheduled for " . 
                       \Carbon\Carbon::parse($consultation->selected_date)->format('M d, Y g:i A');
            $link = route('client.consultations');
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $client->id,
                'type' => 'consultation_accepted',
                'title' => $title,
                'content' => $content,
                'link' => $link
            ]);
            
            // Create Laravel database notification
            if ($client instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $client->notify(new \App\Notifications\GenericNotification(
                        'consultation_accepted',
                        $title,
                        $content,
                        $link
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($client->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create consultation accepted notification: ' . $e->getMessage(), [
                'consultation_id' => isset($consultation) ? $consultation->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a consultation declined notification for a client
     */
    public static function consultationDeclined(Consultation $consultation)
    {
        try {
            $client = User::find($consultation->client_id);
            $lawyer = User::find($consultation->lawyer_id);
            
            if (!$client || !$lawyer) {
                Log::warning('Cannot create consultation declined notification: client or lawyer not found', [
                    'consultation_id' => $consultation->id,
                    'client_id' => $consultation->client_id,
                    'lawyer_id' => $consultation->lawyer_id
                ]);
                return;
            }
            
            // Get lawyer's full name from profile
            $lawyerProfile = $lawyer->lawyerProfile;
            $lawyerName = $lawyerProfile ? $lawyerProfile->first_name . ' ' . $lawyerProfile->last_name : $lawyer->name;
            
            $title = 'Consultation Request Declined';
            $content = "{$lawyerName} has declined your consultation request.";
            $link = route('client.consultations');
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $client->id,
                'type' => 'consultation_declined',
                'title' => $title,
                'content' => $content,
                'link' => $link
            ]);
            
            // Create Laravel database notification
            if ($client instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $client->notify(new \App\Notifications\GenericNotification(
                        'consultation_declined',
                        $title,
                        $content,
                        $link
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($client->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create consultation declined notification: ' . $e->getMessage(), [
                'consultation_id' => isset($consultation) ? $consultation->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a meeting link updated notification for a client
     */
    public static function consultationLinkUpdated(Consultation $consultation)
    {
        try {
            $client = User::find($consultation->client_id);
            $lawyer = User::find($consultation->lawyer_id);
            
            if (!$client || !$lawyer) {
                Log::warning('Cannot create consultation link updated notification: client or lawyer not found', [
                    'consultation_id' => $consultation->id,
                    'client_id' => $consultation->client_id,
                    'lawyer_id' => $consultation->lawyer_id
                ]);
                return;
            }
            
            // Get lawyer's full name from profile
            $lawyerProfile = $lawyer->lawyerProfile;
            $lawyerName = $lawyerProfile ? $lawyerProfile->first_name . ' ' . $lawyerProfile->last_name : $lawyer->name;
            
            $title = 'Online Meeting Link Updated';
            $content = "{$lawyerName} has provided a custom meeting link for your consultation. Please check your consultation details.";
            $link = route('client.consultations');
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $client->id,
                'type' => 'meeting_link_updated',
                'title' => $title,
                'content' => $content,
                'link' => $link
            ]);
            
            // Create Laravel database notification
            if ($client instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $client->notify(new \App\Notifications\GenericNotification(
                        'meeting_link_updated',
                        $title,
                        $content,
                        $link
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($client->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create consultation link updated notification: ' . $e->getMessage(), [
                'consultation_id' => isset($consultation) ? $consultation->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a consultation completed notification for a client
     */
    public static function consultationCompleted(Consultation $consultation)
    {
        try {
            $client = User::find($consultation->client_id);
            $lawyer = User::find($consultation->lawyer_id);
            
            if (!$client || !$lawyer) {
                Log::warning('Cannot create consultation completed notification: client or lawyer not found', [
                    'consultation_id' => $consultation->id,
                    'client_id' => $consultation->client_id,
                    'lawyer_id' => $consultation->lawyer_id
                ]);
                return;
            }
            
            // Get lawyer's full name from profile
            $lawyerProfile = $lawyer->lawyerProfile;
            $lawyerName = $lawyerProfile ? $lawyerProfile->first_name . ' ' . $lawyerProfile->last_name : $lawyer->name;
            
            // Create notification message based on whether a document was provided
            $message = $consultation->consultation_document_path 
                ? "{$lawyerName} has marked your consultation as complete. You can now view the results, findings, and attached consultation document."
                : "{$lawyerName} has marked your consultation as complete. You can now view the results and findings.";
            
            $title = 'Consultation Completed';
            $link = route('client.consultations');
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $client->id,
                'type' => 'consultation_completed',
                'title' => $title,
                'content' => $message,
                'link' => $link
            ]);
            
            // Create Laravel database notification
            if ($client instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $client->notify(new \App\Notifications\GenericNotification(
                        'consultation_completed',
                        $title,
                        $message,
                        $link
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($client->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create consultation completed notification: ' . $e->getMessage(), [
                'consultation_id' => isset($consultation) ? $consultation->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a consultation assigned notification for the lawyer
     */
    public static function consultationAssigned(Consultation $consultation, User $lawyer)
    {
        try {
            $client = User::find($consultation->client_id);
            
            if (!$client || !$lawyer) {
                Log::warning('Cannot create consultation assigned notification: client or lawyer not found', [
                    'consultation_id' => $consultation->id,
                    'client_id' => $consultation->client_id,
                    'lawyer_id' => $lawyer->id ?? 'unknown'
                ]);
                return;
            }
            
            // Get client's full name from profile
            $clientName = self::getUserDisplayName($client);
            
            $title = 'Consultation Assigned To You';
            $content = "You have been assigned a consultation with {$clientName} by your law firm.";
            $link = route('lawyer.consultations');
            
            // Create notification for the assigned lawyer
            $notification = AppNotification::create([
                'user_id' => $lawyer->id,
                'type' => 'consultation_assigned',
                'title' => $title,
                'content' => $content,
                'link' => $link
            ]);
            
            // Create Laravel database notification for the lawyer
            if ($lawyer instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $lawyer->notify(new \App\Notifications\GenericNotification(
                        'consultation_assigned',
                        $title,
                        $content,
                        $link
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification to lawyer: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($lawyer->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
            
            // Also notify the client that a lawyer has been assigned
            $lawyerName = self::getUserDisplayName($lawyer);
            $firmName = '';
            
            // Get law firm name if available
            if ($lawyer->lawyerProfile && $lawyer->lawyerProfile->law_firm_id) {
                $lawFirm = User::find($lawyer->lawyerProfile->law_firm_id);
                if ($lawFirm && $lawFirm->lawFirmProfile) {
                    $firmName = $lawFirm->lawFirmProfile->firm_name;
                }
            }
            
            $clientTitle = 'Lawyer Assigned To Your Consultation';
            $clientMessage = $firmName 
                ? "{$firmName} has assigned {$lawyerName} to your consultation." 
                : "A lawyer ({$lawyerName}) has been assigned to your consultation.";
            $clientLink = route('client.consultations');
            
            $clientNotification = AppNotification::create([
                'user_id' => $client->id,
                'type' => 'lawyer_assigned',
                'title' => $clientTitle,
                'content' => $clientMessage,
                'link' => $clientLink
            ]);
            
            // Create Laravel database notification for the client
            if ($client instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $client->notify(new \App\Notifications\GenericNotification(
                        'lawyer_assigned',
                        $clientTitle,
                        $clientMessage,
                        $clientLink
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification to client: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($clientNotification) {
                try {
                    event(new \App\Events\NotificationReceived($client->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create consultation assigned notification: ' . $e->getMessage(), [
                'consultation_id' => isset($consultation) ? $consultation->id : 'unknown',
                'lawyer_id' => isset($lawyer) && $lawyer ? $lawyer->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a case started notification for a client
     */
    public static function caseStarted(LegalCase $case)
    {
        try {
            $client = User::find($case->client_id);
            $lawyer = User::find($case->lawyer_id);
            
            if (!$client || !$lawyer) {
                Log::warning('Cannot create case started notification: client or lawyer not found', [
                    'case_id' => $case->id,
                    'client_id' => $case->client_id,
                    'lawyer_id' => $case->lawyer_id
                ]);
                return;
            }
            
            // Get lawyer's full name from profile
            $lawyerProfile = $lawyer->lawyerProfile;
            $lawyerName = $lawyerProfile ? $lawyerProfile->first_name . ' ' . $lawyerProfile->last_name : $lawyer->name;
            
            // Create notification message based on whether the case has a client document
            $message = !empty($case->client_document_path)
                ? "{$lawyerName} has started a new case for you. A contract has been sent for your review, and your attached document has been received."
                : "{$lawyerName} has started a new case for you. A contract has been sent for your review.";
            
            $title = 'New Case Started';
            $link = route('client.cases');
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $client->id,
                'type' => 'case_started',
                'title' => $title,
                'content' => $message,
                'link' => $link
            ]);
            
            // Create Laravel database notification
            if ($client instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $client->notify(new \App\Notifications\GenericNotification(
                        'case_started',
                        $title,
                        $message,
                        $link
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($client->id));
                } catch (\Throwable $e) {
                    Log::error('Error dispatching notification event for caseStarted: ' . $e->getMessage(), [
                        'case_id' => $case->id,
                        'client_id' => $client->id,
                        'exception_class' => get_class($e)
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create case started notification: ' . $e->getMessage(), [
                'case_id' => isset($case) ? $case->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send a notification when a case is accepted by a lawyer
     */
    public static function caseAccepted(LegalCase $case)
    {
        try {
            // Get lawyer info for personalized notification
            $lawyer = User::find($case->lawyer_id);
            $lawyerName = 'Your lawyer';
            
            if ($lawyer) {
                if ($lawyer->lawyerProfile) {
                    $lawyerName = "{$lawyer->lawyerProfile->first_name} {$lawyer->lawyerProfile->last_name}";
                } elseif ($lawyer->lawFirmLawyer) {
                    $lawyerName = "{$lawyer->lawFirmLawyer->first_name} {$lawyer->lawFirmLawyer->last_name}";
                } else {
                    $lawyerName = $lawyer->name;
                }
            }
            
            // Build a more informative message
            $message = "{$lawyerName} has accepted your case \"{$case->title}\". " . 
                       "The next step will be to review and sign a contract once it is prepared. " .
                       "You will be notified when the contract is ready.";
            
            self::createSystemNotification(
                'case_accepted',
                $case->client_id,
                'Your case has been accepted',
                $message,
                [
                    'case_id' => $case->id,
                    'lawyer_id' => $case->lawyer_id,
                    'case_title' => $case->title
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send case accepted notification', [
                'error' => $e->getMessage(),
                'case_id' => isset($case) ? $case->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send a notification when a case is rejected by a lawyer
     */
    public static function caseRejected(LegalCase $case)
    {
        try {
            // Get lawyer info for personalized notification
            $lawyer = User::find($case->lawyer_id);
            $lawyerName = 'Your lawyer';
            
            if ($lawyer) {
                if ($lawyer->lawyerProfile) {
                    $lawyerName = "{$lawyer->lawyerProfile->first_name} {$lawyer->lawyerProfile->last_name}";
                } elseif ($lawyer->lawFirmLawyer) {
                    $lawyerName = "{$lawyer->lawFirmLawyer->first_name} {$lawyer->lawFirmLawyer->last_name}";
                } else {
                    $lawyerName = $lawyer->name;
                }
            }
            
            // Build a more informative message
            $message = "{$lawyerName} has declined your case \"{$case->title}\". " . 
                       "Reason: {$case->rejection_reason}. " .
                       "You can request a consultation with another lawyer or try submitting a new case.";
            
            self::createSystemNotification(
                'case_rejected',
                $case->client_id,
                'Your case has been declined',
                $message,
                [
                    'case_id' => $case->id,
                    'lawyer_id' => $case->lawyer_id,
                    'case_title' => $case->title,
                    'rejection_reason' => $case->rejection_reason
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send case rejected notification', [
                'error' => $e->getMessage(),
                'case_id' => isset($case) ? $case->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send a notification when a contract is sent to the client
     */
    public static function contractSent(LegalCase $case)
    {
        try {
            self::createSystemNotification(
                'contract_sent',
                $case->client_id,
                'Contract ready for your review',
                'A contract for your case "' . $case->title . '" is ready for your review and signature.',
                [
                    'case_id' => $case->id
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send contract sent notification', [
                'error' => $e->getMessage(),
                'case_id' => isset($case) ? $case->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send a notification when a case is marked as active
     */
    public static function caseActive(LegalCase $case)
    {
        try {
            self::createSystemNotification(
                'case_active',
                $case->client_id,
                'Your case is now active',
                'Your case "' . $case->title . '" is now active. Your lawyer will begin working on it immediately.',
                [
                    'case_id' => $case->id
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send case active notification', [
                'error' => $e->getMessage(),
                'case_id' => isset($case) ? $case->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send a notification when a case is updated
     */
    public static function caseUpdated(LegalCase $case)
    {
        try {
            self::createSystemNotification(
                'case_updated',
                $case->client_id,
                'Your case has been updated',
                'There is a new update on your case "' . $case->title . '". Please check your case details.',
                [
                    'case_id' => $case->id
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send case updated notification', [
                'error' => $e->getMessage(),
                'case_id' => isset($case) ? $case->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a system notification using both notification systems
     */
    public static function createSystemNotification($type, $userId, $title, $message, $data = [])
    {
        try {
            // Create app notification
            AppNotification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'content' => $message,
                'link' => $data['action_url'] ?? null,
                'is_read' => false
            ]);
            
            // Create Laravel database notification
            $user = User::find($userId);
            if ($user && $user instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $user->notify(new \App\Notifications\GenericNotification(
                        $type,
                        $title,
                        $message,
                        $data['action_url'] ?? null,
                        $data
                    ));
                    
                    // Dispatch event for real-time notifications
                    try {
                        event(new \App\Events\NotificationReceived($userId));
                    } catch (\Exception $e) {
                        Log::error('Error dispatching notification event: ' . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage(), [
                        'user_id' => $userId,
                        'type' => $type
                    ]);
                }
            } else {
                Log::warning('User not found or not a valid model when creating notification', [
                    'user_id' => $userId,
                    'type' => $type
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create system notification', [
                'error' => $e->getMessage(),
                'type' => isset($type) ? $type : 'unknown',
                'user_id' => isset($userId) ? $userId : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a new message notification
     */
    public static function newMessage($message)
    {
        $receiver = User::find($message->receiver_id);
        $sender = User::find($message->sender_id);
        
        if (!$receiver || !$sender) {
            Log::warning('Cannot create message notification: receiver or sender not found', [
                'receiver_id' => $message->receiver_id ?? 'null',
                'sender_id' => $message->sender_id ?? 'null'
            ]);
            return;
        }
        
        try {
            // Get sender's name based on their profile type
            $senderName = self::getUserDisplayName($sender);
            
            // Create message preview (truncated content)
            $messagePreview = Str::limit($message->content, 50) ?: 'Sent an attachment';
            
            $title = 'New Message';
            $content = "{$senderName} sent you a message: \"{$messagePreview}\"";
            $link = route('messages');
            
            // Create notification
            $notification = AppNotification::create([
                'user_id' => $receiver->id,
                'type' => 'new_message',
                'title' => $title,
                'content' => $content,
                'link' => $link
            ]);
            
            // Create Laravel database notification
            if ($receiver instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $receiver->notify(new \App\Notifications\GenericNotification(
                        'new_message',
                        $title,
                        $content,
                        $link
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending message notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($receiver->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching message notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create message notification: ' . $e->getMessage(), [
                'receiver_id' => isset($receiver) && $receiver ? $receiver->id : 'unknown',
                'sender_id' => isset($sender) && $sender ? $sender->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get display name for a user based on their profile type
     */
    private static function getUserDisplayName($user)
    {
        if ($user->isLawyer()) {
            if ($user->lawyerProfile) {
                return $user->lawyerProfile->first_name . ' ' . $user->lawyerProfile->last_name;
            } elseif ($user->lawFirmLawyer) {
                return $user->lawFirmLawyer->first_name . ' ' . $user->lawFirmLawyer->last_name;
            }
        } elseif ($user->isClient() && $user->clientProfile) {
            return $user->clientProfile->first_name . ' ' . $user->clientProfile->last_name;
        } elseif ($user->isLawFirm() && $user->lawFirmProfile) {
            return $user->lawFirmProfile->firm_name;
        }
        
        return $user->name;
    }

    /**
     * Create a notification for invoice-related events
     */
    public static function invoiceNotification(string $type, int $userId, string $title, string $message, array $data = []): void
    {
        self::createSystemNotification($type, $userId, $title, $message, $data);
    }

    /**
     * Create a notification for case activated
     */
    public static function caseActivated(LegalCase $case)
    {
        try {
            $client = User::find($case->client_id);
            $lawyer = User::find($case->lawyer_id);
            
            if ($client && $lawyer) {
                // Get lawyer's full name from profile
                $lawyerProfile = $lawyer->lawyerProfile;
                $lawyerName = $lawyerProfile ? $lawyerProfile->first_name . ' ' . $lawyerProfile->last_name : $lawyer->name;
                
                $title = 'Case Activated';
                $content = "Your case has been activated by {$lawyerName}. You can now track your case progress.";
                $link = route('client.case.view', ['case' => $case->id]);
                
                // Create app notification
                $notification = AppNotification::create([
                    'user_id' => $client->id,
                    'type' => 'case_activated',
                    'title' => $title,
                    'content' => $content,
                    'link' => $link
                ]);
                
                // Create Laravel database notification
                if ($client instanceof \Illuminate\Database\Eloquent\Model) {
                    try {
                        $client->notify(new \App\Notifications\GenericNotification(
                            'case_activated',
                            $title,
                            $content,
                            $link
                        ));
                    } catch (\Exception $e) {
                        Log::error('Error sending notification: ' . $e->getMessage());
                    }
                }
                
                // Dispatch event for real-time notifications
                if ($notification) {
                    try {
                        event(new \App\Events\NotificationReceived($client->id));
                    } catch (\Exception $e) {
                        Log::error('Error dispatching notification event: ' . $e->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create case activated notification: ' . $e->getMessage(), [
                'case_id' => isset($case) ? $case->id : 'unknown',
                'client_id' => isset($client) && $client ? $client->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Create a notification for case closed
     */
    public static function caseClosed(LegalCase $case)
    {
        try {
            $client = User::find($case->client_id);
            $lawyer = User::find($case->lawyer_id);
            
            if (!$client || !$lawyer) {
                Log::warning('Cannot create case closed notification: client or lawyer not found', [
                    'case_id' => $case->id,
                    'client_id' => $case->client_id,
                    'lawyer_id' => $case->lawyer_id
                ]);
                return;
            }
            
            // Get lawyer's full name from profile
            $lawyerProfile = $lawyer->lawyerProfile;
            $lawyerName = $lawyerProfile ? $lawyerProfile->first_name . ' ' . $lawyerProfile->last_name : $lawyer->name;
            
            $title = 'Case Closed';
            $content = "Your case has been closed by {$lawyerName}. The case has been archived but remains accessible for reference. Please take a moment to rate your lawyer's performance.";
            $link = route('client.cases.manage');
            $data = [
                'case_id' => $case->id,
                'can_rate' => true
            ];
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $client->id,
                'type' => 'case_closed',
                'title' => $title,
                'content' => $content,
                'link' => $link,
                'data' => $data
            ]);
            
            // Create Laravel database notification
            if ($client instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $client->notify(new \App\Notifications\GenericNotification(
                        'case_closed',
                        $title,
                        $content,
                        $link,
                        $data
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($client->id));
                } catch (\Throwable $e) {
                    Log::error('Error dispatching notification event for caseClosed: ' . $e->getMessage(), [
                        'case_id' => $case->id,
                        'client_id' => $client->id,
                        'exception_class' => get_class($e)
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create case closed notification: ' . $e->getMessage(), [
                'case_id' => isset($case) ? $case->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a notification for lawyer rating
     */
    public static function lawyerRated($lawyerId, $caseId, $caseTitle, $rating)
    {
        try {
            $lawyer = User::find($lawyerId);
            if (!$lawyer || !($lawyer instanceof \Illuminate\Database\Eloquent\Model)) {
                Log::error('Lawyer not found or invalid when creating rating notification', [
                    'lawyer_id' => $lawyerId,
                    'case_id' => $caseId
                ]);
                return;
            }
            
            $title = 'New Rating Received';
            $content = 'You have received a rating for case: ' . $caseTitle;
            $link = route('lawyer.cases.show', $caseId);
            $data = [
                'case_id' => $caseId,
                'rating' => $rating
            ];
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $lawyerId,
                'type' => 'lawyer_rated',
                'title' => $title,
                'content' => $content,
                'link' => $link,
                'data' => $data
            ]);
            
            // Create Laravel database notification
            try {
                $lawyer->notify(new \App\Notifications\GenericNotification(
                    'lawyer_rated',
                    $title,
                    $content,
                    $link,
                    $data
                ));
            } catch (\Exception $e) {
                Log::error('Error sending lawyer rating database notification: ' . $e->getMessage(), ['lawyer_id' => $lawyerId, 'case_id' => $caseId]);
            }
            
            // Dispatch event for real-time notifications if available
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($lawyerId));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create lawyer rating notification: ' . $e->getMessage(), [
                'lawyer_id' => isset($lawyerId) ? $lawyerId : 'unknown',
                'case_id' => isset($caseId) ? $caseId : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a notification for law firm rating
     */
    public static function lawFirmRated(LawFirmRating $rating)
    {
        try {
            $lawFirm = User::find($rating->law_firm_id);
            if (!$lawFirm || !($lawFirm instanceof \Illuminate\Database\Eloquent\Model)) {
                Log::error('Law firm not found or invalid when creating rating notification', [
                    'law_firm_id' => $rating->law_firm_id,
                    'case_id' => $rating->legal_case_id
                ]);
                return;
            }
            
            $case = LegalCase::find($rating->legal_case_id);
            $caseTitle = $case ? $case->title : 'Unknown Case';
            
            $title = 'New Rating Received';
            $content = 'Your law firm has received a rating for case: ' . $caseTitle;
            $link = route('law-firm.cases.show', $rating->legal_case_id);
            $data = [
                'case_id' => $rating->legal_case_id,
                'rating' => $rating->rating
            ];
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $rating->law_firm_id,
                'type' => 'law_firm_rated',
                'title' => $title,
                'content' => $content,
                'link' => $link,
                'data' => $data
            ]);
            
            // Create Laravel database notification
            try {
                $lawFirm->notify(new \App\Notifications\GenericNotification(
                    'law_firm_rated',
                    $title,
                    $content,
                    $link,
                    $data
                ));
            } catch (\Exception $e) {
                Log::error('Error sending law firm rating database notification: ' . $e->getMessage(), [
                    'law_firm_id' => $rating->law_firm_id,
                    'case_id' => $rating->legal_case_id
                ]);
            }
            
            // Dispatch event for real-time notifications if available
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($rating->law_firm_id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create law firm rating notification: ' . $e->getMessage(), [
                'law_firm_id' => $rating->law_firm_id ?? 'unknown',
                'case_id' => $rating->legal_case_id ?? 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send notification to lawyer when a payment is received
     */
    public static function paymentReceived($invoice, $payment)
    {
        try {
            // Find the lawyer who should be notified
            $lawyer = User::find($invoice->lawyer_id);
            
            if (!$lawyer) {
                Log::warning('Cannot create payment received notification: lawyer not found', [
                    'invoice_id' => $invoice->id,
                    'lawyer_id' => $invoice->lawyer_id
                ]);
                return;
            }
            
            // Get client name for personalized notification
            $client = User::find($payment->client_id);
            $clientName = $client ? self::getUserDisplayName($client) : 'A client';
            
            // Determine if this is a full payment or installment
            $paymentType = $invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL ? 'installment' : 'full';
            
            // Create notification title and content
            $title = 'Payment Received';
            $content = "{$clientName} has made a {$paymentType} payment of PHP " . 
                       number_format($payment->amount, 2) . 
                       " for Invoice #{$invoice->invoice_number} via " . 
                       ucfirst($payment->payment_method) . ".";
            
            // Create link to the invoice page
            $link = '';
            if ($invoice->legal_case_id) {
                $link = route('lawyer.case.setup', ['case' => $invoice->legal_case_id, 'tab' => 'invoices']);
            } else {
                $link = route('lawyer.invoices');
            }
            
            // Data for the notification
            $data = [
                'invoice_id' => $invoice->id,
                'case_id' => $invoice->legal_case_id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method
            ];
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $lawyer->id,
                'type' => 'payment_received',
                'title' => $title,
                'content' => $content,
                'link' => $link,
                'data' => $data
            ]);
            
            // Create Laravel database notification
            if ($lawyer instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $lawyer->notify(new \App\Notifications\GenericNotification(
                        'payment_received',
                        $title,
                        $content,
                        $link,
                        $data
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending payment notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($lawyer->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create payment received notification: ' . $e->getMessage(), [
                'invoice_id' => isset($invoice) ? $invoice->id : 'unknown',
                'payment_id' => isset($payment) ? $payment->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create a notification for case updates
     */
    public static function contractRejectedByClient(LegalCase $case, User $client, string $reason)
    {
        if ($case->lawyer instanceof \Illuminate\Database\Eloquent\Model) {
            try {
                $title = "Contract Rejected by Client";
                $content = "{$client->name} has rejected the contract for case #{$case->case_number}. Reason: {$reason}";
                $actionUrl = route('lawyer.cases.show', $case->id); // Or manage cases page

                $case->lawyer->notify(new GenericNotification(
                    $title,
                    $content,
                    $actionUrl,
                    'contract_rejected', // type
                    $client->id // related_id if needed, e.g., client who took action
                ));
            } catch (\Throwable $e) {
                Log::error("Error sending contract rejected notification to lawyer {$case->lawyer_id}: " . $e->getMessage(), [
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    public static function contractChangesRequestedByClient(LegalCase $case, User $client, string $requestedChanges)
    {
        if ($case->lawyer instanceof \Illuminate\Database\Eloquent\Model) {
            try {
                $title = "Contract Changes Requested by Client";
                $content = "{$client->name} has requested changes to the contract for case #{$case->case_number}. Details: {$requestedChanges}";
                $actionUrl = route('lawyer.cases.show', $case->id); // Or manage cases page

                $case->lawyer->notify(new GenericNotification(
                    $title,
                    $content,
                    $actionUrl,
                    'contract_changes_requested', // type
                    $client->id // related_id
                ));
            } catch (\Throwable $e) {
                Log::error("Error sending contract changes requested notification to lawyer {$case->lawyer_id}: " . $e->getMessage(), [
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    public static function revisedContractUploaded(LegalCase $case, User $lawyer)
    {
        if ($case->client instanceof \Illuminate\Database\Eloquent\Model) {
            try {
                $title = "Revised Contract Uploaded";
                $content = "A revised contract has been uploaded by {$lawyer->name} for case #{$case->case_number}. Please review.";
                $actionUrl = route('client.contract.review', $case->id);

                $case->client->notify(new GenericNotification(
                    $title,
                    $content,
                    $actionUrl,
                    'revised_contract_uploaded', // type
                    $lawyer->id // related_id, e.g., lawyer who uploaded
                ));
            } catch (\Throwable $e) {
                Log::error("Error sending revised contract notification to client {$case->client_id}: " . $e->getMessage(), [
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Create a notification when a task status is changed
     */
    public static function taskStatusChanged($task, $statusText, $actor, $recipient)
    {
        try {
            if (!$task || !$actor || !$recipient) {
                Log::warning('Cannot create task status changed notification: missing data', [
                    'task_id' => $task->id ?? 'null',
                    'actor_id' => $actor->id ?? 'null',
                    'recipient_id' => $recipient->id ?? 'null'
                ]);
                return;
            }
            
            // Get actor's name based on their profile type
            $actorName = self::getUserDisplayName($actor);
            
            $title = 'Task Status Changed';
            $content = "{$actorName} has {$statusText} the task: \"{$task->title}\"";
            
            // Set appropriate link based on the recipient's role
            $link = null;
            if ($recipient->hasRole('lawyer')) {
                $link = route('lawyer.case.setup', $task->legal_case_id);
            } elseif ($recipient->hasRole('client')) {
                $link = route('client.case.overview', $task->legal_case_id);
            } elseif ($recipient->hasRole('law_firm')) {
                $link = route('law-firm.case.setup', $task->legal_case_id);
            }
            
            // Create app notification
            $notification = AppNotification::create([
                'user_id' => $recipient->id,
                'type' => 'task_status_changed',
                'title' => $title,
                'content' => $content,
                'link' => $link,
                'data' => [
                    'task_id' => $task->id,
                    'case_id' => $task->legal_case_id,
                    'status' => $statusText
                ]
            ]);
            
            // Create Laravel database notification
            if ($recipient instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $recipient->notify(new \App\Notifications\GenericNotification(
                        'task_status_changed',
                        $title,
                        $content,
                        $link,
                        [
                            'task_id' => $task->id,
                            'case_id' => $task->legal_case_id,
                            'status' => $statusText
                        ]
                    ));
                } catch (\Exception $e) {
                    Log::error('Error sending task status notification: ' . $e->getMessage());
                }
            }
            
            // Dispatch event for real-time notifications
            if ($notification) {
                try {
                    event(new \App\Events\NotificationReceived($recipient->id));
                } catch (\Exception $e) {
                    Log::error('Error dispatching task status notification event: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create task status changed notification: ' . $e->getMessage(), [
                'task_id' => isset($task) ? $task->id : 'unknown',
                'actor_id' => isset($actor) ? $actor->id : 'unknown',
                'recipient_id' => isset($recipient) ? $recipient->id : 'unknown',
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 