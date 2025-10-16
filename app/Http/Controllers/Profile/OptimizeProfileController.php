<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\LawyerBio;
use Illuminate\Http\Request;

class OptimizeProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        if ($user->role->name === 'lawyer') {
            return redirect()->route('lawyers.optimize-profile');
        } elseif ($user->role->name === 'law_firm') {
            return redirect()->route('law-firm.optimize-profile');
        }

        return redirect()->back()->with('error', 'Invalid user role for profile optimization.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'skills' => 'required|string',
            'experience' => 'required|string',
            'education' => 'required|string',
            'achievements' => 'nullable|string',
            'specializations' => 'required|string',
        ]);

        $user = auth()->user();
        
        $data = $request->only(['skills', 'experience', 'education', 'achievements', 'specializations']);

        if ($user->role->name === 'lawyer') {
            $data['lawyer_profile_id'] = $user->lawyerProfile->id;
            LawyerBio::updateOrCreate(
                ['lawyer_profile_id' => $user->lawyerProfile->id],
                $data
            );
        } elseif ($user->role->name === 'law_firm') {
            $data['law_firm_profile_id'] = $user->lawFirmProfile->id;
            LawyerBio::updateOrCreate(
                ['law_firm_profile_id' => $user->lawFirmProfile->id],
                $data
            );
        }

        return redirect()->back()->with('success', 'Profile optimized successfully.');
    }
} 