<?php

namespace App\Http\Controllers;

use App\Models\LawFirmProfile;
use Illuminate\Http\Request;

class LawFirmController extends Controller
{
    /**
     * Display the specified law firm profile.
     *
     * @param  \App\Models\LawFirmProfile  $lawFirmProfile
     * @return \Illuminate\View\View
     */
    public function __invoke(LawFirmProfile $lawFirmProfile)
    {
        // Make sure the law firm is approved and profile is completed
        if (!$lawFirmProfile->user || $lawFirmProfile->user->status !== 'approved' || !$lawFirmProfile->user->profile_completed) {
            abort(404);
        }
        
        // Load the services relationship
        $lawFirmProfile->load(['services', 'user']);
        
        // Load lawyers associated with this law firm
        $lawyers = $lawFirmProfile->lawyers()
            ->whereHas('user', function($q) {
                $q->where('status', 'approved')
                    ->where('profile_completed', true);
            })
            ->get();
        
        return view('law-firm-profile', [
            'lawFirm' => $lawFirmProfile,
            'lawyers' => $lawyers
        ]);
    }
} 