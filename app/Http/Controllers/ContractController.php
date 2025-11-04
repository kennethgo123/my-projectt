<?php

namespace App\Http\Controllers;

use App\Models\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    /**
     * Download the contract for a case (for clients)
     */
    public function download(LegalCase $case)
    {
        // Ensure the user has permission to download this contract
        if ($case->client_id !== Auth::id()) {
            abort(403, 'You are not authorized to download this contract.');
        }
        
        // Check if contract exists
        if (!$case->contract_path || !Storage::disk('public')->exists($case->contract_path)) {
            abort(404, 'Contract not found.');
        }
        
        // Get the file path
        $filePath = Storage::disk('public')->path($case->contract_path);
        
        // Get the original filename or generate one
        $fileName = basename($case->contract_path);
        if (empty($fileName) || $fileName === $case->contract_path) {
            $fileName = 'contract_' . $case->case_number . '.pdf';
        }
        
        // Return download response
        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
    
    /**
     * View the contract for a case (for lawyers)
     */
    public function view(LegalCase $case)
    {
        // Ensure the user is the lawyer for this case
        $lawyerId = Auth::id();
        $isAuthorized = false;
        
        // Check if user is the primary lawyer
        if ($case->lawyer_id === $lawyerId) {
            $isAuthorized = true;
        }
        
        // Check if user is part of the team lawyers
        if (!$isAuthorized && $case->teamLawyers()->where('user_id', $lawyerId)->exists()) {
            $isAuthorized = true;
        }
        
        if (!$isAuthorized) {
            abort(403, 'You are not authorized to view this contract.');
        }
        
        // Check if contract exists
        if (!$case->contract_path || !Storage::disk('public')->exists($case->contract_path)) {
            abort(404, 'Contract not found.');
        }
        
        // Get the file path
        $filePath = Storage::disk('public')->path($case->contract_path);
        
        // Return the file for viewing (not download)
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
