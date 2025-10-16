<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use App\Models\Message;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\CaseUpdate;
use App\Models\ContractAction;
use App\Models\LawyerRating;
use App\Models\LawFirmRating;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InvestigationTimelineService
{
    /**
     * Get unified timeline of all interactions between a lawyer and client
     * 
     * @param int $lawyerId
     * @param int $clientId
     * @param Carbon|null $fromDate
     * @param Carbon|null $toDate
     * @return Collection
     */
    public function getUnifiedTimeline(int $lawyerId, int $clientId, ?Carbon $fromDate = null, ?Carbon $toDate = null): Collection
    {
        $timeline = collect();

        // Collect all interactions
        $timeline = $timeline->merge($this->getMessages($lawyerId, $clientId, $fromDate, $toDate));
        $timeline = $timeline->merge($this->getConsultations($lawyerId, $clientId, $fromDate, $toDate));
        $timeline = $timeline->merge($this->getLegalCases($lawyerId, $clientId, $fromDate, $toDate));
        $timeline = $timeline->merge($this->getCaseUpdates($lawyerId, $clientId, $fromDate, $toDate));
        $timeline = $timeline->merge($this->getContractActions($lawyerId, $clientId, $fromDate, $toDate));
        $timeline = $timeline->merge($this->getRatingsAndReviews($lawyerId, $clientId, $fromDate, $toDate));

        // Sort by timestamp in descending order (most recent first)
        return $timeline->sortByDesc('timestamp')->values();
    }

    /**
     * Get timeline for a specific report investigation
     * 
     * @param Report $report
     * @param Carbon|null $fromDate
     * @param Carbon|null $toDate
     * @return Collection
     */
    public function getReportTimeline(Report $report, ?Carbon $fromDate = null, ?Carbon $toDate = null): Collection
    {
        $reporterId = $report->reporter_id;
        $reportedUserId = $report->reported_user_id;

        return $this->getUnifiedTimeline($reportedUserId, $reporterId, $fromDate, $toDate);
    }

    /**
     * Get conversation statistics for investigation
     * 
     * @param int $lawyerId
     * @param int $clientId
     * @return array
     */
    public function getInteractionStats(int $lawyerId, int $clientId): array
    {
        $messages = Message::where(function ($query) use ($lawyerId, $clientId) {
            $query->where('sender_id', $lawyerId)->where('receiver_id', $clientId);
        })->orWhere(function ($query) use ($lawyerId, $clientId) {
            $query->where('sender_id', $clientId)->where('receiver_id', $lawyerId);
        })->get();

        $consultations = Consultation::where('lawyer_id', $lawyerId)
            ->where('client_id', $clientId)
            ->get();

        $cases = LegalCase::where('lawyer_id', $lawyerId)
            ->where('client_id', $clientId)
            ->get();

        return [
            'total_messages' => $messages->count(),
            'messages_by_lawyer' => $messages->where('sender_id', $lawyerId)->count(),
            'messages_by_client' => $messages->where('sender_id', $clientId)->count(),
            'total_consultations' => $consultations->count(),
            'completed_consultations' => $consultations->where('status', 'completed')->count(),
            'total_cases' => $cases->count(),
            'active_cases' => $cases->whereIn('status', ['active', 'in_progress'])->count(),
            'first_interaction' => $messages->min('created_at') ? Carbon::parse($messages->min('created_at'))->format('M j, Y') : 'N/A',
            'last_interaction' => $messages->max('created_at') ? Carbon::parse($messages->max('created_at'))->format('M j, Y') : 'N/A',
            'avg_response_time' => $this->calculateAverageResponseTime($messages),
            'privacy_note' => 'Message content is protected. Only metadata and timing patterns are analyzed.',
        ];
    }

    /**
     * Get messages between lawyer and client
     */
    private function getMessages(int $lawyerId, int $clientId, ?Carbon $fromDate, ?Carbon $toDate): Collection
    {
        $query = Message::where(function ($q) use ($lawyerId, $clientId) {
            $q->where('sender_id', $lawyerId)->where('receiver_id', $clientId);
        })->orWhere(function ($q) use ($lawyerId, $clientId) {
            $q->where('sender_id', $clientId)->where('receiver_id', $lawyerId);
        })->with(['sender', 'receiver']);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        return $query->get()->map(function ($message) {
            return [
                'id' => $message->id,
                'type' => 'message',
                'icon' => 'chat',
                'title' => 'Message Sent',
                'description' => '[Message content hidden for privacy]',
                'full_content' => '[Message content is not available to staff for privacy protection]',
                'actor' => $message->sender->name,
                'actor_type' => $message->sender->isLawyer() ? 'lawyer' : 'client',
                'timestamp' => $message->created_at,
                'formatted_time' => $message->created_at->format('M j, Y g:i A'),
                'metadata' => [
                    'has_attachment' => !empty($message->attachment_path),
                    'read_at' => $message->read_at?->format('M j, Y g:i A'),
                    'character_count' => strlen($message->content ?? ''),
                    'privacy_note' => 'Message content is protected for privacy',
                ],
                'severity' => 'normal',
                'category' => 'communication'
            ];
        });
    }

    /**
     * Get consultations between lawyer and client
     */
    private function getConsultations(int $lawyerId, int $clientId, ?Carbon $fromDate, ?Carbon $toDate): Collection
    {
        $query = Consultation::where('lawyer_id', $lawyerId)
            ->where('client_id', $clientId)
            ->with(['lawyer', 'client']);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        return $query->get()->map(function ($consultation) {
            return [
                'id' => $consultation->id,
                'type' => 'consultation',
                'icon' => 'calendar',
                'title' => 'Consultation ' . ucfirst($consultation->status),
                'description' => $consultation->consultation_type . ' - ' . $this->truncateText($consultation->description, 80),
                'full_content' => $consultation->description ?? '',
                'actor' => $consultation->status === 'pending' ? 'Client' : 'Lawyer',
                'actor_type' => $consultation->status === 'pending' ? 'client' : 'lawyer',
                'timestamp' => $consultation->created_at,
                'formatted_time' => $consultation->created_at->format('M j, Y g:i A'),
                'metadata' => [
                    'status' => $consultation->status,
                    'consultation_type' => $consultation->consultation_type,
                    'requested_date' => $consultation->created_at->format('M j, Y g:i A'),
                    'start_time' => $consultation->start_time ? Carbon::parse($consultation->start_time)->format('M j, Y g:i A') : null,
                    'end_time' => $consultation->end_time ? Carbon::parse($consultation->end_time)->format('M j, Y g:i A') : null,
                    'meeting_link' => $consultation->meeting_link ? 'Yes' : 'No',
                ],
                'severity' => $consultation->status === 'declined' ? 'warning' : 'normal',
                'category' => 'consultation'
            ];
        });
    }

    /**
     * Get legal cases between lawyer and client
     */
    private function getLegalCases(int $lawyerId, int $clientId, ?Carbon $fromDate, ?Carbon $toDate): Collection
    {
        $query = LegalCase::where('lawyer_id', $lawyerId)
            ->where('client_id', $clientId)
            ->with(['lawyer', 'client']);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        return $query->get()->map(function ($case) {
            return [
                'id' => $case->id,
                'type' => 'legal_case',
                'icon' => 'briefcase',
                'title' => 'Legal Case: ' . $case->title,
                'description' => 'Status: ' . ucfirst($case->status) . ' - ' . $this->truncateText($case->description, 80),
                'full_content' => $case->description ?? '',
                'actor' => 'System',
                'actor_type' => 'system',
                'timestamp' => $case->created_at,
                'formatted_time' => $case->created_at->format('M j, Y g:i A'),
                'metadata' => [
                    'case_number' => $case->case_number,
                    'status' => $case->status,
                    'priority' => $case->priority,
                    'contract_status' => $case->contract_status,
                    'case_started' => $case->created_at->format('M j, Y g:i A'),
                    'signed_at' => $case->signed_at?->format('M j, Y g:i A'),
                    'closed_at' => $case->closed_at?->format('M j, Y g:i A'),
                ],
                'severity' => in_array($case->status, ['disputed', 'rejected']) ? 'warning' : 'normal',
                'category' => 'case_management'
            ];
        });
    }

    /**
     * Get case updates for cases between lawyer and client
     */
    private function getCaseUpdates(int $lawyerId, int $clientId, ?Carbon $fromDate, ?Carbon $toDate): Collection
    {
        $caseIds = LegalCase::where('lawyer_id', $lawyerId)
            ->where('client_id', $clientId)
            ->pluck('id');

        if ($caseIds->isEmpty()) {
            return collect();
        }

        $query = CaseUpdate::whereIn('legal_case_id', $caseIds)
            ->with(['legalCase', 'user']);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        return $query->get()->map(function ($update) {
            return [
                'id' => $update->id,
                'type' => 'case_update',
                'icon' => 'document',
                'title' => 'Case Update: ' . $update->title,
                'description' => $this->truncateText($update->content, 100),
                'full_content' => $update->content ?? '',
                'actor' => $update->creator_name ?? 'System',
                'actor_type' => $update->user?->isLawyer() ? 'lawyer' : ($update->user?->isClient() ? 'client' : 'system'),
                'timestamp' => $update->created_at,
                'formatted_time' => $update->created_at->format('M j, Y g:i A'),
                'metadata' => [
                    'case_title' => $update->legalCase->title,
                    'case_number' => $update->legalCase->case_number,
                    'update_type' => $update->update_type ?? 'general',
                    'visibility' => $update->visibility ?? 'both',
                ],
                'severity' => 'normal',
                'category' => 'case_update'
            ];
        });
    }

    /**
     * Get contract actions for cases between lawyer and client
     */
    private function getContractActions(int $lawyerId, int $clientId, ?Carbon $fromDate, ?Carbon $toDate): Collection
    {
        $caseIds = LegalCase::where('lawyer_id', $lawyerId)
            ->where('client_id', $clientId)
            ->pluck('id');

        if ($caseIds->isEmpty()) {
            return collect();
        }

        $query = ContractAction::whereIn('legal_case_id', $caseIds)
            ->with(['legalCase']);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        return $query->get()->map(function ($action) {
            return [
                'id' => $action->id,
                'type' => 'contract_action',
                'icon' => 'document-signature',
                'title' => 'Contract Action: ' . ucfirst(str_replace('_', ' ', $action->action_type)),
                'description' => $this->truncateText($action->details, 100),
                'full_content' => $action->details ?? '',
                'actor' => ucfirst($action->actor_type),
                'actor_type' => $action->actor_type,
                'timestamp' => $action->created_at,
                'formatted_time' => $action->created_at->format('M j, Y g:i A'),
                'metadata' => [
                    'action_type' => $action->action_type,
                    'case_title' => $action->legalCase->title,
                    'lawyer_acknowledged' => $action->lawyer_acknowledged,
                    'acknowledged_at' => $action->lawyer_acknowledged_at?->format('M j, Y g:i A'),
                    'has_signature' => !empty($action->signature_path),
                ],
                'severity' => $action->action_type === 'reject_contract' ? 'warning' : 'normal',
                'category' => 'contract'
            ];
        });
    }

    /**
     * Get ratings and reviews between lawyer and client
     */
    private function getRatingsAndReviews(int $lawyerId, int $clientId, ?Carbon $fromDate, ?Carbon $toDate): Collection
    {
        $query = LawyerRating::where('lawyer_id', $lawyerId)
            ->where('client_id', $clientId)
            ->with(['lawyer', 'client', 'legalCase']);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        return $query->get()->map(function ($rating) {
            return [
                'id' => $rating->id,
                'type' => 'rating',
                'icon' => 'star',
                'title' => 'Lawyer Rating: ' . $rating->rating . '/5 stars',
                'description' => $this->truncateText($rating->feedback, 100),
                'full_content' => $rating->feedback ?? '',
                'actor' => 'Client',
                'actor_type' => 'client',
                'timestamp' => $rating->created_at,
                'formatted_time' => $rating->created_at->format('M j, Y g:i A'),
                'metadata' => [
                    'rating' => $rating->rating,
                    'is_visible' => $rating->is_visible,
                    'case_title' => $rating->legalCase?->title,
                    'rated_at' => $rating->rated_at?->format('M j, Y g:i A'),
                ],
                'severity' => $rating->rating <= 2 ? 'warning' : 'normal',
                'category' => 'feedback'
            ];
        });
    }

    /**
     * Calculate average response time between messages
     */
    private function calculateAverageResponseTime(Collection $messages): string
    {
        if ($messages->count() < 2) {
            return 'N/A';
        }

        $sortedMessages = $messages->sortBy('created_at');
        $responseTimes = [];

        for ($i = 1; $i < $sortedMessages->count(); $i++) {
            $previous = $sortedMessages->values()[$i - 1];
            $current = $sortedMessages->values()[$i];

            // Only calculate if it's a response (different senders)
            if ($previous->sender_id !== $current->sender_id) {
                $diff = Carbon::parse($current->created_at)->diffInMinutes(Carbon::parse($previous->created_at));
                $responseTimes[] = $diff;
            }
        }

        if (empty($responseTimes)) {
            return 'N/A';
        }

        $averageMinutes = array_sum($responseTimes) / count($responseTimes);

        if ($averageMinutes < 60) {
            return round($averageMinutes) . ' minutes';
        } elseif ($averageMinutes < 1440) {
            return round($averageMinutes / 60, 1) . ' hours';
        } else {
            return round($averageMinutes / 1440, 1) . ' days';
        }
    }

    /**
     * Truncate text for display
     */
    private function truncateText(?string $text, int $length): string
    {
        if ($text === null) {
            return '';
        }
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    /**
     * Get red flags and suspicious patterns
     */
    public function getRedFlags(int $lawyerId, int $clientId): array
    {
        $timeline = $this->getUnifiedTimeline($lawyerId, $clientId);
        $redFlags = [];

        // Check for unusual messaging patterns
        $messages = $timeline->where('type', 'message');
        $lateNightMessages = $messages->filter(function ($message) {
            $hour = $message['timestamp']->hour;
            return $hour >= 22 || $hour <= 6; // 10 PM to 6 AM
        });

        if ($lateNightMessages->count() > 5) {
            $redFlags[] = [
                'type' => 'unusual_timing',
                'severity' => 'medium',
                'description' => 'Frequent late-night communication (' . $lateNightMessages->count() . ' messages)',
                'count' => $lateNightMessages->count()
            ];
        }

        // Check for declined consultations
        $declinedConsultations = $timeline->where('type', 'consultation')
            ->where('metadata.status', 'declined');

        if ($declinedConsultations->count() > 1) {
            $redFlags[] = [
                'type' => 'declined_consultations',
                'severity' => 'medium',
                'description' => 'Multiple declined consultations (' . $declinedConsultations->count() . ')',
                'count' => $declinedConsultations->count()
            ];
        }

        // Check for low ratings
        $lowRatings = $timeline->where('type', 'rating')
            ->filter(function ($item) {
                return $item['metadata']['rating'] <= 2;
            });

        if ($lowRatings->count() > 0) {
            $redFlags[] = [
                'type' => 'low_ratings',
                'severity' => 'high',
                'description' => 'Poor client ratings received (' . $lowRatings->count() . ' ratings ≤ 2 stars)',
                'count' => $lowRatings->count()
            ];
        }

        // Check for contract rejections
        $contractRejections = $timeline->where('type', 'contract_action')
            ->where('metadata.action_type', 'reject_contract');

        if ($contractRejections->count() > 0) {
            $redFlags[] = [
                'type' => 'contract_rejections',
                'severity' => 'medium',
                'description' => 'Contract rejections (' . $contractRejections->count() . ')',
                'count' => $contractRejections->count()
            ];
        }

        return $redFlags;
    }
}
