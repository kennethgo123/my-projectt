<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Consultation;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the client's consultations.
     */
    public function index()
    {
        $consultations = Consultation::where('client_id', auth()->id())
            ->with(['lawyer'])
            ->latest()
            ->get();
            
        return view('client.consultations.index', [
            'consultations' => $consultations
        ]);
    }
}
