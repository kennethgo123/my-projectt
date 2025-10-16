<?php

namespace App\Http\Controllers;

use App\Models\LegalCase;
use App\Models\ContractAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{
    /**
     * Show a case signature using all possible methods
     */
    public function showSignature($caseId, Request $request)
    {
        $case = LegalCase::withTrashed()->findOrFail($caseId);
        $signaturePath = null;
        
        // Method 1: Direct from legal_cases
        if ($case->signature_path) {
            $signaturePath = $case->signature_path;
            Log::info("Found signature in legal_cases: {$signaturePath}");
        }
        
        // Method 2: From contract actions - accepted
        if (!$signaturePath) {
            $contractAction = ContractAction::where('legal_case_id', $caseId)
                ->where('action_type', 'accepted')
                ->whereNotNull('signature_path')
                ->latest()
                ->first();
                
            if ($contractAction && $contractAction->signature_path) {
                $signaturePath = $contractAction->signature_path;
                Log::info("Found signature in contract_actions (accepted): {$signaturePath}");
            }
        }
        
        // Method 3: From contract actions - contract_signed
        if (!$signaturePath) {
            $contractAction = ContractAction::where('legal_case_id', $caseId)
                ->where('action_type', 'contract_signed')
                ->whereNotNull('signature_path')
                ->latest()
                ->first();
                
            if ($contractAction && $contractAction->signature_path) {
                $signaturePath = $contractAction->signature_path;
                Log::info("Found signature in contract_actions (contract_signed): {$signaturePath}");
            }
        }
        
        // Method 4: From contract actions - any
        if (!$signaturePath) {
            $contractAction = ContractAction::where('legal_case_id', $caseId)
                ->whereNotNull('signature_path')
                ->latest()
                ->first();
                
            if ($contractAction && $contractAction->signature_path) {
                $signaturePath = $contractAction->signature_path;
                Log::info("Found signature in contract_actions (any): {$signaturePath}");
            }
        }
        
        if (!$signaturePath) {
            Log::error("No signature found for case ID: {$caseId}");
            return response()->json(['error' => 'No signature found'], 404);
        }
        
        // Update the case if needed
        if ($case->signature_path !== $signaturePath) {
            $case->update(['signature_path' => $signaturePath]);
            Log::info("Updated legal_case.signature_path for case {$caseId}");
        }

        // If acknowledged=1 is in the query string, show the signature directly
        if ($request->query('acknowledged')) {
            // Try different path combinations
            $paths = [
                $signaturePath,
                'public/' . $signaturePath,
                str_replace('public/', '', $signaturePath),
                'signatures/' . basename($signaturePath),
                basename($signaturePath)
            ];
            
            foreach ($paths as $path) {
                // Check if file exists in storage
                if (Storage::exists($path)) {
                    $fullPath = Storage::path($path);
                    if (file_exists($fullPath)) {
                        return response()->file($fullPath);
                    }
                }
                
                // Also try public disk
                if (Storage::disk('public')->exists($path)) {
                    $fullPath = Storage::disk('public')->path($path);
                    if (file_exists($fullPath)) {
                        return response()->file($fullPath);
                    }
                }
            }
            
            return response()->json([
                'error' => 'File not found',
                'path' => $signaturePath
            ], 404);
        }

        // If not acknowledged, show the acknowledgment page
        return view('signature.acknowledgment', [
            'signatureUrl' => route('signature.show', ['caseId' => $caseId, 'acknowledged' => 1])
        ]);
    }
} 