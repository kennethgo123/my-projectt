<?php

namespace App\Http\Controllers\Lawyer;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Create a legal case from a consultation.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Consultation $consultation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createCase(Request $request, Consultation $consultation)
    {
        // Ensure the lawyer is authorized to create a case from this consultation
        if ($consultation->lawyer_id !== auth()->id()) {
            return redirect()->back()->with('error', 'You are not authorized to create a case from this consultation.');
        }
        
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'case_type' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'deadline' => 'nullable|date',
            'opposing_party' => 'nullable|string|max:255',
            'opposing_counsel' => 'nullable|string|max:255',
            'is_confidential' => 'nullable|boolean'
        ]);
        
        // Create the legal case
        try {
            $legalCase = $consultation->createLegalCase($validated);
            
            // Redirect to the case details page
            return redirect()->route('lawyer.cases.show', $legalCase)
                ->with('success', 'Legal case created successfully.');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Failed to create legal case', [
                'error' => $e->getMessage(),
                'consultation_id' => $consultation->id
            ]);
            
            // Redirect back with error
            return redirect()->back()
                ->with('error', 'Failed to create legal case: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a legal case from a consultation.
     *
     * @param \App\Models\Consultation $consultation
     * @return \Illuminate\View\View
     */
    public function showCreateCaseForm(Consultation $consultation)
    {
        // Ensure the lawyer is authorized to create a case from this consultation
        if ($consultation->lawyer_id !== auth()->id()) {
            return redirect()->back()->with('error', 'You are not authorized to create a case from this consultation.');
        }
        
        // Check if the consultation is completed and can start a case
        if (!$consultation->is_completed) {
            return redirect()->back()->with('error', 'The consultation must be completed before creating a case.');
        }
        
        // Check if a case already exists for this consultation
        if ($consultation->case) {
            return redirect()->route('lawyer.cases.show', $consultation->case)
                ->with('info', 'A legal case already exists for this consultation.');
        }
        
        return view('lawyer.consultations.create-case', compact('consultation'));
    }
} 