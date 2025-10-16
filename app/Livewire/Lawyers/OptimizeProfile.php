<?php

namespace App\Livewire\Lawyers;

use Livewire\Component;
use App\Models\LawyerProfile;
use App\Models\LegalService;
use Livewire\WithFileUploads;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OptimizeProfile extends Component
{
    use WithFileUploads;

    public $about = '';
    public $education = '';
    public $experience = '';
    public $achievements = '';
    public $languages = [];
    public $photo;
    public $existingPhoto;
    public $croppedPhoto;
    public $offersOnlineConsultation = false;
    public $offersInhouseConsultation = false;
    public $availableServices = [];
    public $selectedServices = [];
    public $isLawFirmLawyer = false;
    public $office_address = '';
    public $lat = null;
    public $lng = null;
    public $show_office_address = false;

    public function mount()
    {
        $user = auth()->user();
        
        // Check if this is a law firm lawyer
        if ($user->lawFirmLawyer) {
            $this->isLawFirmLawyer = true;
            $profile = $user->lawFirmLawyer;
            $this->about = $profile->about ?? '';
            $this->education = $profile->education ?? '';
            $this->experience = $profile->experience ?? '';
            $this->achievements = $profile->achievements ?? '';
            $this->languages = $profile->languages ? (is_array($profile->languages) ? $profile->languages : json_decode($profile->languages, true)) : [];
            $this->existingPhoto = $profile->photo_path;
            $this->offersOnlineConsultation = $profile->offers_online_consultation ?? false;
            $this->offersInhouseConsultation = $profile->offers_inhouse_consultation ?? false;
            $this->office_address = $profile->office_address ?? '';
            $this->lat = $profile->lat;
            $this->lng = $profile->lng;
            $this->show_office_address = $profile->show_office_address ?? false;
            
            // Load the law firm's available services
            if ($profile->lawFirm) {
                $this->availableServices = $profile->lawFirm->services;
            } else {
                $this->availableServices = LegalService::active()->get();
            }
            
            // Get the lawyer's selected services
            $profile->load('services');
            $this->selectedServices = $profile->services->pluck('id')->toArray();
            
            return;
        }
        
        // Regular lawyer profile
        $profile = $user->lawyerProfile;
        if ($profile) {
            $this->about = $profile->about;
            $this->education = $profile->education;
            $this->experience = $profile->experience;
            $this->achievements = $profile->achievements;
            $this->languages = is_string($profile->languages) ? json_decode($profile->languages, true) : ($profile->languages ?? []);
            $this->existingPhoto = $profile->photo_path;
            $this->offersOnlineConsultation = $profile->offers_online_consultation ?? false;
            $this->offersInhouseConsultation = $profile->offers_inhouse_consultation ?? false;
            $this->office_address = $profile->office_address ?? '';
            $this->lat = $profile->lat;
            $this->lng = $profile->lng;
            $this->show_office_address = $profile->show_office_address ?? false;
            
            // Load available services
            $this->availableServices = LegalService::active()->get();
            
            // Get the lawyer's selected services
            $profile->load('services');
            $this->selectedServices = $profile->services->pluck('id')->toArray();
        }
    }

    public function updatedPhoto()
    {
        Log::info('Photo uploaded, validating...');
        
        $this->validate([
            'photo' => [
                'required',
                'image',
                'max:8192', // 8MB
                'mimes:jpeg,png,webp,heic',
            ],
        ]);
        
        Log::info('Photo validation passed, dispatching photo-selected event');
        
        // Get the temporary URL for the uploaded photo
        if ($this->photo) {
            try {
                $photoUrl = $this->photo->temporaryUrl();
                Log::info('Photo temporary URL generated: ' . $photoUrl);
                
                // Dispatch the event with the photo URL
                $this->dispatch('photo-selected', ['photoUrl' => $photoUrl]);
            } catch (\Exception $e) {
                Log::error('Error generating temporary URL: ' . $e->getMessage());
            }
        } else {
            Log::warning('No photo found after validation');
        }
    }

    public function cropPhoto($croppedData)
    {
        Log::info('cropPhoto method called');
        
        try {
            if (empty($croppedData)) {
                Log::warning('Received empty cropped data');
                return;
            }
            
            Log::info('Received cropped photo data: ' . substr($croppedData, 0, 50) . '...');
            $this->croppedPhoto = $croppedData;
            Log::info('Cropped photo data saved to component');
        } catch (\Exception $e) {
            Log::error('Error in cropPhoto: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $user = auth()->user();
        $profile = null;
        $photoPath = null;

        // Process photo if provided
        if ($this->photo && !$this->croppedPhoto) {
            try {
                Log::info('Processing uploaded photo directly (bypassing cropper)...');
                
                // Make sure the profile-photos directory exists
                if (!Storage::disk('public')->exists('profile-photos')) {
                    Storage::disk('public')->makeDirectory('profile-photos');
                    Log::info('Created profile-photos directory');
                }
                
                $filename = 'profile-photos/' . uniqid() . '.webp';
                $fullPath = storage_path('app/public/' . $filename);
                
                // Get the temp path of the uploaded file
                $tempPath = $this->photo->getRealPath();
                Log::info('Processing photo from temp path: ' . $tempPath);
                
                // Process with Intervention Image
                $manager = new ImageManager(new Driver());
                $image = $manager->read($tempPath);
                $image->toWebp(80)->save($fullPath);
                
                if (!file_exists($fullPath)) {
                    Log::error('Failed to save image to path: ' . $fullPath);
                    throw new \Exception('Failed to save image file');
                }
                
                // Delete old photo if it exists
                if ($this->existingPhoto) {
                    try {
                        $oldPath = storage_path('app/public/' . $this->existingPhoto);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                            Log::info('Deleted old photo: ' . $oldPath);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Could not delete old photo: ' . $e->getMessage());
                    }
                }
                
                $photoPath = $filename;
                Log::info('Photo directly processed and saved to: ' . $photoPath);
            } catch (\Exception $e) {
                Log::error('Error processing direct photo: ' . $e->getMessage());
                session()->flash('error', 'Error processing photo: ' . $e->getMessage());
            }
        } 
        // Process photo if provided as cropped data
        else if ($this->croppedPhoto) {
            try {
                Log::info('Processing cropped photo...');
                
                // Make sure the profile-photos directory exists
                if (!Storage::disk('public')->exists('profile-photos')) {
                    Storage::disk('public')->makeDirectory('profile-photos');
                    Log::info('Created profile-photos directory');
                }
                
                // Process and save the cropped photo
                $manager = new ImageManager(new Driver());
                
                // Create image from base64 data
                $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $this->croppedPhoto);
                if (empty($base64Data)) {
                    Log::error('Empty base64 data after regex');
                    throw new \Exception('Invalid image data received');
                }
                
                $croppedData = base64_decode($base64Data);
                if (!$croppedData) {
                    Log::error('Failed to decode base64 data');
                    throw new \Exception('Failed to decode image data');
                }
                
                $image = $manager->read($croppedData);
                
                // Generate unique filename
                $filename = 'profile-photos/' . uniqid() . '.webp';
                $fullPath = storage_path('app/public/' . $filename);
                
                // Store the image
                $image->toWebp(80)->save($fullPath);
                
                // Verify the image was created
                if (!file_exists($fullPath)) {
                    Log::error('Failed to save image to path: ' . $fullPath);
                    throw new \Exception('Failed to save image file');
                }
                
                // Delete old photo if it exists
                if ($this->existingPhoto) {
                    try {
                        $oldPath = storage_path('app/public/' . $this->existingPhoto);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                            Log::info('Deleted old photo: ' . $oldPath);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Could not delete old photo: ' . $e->getMessage());
                        // Continue anyway, this is not critical
                    }
                }
                
                $photoPath = $filename;
                Log::info('Photo saved successfully to: ' . $photoPath);
            } catch (\Exception $e) {
                Log::error('Error saving photo: ' . $e->getMessage());
                session()->flash('error', 'Error saving photo: ' . $e->getMessage());
            }
        }

        // Prepare update data
        $updateData = [
            'about' => $this->about,
            'education' => $this->education,
            'experience' => $this->experience,
            'achievements' => $this->achievements,
            'languages' => json_encode($this->languages),
            'offers_online_consultation' => $this->offersOnlineConsultation,
            'offers_inhouse_consultation' => $this->offersInhouseConsultation,
            'is_optimized' => true,
            'office_address' => $this->office_address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'show_office_address' => $this->show_office_address,
        ];
        
        if ($photoPath) {
            $updateData['photo_path'] = $photoPath;
            Log::info('Added photo_path to update data: ' . $photoPath);
        } elseif ($this->existingPhoto && !$this->photo && !$this->croppedPhoto) {
            $updateData['photo_path'] = $this->existingPhoto;
            Log::info('Retaining existing photo_path: ' . $this->existingPhoto);
        }
        
        $success = false;
        
        // Update law firm lawyer profile
        if ($user->lawFirmLawyer) {
            Log::info('Updating law firm lawyer profile');
            $success = $user->lawFirmLawyer->update($updateData);
            
            // Update services
            if ($success) {
                $user->lawFirmLawyer->services()->sync($this->selectedServices);
                Log::info('Updated law firm lawyer services: ' . implode(', ', $this->selectedServices));
                
                if ($photoPath) {
                    $this->existingPhoto = $photoPath; // Update existingPhoto with the new path
                }
            }
        } 
        // Update regular lawyer profile
        else if ($user->lawyerProfile) {
            Log::info('Updating regular lawyer profile');
            $success = $user->lawyerProfile->update($updateData);
        
            // Update services
            if ($success) {
                $user->lawyerProfile->services()->sync($this->selectedServices);
                Log::info('Updated lawyer services: ' . implode(', ', $this->selectedServices));
                
                if ($photoPath) {
                    $this->existingPhoto = $photoPath; // Update existingPhoto with the new path
                }
            }
        } else {
            Log::error('User does not have a lawyer profile');
            session()->flash('error', 'Profile not found');
            return;
        }

        if ($success) {
            session()->flash('message', 'Profile optimized successfully!');
            // Use both a dispatch for Livewire and a direct browser event
            $this->dispatch('profile-optimized');
            // Also dispatch a browser event for more compatibility
            $this->dispatch('profile-optimized-js', ['scrollToTop' => true]);
        } else {
            session()->flash('error', 'Failed to update profile. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.lawyers.optimize-profile')
            ->layout('layouts.app');
    }
} 