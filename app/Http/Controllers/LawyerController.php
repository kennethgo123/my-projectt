<?php

namespace App\Http\Controllers;

use App\Models\LawyerProfile;
use App\Models\LawFirmProfile;
use Illuminate\Http\Request;

class LawyerController extends Controller
{
    public function search(Request $request)
    {
        $query = LawyerProfile::query()
            ->whereHas('user', function ($q) {
                $q->where('profile_completed', true)
                    ->where('status', 'approved');
            });

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Filter by service
        if ($request->filled('service')) {
            $query->whereHas('services', function ($q) use ($request) {
                $q->where('legal_services.id', $request->service);
            });
        }

        // Get law firms that match the criteria
        $lawFirmsQuery = LawFirmProfile::query()
            ->whereHas('user', function ($q) {
                $q->where('profile_completed', true)
                    ->where('status', 'approved');
            });

        if ($request->filled('city')) {
            $lawFirmsQuery->where('city', $request->city);
        }

        if ($request->filled('service')) {
            $lawFirmsQuery->whereHas('services', function ($q) use ($request) {
                $q->where('legal_services.id', $request->service);
            });
        }

        $lawyers = $query->paginate(10);
        $lawFirms = $lawFirmsQuery->paginate(10);

        return view('lawyers.search', compact('lawyers', 'lawFirms'));
    }
} 