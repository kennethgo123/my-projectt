<?php

namespace App\Livewire;

use App\Models\UserFeaturedSlot;
use Livewire\Component;

class FeaturedProfessionals extends Component
{
    public function render()
    {
        // Get active featured slots for Max tier professionals
        $featuredSlots = UserFeaturedSlot::where('is_active', true)
            ->whereDate('feature_starts_at', '<=', now())
            ->whereDate('feature_ends_at', '>=', now())
            ->whereHas('subscription', function($query) {
                $query->where('status', 'active')
                    ->whereHas('plan', function($q) {
                        $q->where('name', 'Max');
                    });
            })
            ->orderBy('rotation_order')
            ->with(['user' => function($query) {
                $query->with(['lawyerProfile', 'lawFirmProfile', 'lawFirmLawyer']);
            }])
            ->take(4) // Show up to 4 featured professionals
            ->get();
            
        return view('livewire.featured-professionals', [
            'featuredSlots' => $featuredSlots
        ]);
    }
}
