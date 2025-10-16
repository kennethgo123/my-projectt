<?php

namespace App\Http\Controllers\LawFirm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LawyerProfile;
use App\Models\Service;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LawyerController extends Controller
{
    public function create()
    {
        $firmUser = Auth::user();

        // Check if user is a law firm
        if (!$firmUser || !$firmUser->isLawFirm()) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load the law firm profile and its services
        $firmUser->load('lawFirmProfile.services');

        // Get the services from the loaded relationship
        $services = $firmUser->lawFirmProfile ? $firmUser->lawFirmProfile->services : collect();

        if ($services->isEmpty()) {
            // Optionally, redirect or show a message if the firm has no services defined
            // return redirect()->route('some.route')->with('warning', 'Please define your firm\'s services first.');
        }

        return view('law-firm.lawyers.create', compact('services'));
    }

    public function store(Request $request)
    {
        Log::info('LawyerController@store method called');

        // Check if user is a law firm
        if (!auth()->check() || !auth()->user()->role || !auth()->user()->isLawFirm()) {
            Log::warning('Unauthorized access attempt to LawyerController@store');
            abort(403, 'Unauthorized action.');
        }

        // Get the law firm's profile
        $firmProfile = auth()->user()->lawFirmProfile;
        if (!$firmProfile) {
            Log::error('Law firm profile not found');
            return back()->with('error', 'Law firm profile not found. Please complete your profile first.')->withInput();
        }

        Log::info('Validating lawyer creation request', ['data' => $request->all()]);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'contact_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'valid_id_type' => 'required|string',
            'valid_id_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'bar_admission_type' => 'required|string',
            'bar_admission_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:legal_services,id'
        ], [
            'first_name.required' => 'The first name field is required.',
            'last_name.required' => 'The last name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered in our system.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'contact_number.required' => 'The contact number field is required.',
            'address.required' => 'The address field is required.',
            'valid_id_type.required' => 'Please select a valid ID type.',
            'valid_id_file.required' => 'Please upload a valid ID file.',
            'valid_id_file.mimes' => 'The valid ID file must be a PDF, JPG, JPEG, or PNG.',
            'bar_admission_type.required' => 'Please select a bar admission type.',
            'bar_admission_file.required' => 'Please upload your bar admission file.',
            'bar_admission_file.mimes' => 'The bar admission file must be a PDF, JPG, JPEG, or PNG.',
            'services.required' => 'Please select at least one service.',
            'services.min' => 'Please select at least one service.',
        ]);

        Log::info('Validation passed, proceeding with lawyer creation');

        try {
            DB::beginTransaction();
            Log::info('Starting database transaction');

            // Find the Lawyer Role ID
            $lawyerRole = Role::where('name', 'lawyer')->first();
            if (!$lawyerRole) {
                Log::error('Lawyer role not found in database');
                DB::rollBack();
                return back()->with('error', 'Lawyer role not found. Please contact administrator.')->withInput();
            }

            // Create the user
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $lawyerRole->id,
                'status' => 'pending',
                'firm_id' => auth()->user()->id
            ]);

            Log::info('User created successfully', ['user_id' => $user->id]);

            // Store files
            $validIdPath = $request->file('valid_id_file')->store('valid-ids', 'public');
            $barAdmissionPath = $request->file('bar_admission_file')->store('bar-admissions', 'public');

            Log::info('Files stored successfully', [
                'valid_id_path' => $validIdPath,
                'bar_admission_path' => $barAdmissionPath
            ]);

            // Create lawyer profile
            $lawyerProfile = LawyerProfile::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'contact_number' => $request->contact_number,
                'address' => $request->address,
                'valid_id_type' => $request->valid_id_type,
                'valid_id_file' => $validIdPath,
                'bar_admission_type' => $request->bar_admission_type,
                'bar_admission_file' => $barAdmissionPath,
                'min_budget' => 0, // Set default value
                'max_budget' => 0, // Set default value
            ]);

            Log::info('Lawyer profile created successfully', ['profile_id' => $lawyerProfile->id]);

            // Attach services
            $lawyerProfile->services()->attach($request->services);

            Log::info('Services attached successfully');

            DB::commit();
            Log::info('Transaction committed successfully');

            return redirect()->route('law-firm.lawyers')->with('success', 'Lawyer added successfully. They will be able to access the system once approved by the admin.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add lawyer: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            return back()->with('error', 'Failed to add lawyer. Please try again.')->withInput();
        }
    }
} 