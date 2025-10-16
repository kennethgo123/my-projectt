<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LegalCase;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function upload(LegalCase $case)
    {
        // Check if the authenticated user is the client of this case
        if ($case->client_id !== Auth::id()) {
            abort(403, 'You are not authorized to access this case.');
        }
        
        return view('livewire.shared.document-upload', ['case' => $case]);
    }
} 