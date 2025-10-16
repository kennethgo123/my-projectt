<?php

namespace App\Livewire\LawFirm;

use App\Models\LawFirmLawyer;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManageLawyers extends Component
{
    use WithFileUploads, WithPagination;

    public $firstName = '';
    public $middleName = '';
    public $lastName = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $contactNumber = '';
    public $address = '';
    public $city = '';
    public $validIdType = '';
    public $validIdFile;
    public $barAdmissionType = '';
    public $barAdmissionFile;
    public $showForm = false;
    public $selectedServices = [];
    public $availableServices = [];

    protected $rules = [
        'firstName' => ['required', 'string', 'max:255'],
        'middleName' => ['nullable', 'string', 'max:255'],
        'lastName' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'contactNumber' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
        'address' => ['required', 'string'],
        'city' => ['required', 'in:Cavite City,Dasmarinas,General Trias,Imus,Tagaytay,Trece Martires,Bacoor'],
        'validIdType' => ['required', 'in:Philippine Passport,PhilSys National ID,SSS ID,GSIS ID,UMID,Drivers License,PRC ID,Postal ID,Voters ID,PhilHealth ID,NBI Clearance'],
        'validIdFile' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        'barAdmissionType' => ['required', 'in:Bar Admission Id,Bar Admission Certificate'],
        'barAdmissionFile' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        'selectedServices' => ['required', 'array', 'min:1'],
    ];

    public function mount()
    {
        $lawFirmProfile = auth()->user()->lawFirmProfile;
        if ($lawFirmProfile) {
            $lawFirmProfile->load('services');
            $this->availableServices = $lawFirmProfile->services;
        }
    }

    public function showAddLawyerForm()
    {
        $this->showForm = true;
        $this->reset(['firstName', 'middleName', 'lastName', 'email', 'password', 'password_confirmation', 
                     'contactNumber', 'address', 'city', 'validIdType', 'validIdFile', 
                     'barAdmissionType', 'barAdmissionFile', 'selectedServices']);
    }

    public function addLawyer()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $validIdPath = $this->validIdFile->store('valid-ids', 'public');
            $barAdmissionPath = $this->barAdmissionFile->store('bar-admissions', 'public');

            // Get the law firm profile to access budget info
            $lawFirmProfile = auth()->user()->lawFirmProfile;
            if (!$lawFirmProfile) {
                throw new \Exception('Law firm profile not found');
            }

            // Find the Lawyer Role
            $lawyerRole = Role::where('name', 'lawyer')->firstOrFail();

            // Create user account for the lawyer
            $user = User::create([
                'name' => trim($this->firstName . ' ' . ($this->middleName ? $this->middleName . ' ' : '') . $this->lastName),
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role_id' => $lawyerRole->id,
                'status' => 'pending',
                'firm_id' => auth()->id(),
                'profile_completed' => 1
            ]);

            // Create law firm lawyer record with firm's budget values
            $lawFirmLawyer = LawFirmLawyer::create([
                'law_firm_profile_id' => $lawFirmProfile->id,
                'user_id' => $user->id,
                'first_name' => $this->firstName,
                'middle_name' => $this->middleName,
                'last_name' => $this->lastName,
                'contact_number' => '+63' . $this->contactNumber,
                'address' => $this->address,
                'city' => $this->city,
                'valid_id_type' => $this->validIdType,
                'valid_id_file' => $validIdPath,
                'bar_admission_type' => $this->barAdmissionType,
                'bar_admission_file' => $barAdmissionPath,
                'min_budget' => $lawFirmProfile->min_budget,
                'max_budget' => $lawFirmProfile->max_budget
            ]);
            
            // Attach selected services to the lawyer
            if (!empty($this->selectedServices)) {
                $lawFirmLawyer->services()->attach($this->selectedServices);
            }

            DB::commit();

            $this->reset(['firstName', 'middleName', 'lastName', 'email', 'password', 'password_confirmation', 
                         'contactNumber', 'address', 'city', 'validIdType', 'validIdFile', 
                         'barAdmissionType', 'barAdmissionFile', 'selectedServices', 'showForm']);
            
            session()->flash('message', 'Lawyer added successfully. They are now pending for approval.');
        } catch (\Exception $e) {
            // Clean up uploaded files if there's an error
            if (isset($validIdPath)) {
                Storage::disk('public')->delete($validIdPath);
            }
            if (isset($barAdmissionPath)) {
                Storage::disk('public')->delete($barAdmissionPath);
            }
            
            DB::rollBack();
            session()->flash('error', 'Failed to add lawyer. Please try again.');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.law-firm.manage-lawyers', [
            'lawyers' => LawFirmLawyer::where('law_firm_profile_id', auth()->user()->lawFirmProfile->id)
                ->with('services')
                ->paginate(10),
            'cities' => [
                'Cavite City',
                'Dasmarinas',
                'General Trias',
                'Imus',
                'Tagaytay',
                'Trece Martires',
                'Bacoor'
            ],
            'validIdTypes' => [
                'Philippine Passport',
                'PhilSys National ID',
                'SSS ID',
                'GSIS ID',
                'UMID',
                'Drivers License',
                'PRC ID',
                'Postal ID',
                'Voters ID',
                'PhilHealth ID',
                'NBI Clearance'
            ],
        ]);
    }
} 