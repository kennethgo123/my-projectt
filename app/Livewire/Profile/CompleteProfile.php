<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Models\LegalService;
use App\Models\ClientProfile;
use App\Models\LawyerProfile;
use App\Models\LawFirmProfile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompleteProfile extends Component
{
    use WithFileUploads;

    public $selectedServices = [];
    public $services = [];
    public $photo;
    
    // Common Fields for Both Lawyer and Law Firm
    public $first_name;
    public $middle_name;
    public $last_name;
    public $contact_number;
    public $address;
    public $city;
    public $valid_id_type;
    public $valid_id_file;
    public $bar_admission_type;
    public $bar_admission_file;
    public $min_budget;
    public $max_budget;
    public $pricing_description;
    
    // Law Firm Specific Fields
    public $firm_name;
    public $firm_contact_number;
    public $firm_address;
    public $firm_city;
    public $registration_type;
    public $registration_certificate_file;
    public $bir_certificate_file;

    protected function rules()
    {
        $rules = [];
        $userRoleId = auth()->user()->role_id;

        // Client role_id = 2
        if ($userRoleId == 2) {
            $rules = [
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'contact_number' => ['required', 'string', 'regex:/^9\d{9}$/'],
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'valid_id_type' => 'required|string|max:255',
                'valid_id_file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:8192',
                'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ];
        } 
        // Lawyer role_id = 3
        elseif ($userRoleId == 3) {
            $rules = [
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'contact_number' => ['required', 'string', 'regex:/^9\d{9}$/'],
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'valid_id_type' => 'required|string|max:255',
                'valid_id_file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:8192',
                'bar_admission_type' => 'required|string|max:255',
                'bar_admission_file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:8192',
                'min_budget' => 'required|numeric|min:0',
                'max_budget' => 'required|numeric|gt:min_budget',
                'pricing_description' => 'nullable|string|max:1000',
                'selectedServices' => 'required|array|min:1',
            ];
        } 
        // Law Firm role_id = 4
        elseif ($userRoleId == 4) {
            $rules = [
                'firm_name' => 'required|string|max:255',
                'firm_contact_number' => ['required', 'string', 'regex:/^9\d{9}$/'],
                'firm_address' => 'required|string|max:255',
                'firm_city' => 'required|string|in:Bacoor,Cavite City,Dasmarinas,General Trias,Imus,Tagaytay,Trece Martires',
                'registration_type' => 'required|string|in:SEC Registration Certificate,DTI Registration Certificate',
                'registration_certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:8192',
                'bir_certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx|max:8192',
                'min_budget' => 'required|numeric|min:0',
                'max_budget' => 'required|numeric|gt:min_budget',
                'pricing_description' => 'nullable|string|max:1000',
                'selectedServices' => 'required|array|min:1',
            ];
        }

        return $rules;
    }

    protected $messages = [
        'first_name.required' => 'The first name field is required.',
        'last_name.required' => 'The last name field is required.',
        'contact_number.required' => 'The contact number field is required.',
        'contact_number.regex' => 'The contact number must start with 9 and be 10 digits long (e.g., 9171234567).',
        'address.required' => 'The address field is required.',
        'city.required' => 'The city field is required.',
        'valid_id_type.required' => 'Please select a valid ID type.',
        'valid_id_file.required' => 'Please upload a file for your valid ID.',
        'valid_id_file.file' => 'The valid ID must be a file.',
        'valid_id_file.mimes' => 'The valid ID must be a file of type: pdf, jpg, jpeg, png, docx.',
        'valid_id_file.max' => 'The valid ID file may not be greater than 8MB.',
        'photo.image' => 'The profile photo must be an image.',
        'photo.mimes' => 'The profile photo must be a file of type: jpg, jpeg, png.',
        'photo.max' => 'The profile photo may not be greater than 2MB.',
        'bar_admission_type.required' => 'Please select a bar admission type.',
        'bar_admission_file.required' => 'Please upload your bar admission file.',
        'bar_admission_file.file' => 'The bar admission document must be a file.',
        'bar_admission_file.mimes' => 'The bar admission file must be of type: pdf, jpg, jpeg, png, docx.',
        'bar_admission_file.max' => 'The bar admission file may not be greater than 8MB.',
        'min_budget.required' => 'Minimum budget is required.',
        'min_budget.numeric' => 'Minimum budget must be a number.',
        'min_budget.min' => 'Minimum budget cannot be negative.',
        'max_budget.required' => 'Maximum budget is required.',
        'max_budget.numeric' => 'Maximum budget must be a number.',
        'max_budget.gt' => 'Maximum budget must be greater than the minimum budget.',
        'selectedServices.required' => 'Please select at least one legal service you offer.',
        'selectedServices.min' => 'Please select at least one legal service you offer.',
        'firm_name.required' => 'The law firm name is required.',
        'firm_contact_number.required' => 'The law firm contact number is required.',
        'firm_contact_number.regex' => 'The firm contact number must start with 9 and be 10 digits long (e.g., 9171234567).',
        'firm_address.required' => 'The law firm address is required.',
        'firm_city.required' => 'The law firm city is required.',
        'firm_city.in' => 'Please select a valid city within Cavite.',
        'registration_type.required' => 'Please select a registration type.',
        'registration_type.in' => 'Please select a valid registration type.',
        'registration_certificate_file.required' => 'Please upload your registration certificate.',
        'registration_certificate_file.file' => 'The registration certificate must be a file.',
        'registration_certificate_file.mimes' => 'The registration certificate must be of type: pdf, jpg, jpeg, png, docx.',
        'registration_certificate_file.max' => 'The registration certificate may not be greater than 8MB.',
        'bir_certificate_file.required' => 'Please upload your BIR certificate.',
        'bir_certificate_file.file' => 'The BIR certificate must be a file.',
        'bir_certificate_file.mimes' => 'The BIR certificate must be of type: pdf, jpg, jpeg, png, docx.',
        'bir_certificate_file.max' => 'The BIR certificate may not be greater than 8MB.',
    ];

    public function mount()
    {
        if (auth()->user()->profile_completed) {
            return redirect()->route('dashboard');
        }
        $this->services = LegalService::where('status', 'active')->get();
    }

    public function submit()
    {
        Log::info('SUBMIT METHOD CALLED', ['user_id' => auth()->id(), 'role_id' => auth()->user()->role_id]);
        
        // Add Debug - Check if files are being uploaded properly
        if (request()->hasFile('valid_id_file')) {
            Log::info('Valid ID file detected in request', [
                'original_name' => request()->file('valid_id_file')->getClientOriginalName(),
                'mime' => request()->file('valid_id_file')->getMimeType(),
                'size' => request()->file('valid_id_file')->getSize()
            ]);
        } else {
            Log::info('No valid_id_file in request');
        }
        // Check if Bar Admission file is in request (less likely with Livewire)
        if (request()->hasFile('bar_admission_file')) {
            Log::info('Bar Admission file detected in request', [
                'original_name' => request()->file('bar_admission_file')->getClientOriginalName(),
                'mime' => request()->file('bar_admission_file')->getMimeType(),
                'size' => request()->file('bar_admission_file')->getSize()
            ]);
        } else {
            Log::info('No bar_admission_file in request');
        }
        
        // Check Livewire property for Valid ID
        if ($this->valid_id_file && $this->valid_id_file instanceof \Illuminate\Http\UploadedFile) { // Ensure it's an UploadedFile
            Log::info('Valid ID file detected in Livewire property', [
                'filename' => $this->valid_id_file->getFilename(),
                'size' => $this->valid_id_file->getSize(),
                'mime' => $this->valid_id_file->getMimeType(),
                'exists' => file_exists($this->valid_id_file->getRealPath()), // Use getRealPath()
                'path' => $this->valid_id_file->getRealPath()
            ]);
        } else {
            Log::info('No valid_id_file (or not UploadedFile) in Livewire property');
        }
        
        // Check Livewire property for Bar Admission
        if ($this->bar_admission_file && $this->bar_admission_file instanceof \Illuminate\Http\UploadedFile) { // Ensure it's an UploadedFile
            Log::info('Bar Admission file detected in Livewire property', [
                'filename' => $this->bar_admission_file->getFilename(),
                'size' => $this->bar_admission_file->getSize(),
                'mime' => $this->bar_admission_file->getMimeType(),
                'exists' => file_exists($this->bar_admission_file->getRealPath()), // Use getRealPath()
                'path' => $this->bar_admission_file->getRealPath()
            ]);
        } else {
            Log::info('No bar_admission_file (or not UploadedFile) in Livewire property');
        }

        // Debug form inputs
        Log::info('Form data submitted:', [
            'valid_id_type' => $this->valid_id_type,
            'has_valid_id_file' => isset($this->valid_id_file) && $this->valid_id_file instanceof \Illuminate\Http\UploadedFile,
            'bar_admission_type' => $this->bar_admission_type,
            'has_bar_admission_file' => isset($this->bar_admission_file) && $this->bar_admission_file instanceof \Illuminate\Http\UploadedFile,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
        ]);
        
        Log::info('Profile submission started', [
            'user_id' => auth()->id(),
            'role_id' => auth()->user()->role_id,
            'submitted_data' => [
                'firm_name' => $this->firm_name,
                'firm_contact_number' => $this->firm_contact_number,
                'firm_address' => $this->firm_address,
                'firm_city' => $this->firm_city,
                'registration_type' => $this->registration_type,
                'has_registration_certificate' => isset($this->registration_certificate_file),
                'has_bir_certificate' => isset($this->bir_certificate_file),
                'min_budget' => $this->min_budget,
                'max_budget' => $this->max_budget,
                'selected_services' => $this->selectedServices
            ]
        ]);

        try {
            $validatedData = $this->validate();
            Log::info('Validation passed', ['validated_data' => array_keys($validatedData)]);

            DB::beginTransaction();
            Log::info('Starting database transaction');

            $userRoleId = auth()->user()->role_id;
            Log::info('Processing for role_id: ' . $userRoleId);
            
            // Prepend +63 before saving
            $formattedContactNumber = null;
            if (isset($validatedData['contact_number'])) {
                 $formattedContactNumber = '+63' . $validatedData['contact_number'];
            }
            $formattedFirmContactNumber = null;
             if (isset($validatedData['firm_contact_number'])) {
                 $formattedFirmContactNumber = '+63' . $validatedData['firm_contact_number'];
            }

            // Client role_id = 2
            if ($userRoleId == 2) {
                Log::info('Creating client profile');
                $validIdFileName = $this->storeFile($this->valid_id_file, 'valid-ids');
                
                $photoPath = null;
                if ($this->photo) {
                    $photoPath = $this->storeFile($this->photo, 'profile-photos');
                }
                
                ClientProfile::create([
                    'user_id' => auth()->id(),
                    'first_name' => $this->first_name,
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'contact_number' => $formattedContactNumber,
                    'address' => $this->address,
                    'city' => $this->city,
                    'valid_id_type' => $this->valid_id_type,
                    'valid_id_file' => $validIdFileName,
                    'photo_path' => $photoPath,
                ]);
                Log::info('Client profile created');

                auth()->user()->update([
                    'profile_completed' => true,
                    'status' => 'pending'
                ]);
                Log::info('User status updated to pending, awaiting admin approval');

                DB::commit();
                Log::info('Database transaction committed successfully');

                session()->flash('message', 'Profile completed successfully!');
                return redirect()->route('dashboard');
            } 
            // Lawyer role_id = 3
            elseif ($userRoleId == 3) {
                Log::info('Processing lawyer profile');

                try {
                    // Add more robust file handling with error checking for valid ID
                    try {
                        if (!$this->valid_id_file) {
                            throw new \Exception("Valid ID file is not set in the component property");
                        }
                        
                        // Additional file validation
                        if (!$this->valid_id_file->isValid()) {
                            throw new \Exception("The uploaded ID file is not valid: " . $this->valid_id_file->getErrorMessage());
                        }
                        
                        $validIdPath = $this->valid_id_file->store('valid_ids', 'public');
                        
                        if (!$validIdPath) {
                            throw new \Exception("ID file storage operation returned empty path");
                        }
                        
                        Log::info('Valid ID file stored at: ' . $validIdPath);
                    } catch (\Exception $fileException) {
                        Log::error('ID file upload error: ' . $fileException->getMessage(), [
                            'file' => $fileException->getFile(),
                            'line' => $fileException->getLine(),
                        ]);
                        
                        // Set a specific error message
                        session()->flash('upload_error', 'There was a problem uploading your ID: ' . $fileException->getMessage());
                        throw $fileException;
                    }
                    
                    // Add more robust file handling with error checking for bar admission
                    try {
                        if (!$this->bar_admission_file) {
                            throw new \Exception("Bar admission file is not set in the component property");
                        }
                        
                        // Additional file validation
                        if (!$this->bar_admission_file->isValid()) {
                            throw new \Exception("The uploaded bar admission file is not valid: " . $this->bar_admission_file->getErrorMessage());
                        }
                        
                        $barAdmissionPath = $this->bar_admission_file->store('bar_admissions', 'public');
                        
                        if (!$barAdmissionPath) {
                            throw new \Exception("Bar admission file storage operation returned empty path");
                        }
                        
                        Log::info('Bar admission file stored at: ' . $barAdmissionPath);
                    } catch (\Exception $fileException) {
                        Log::error('Bar admission file upload error: ' . $fileException->getMessage(), [
                            'file' => $fileException->getFile(),
                            'line' => $fileException->getLine(),
                        ]);
                        
                        // Set a specific error message
                        session()->flash('upload_error', 'There was a problem uploading your bar admission: ' . $fileException->getMessage());
                        throw $fileException;
                    }
                    
                    Log::info('Lawyer files stored', ['valid_id' => $validIdPath, 'bar_admission' => $barAdmissionPath]);

                    $lawyerProfile = LawyerProfile::create([
                        'user_id' => auth()->id(),
                        'first_name' => $this->first_name,
                        'middle_name' => $this->middle_name,
                        'last_name' => $this->last_name,
                        'contact_number' => $formattedContactNumber,
                        'address' => $this->address,
                        'city' => $this->city,
                        'valid_id_type' => $this->valid_id_type,
                        'valid_id_file' => $validIdPath,
                        'bar_admission_type' => $this->bar_admission_type,
                        'bar_admission_file' => $barAdmissionPath,
                        'min_budget' => $this->min_budget,
                        'max_budget' => $this->max_budget,
                        'pricing_description' => $this->pricing_description,
                    ]);
                    Log::info('Lawyer profile created', ['profile_id' => $lawyerProfile->id]);

                    $lawyerProfile->services()->attach($this->selectedServices);
                    Log::info('Attached services to lawyer profile', ['services' => $this->selectedServices]);

                    auth()->user()->update([
                        'profile_completed' => true,
                        'status' => 'pending'
                    ]);
                    Log::info('User status updated');

                    DB::commit();
                    Log::info('Database transaction committed successfully');

                    session()->flash('message', 'Profile completed successfully! Your account is now pending admin approval.');
                    return redirect()->route('profile.pending');
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Lawyer profile completion failed during transaction', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    session()->flash('error', 'An error occurred while completing your profile. Please try again.');
                }
            } 
            // Law Firm role_id = 4
            elseif ($userRoleId == 4) {
                Log::info('Processing law firm profile');
                
                try {
                    $registrationCertificatePath = $this->registration_certificate_file->store('firm_registrations', 'public');
                    $birCertificatePath = $this->bir_certificate_file->store('firm_bir_certificates', 'public');
                    Log::info('Law firm files stored', ['registration' => $registrationCertificatePath, 'bir' => $birCertificatePath]);

                    $lawFirmProfile = LawFirmProfile::create([
                        'user_id' => auth()->id(),
                        'firm_name' => $this->firm_name,
                        'contact_number' => $formattedFirmContactNumber,
                        'address' => $this->firm_address,
                        'city' => $this->firm_city,
                        'registration_type' => $this->registration_type,
                        'registration_certificate_file' => $registrationCertificatePath,
                        'bir_certificate_file' => $birCertificatePath,
                        'min_budget' => $this->min_budget,
                        'max_budget' => $this->max_budget,
                    ]);
                    Log::info('Law firm profile created', ['profile_id' => $lawFirmProfile->id]);

                    $lawFirmProfile->services()->attach($this->selectedServices);
                    Log::info('Attached services to law firm profile', ['services' => $this->selectedServices]);

                    auth()->user()->update([
                        'profile_completed' => true,
                        'status' => 'pending'
                    ]);
                    Log::info('User status updated');

                    DB::commit();
                    Log::info('Database transaction committed successfully');

                    session()->flash('message', 'Profile completed successfully! Your account is now pending admin approval.');
                    return redirect()->route('profile.pending');
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Law firm profile completion failed during transaction', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    session()->flash('error', 'An error occurred while completing your profile. Please try again.');
                }
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Profile submission validation failed', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Profile submission failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    public function render()
    {
        return view('livewire.profile.complete-profile', [
            'services' => LegalService::where('status', 'active')->get()
        ])->layout('layouts.guest');
    }

    /**
     * Listen for updated component properties.
     */
    public function updated($propertyName)
    {
        // Handle file uploads explicitly to ensure properties are set
        if ($propertyName === 'valid_id_file') {
            Log::info('updated hook: valid_id_file changed', ['has_file' => !is_null($this->valid_id_file)]);
            // No action needed beyond logging, Livewire handles the temp file
        }
        
        if ($propertyName === 'bar_admission_file') {
            Log::info('updated hook: bar_admission_file changed', ['has_file' => !is_null($this->bar_admission_file)]);
            // No action needed beyond logging
        }
        
        if ($propertyName === 'registration_certificate_file') {
            Log::info('updated hook: registration_certificate_file changed', ['has_file' => !is_null($this->registration_certificate_file)]);
            // No action needed beyond logging
        }
        
        if ($propertyName === 'bir_certificate_file') {
            Log::info('updated hook: bir_certificate_file changed', ['has_file' => !is_null($this->bir_certificate_file)]);
            // No action needed beyond logging
        }
        
        // Optional: Run validation on specific field update (can be noisy)
        // $this->validateOnly($propertyName);
    }

    /**
     * Helper method to store files
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return string|null
     */
    private function storeFile($file, $directory)
    {
        if (!$file) {
            return null;
        }
        
        try {
            if (!$file->isValid()) {
                throw new \Exception("The uploaded file is not valid: " . $file->getErrorMessage());
            }
            
            $path = $file->store($directory, 'public');
            
            if (!$path) {
                throw new \Exception("File storage operation returned empty path");
            }
            
            Log::info('File stored at: ' . $path);
            return $path;
        } catch (\Exception $fileException) {
            Log::error('File upload error: ' . $fileException->getMessage(), [
                'file' => $fileException->getFile(),
                'line' => $fileException->getLine(),
            ]);
            
            session()->flash('upload_error', 'There was a problem uploading your file: ' . $fileException->getMessage());
            throw $fileException;
        }
    }
} 