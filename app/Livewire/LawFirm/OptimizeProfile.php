<?php

namespace App\Livewire\LawFirm;

use App\Models\LawFirmProfile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OptimizeProfile extends Component
{
    use WithFileUploads;

    public $about = '';
    public $experience = '';
    public $achievements = '';
    public $languages = [];
    public $photo;
    public $existingPhoto;
    public $croppedPhoto;
    public $offersOnlineConsultation = false;
    public $offersInhouseConsultation = false;
    public $office_address = '';
    public $lat = null;
    public $lng = null;
    public $show_office_address = false;

    public function mount()
    {
        $profile = auth()->user()->lawFirmProfile;
        if ($profile) {
            $this->about = $profile->about;
            $this->experience = $profile->experience;
            $this->achievements = $profile->achievements;
            
            if ($profile->languages) {
                $this->languages = is_array($profile->languages) ? 
                    $profile->languages : 
                    (json_decode($profile->languages, true) ?? []);
            }
            
            $this->existingPhoto = $profile->photo_path;
            $this->offersOnlineConsultation = $profile->offers_online_consultation ?? false;
            $this->offersInhouseConsultation = $profile->offers_inhouse_consultation ?? false;
            $this->office_address = $profile->office_address ?? '';
            $this->lat = $profile->lat;
            $this->lng = $profile->lng;
            $this->show_office_address = $profile->show_office_address ?? false;
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
        
        if ($this->photo) {
            try {
                $photoUrl = $this->photo->temporaryUrl();
                Log::info('Photo temporary URL generated: ' . $photoUrl);
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
                $this->dispatch('photo-crop-error', ['message' => 'Received empty image data']);
                return;
            }
            
            Log::info('Received cropped photo data: ' . substr($croppedData, 0, 50) . '...');
            
            // Validate the data is actually a base64 image
            if (!preg_match('/^data:image\/(\w+);base64,/', $croppedData)) {
                Log::error('Invalid image format received: ' . substr($croppedData, 0, 30));
                $this->dispatch('photo-crop-error', ['message' => 'Invalid image format']);
                return;
            }
            
            $this->croppedPhoto = $croppedData;
            Log::info('Cropped photo data saved to component property $this->croppedPhoto');
            
            // Send success message back to frontend
            $this->dispatch('photo-cropped', ['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error in cropPhoto: ' . $e->getMessage());
            $this->dispatch('photo-crop-error', ['message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function save()
    {
        $user = auth()->user();
        $profile = $user->lawFirmProfile;
        $photoPath = null;

        // NEW APPROACH: Try to directly process the photo without relying on cropper
        if ($this->photo && !$this->croppedPhoto) {
            try {
                Log::info('Directly processing uploaded photo (bypassing cropper)...');
                
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
                return;
            }
        }
        // Original cropped photo processing - still keep this as fallback
        else if ($this->croppedPhoto) {
            try {
                Log::info('Processing cropped photo...');
                
                if (!Storage::disk('public')->exists('profile-photos')) {
                    Storage::disk('public')->makeDirectory('profile-photos');
                    Log::info('Created profile-photos directory');
                }
                
                $manager = new ImageManager(new Driver());
                
                $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $this->croppedPhoto);
                if (empty($base64Data)) {
                    Log::error('Empty base64 data after regex');
                    throw new \Exception('Invalid image data received');
                }
                
                $decodedData = base64_decode($base64Data);
                if (!$decodedData) {
                    Log::error('Failed to decode base64 data');
                    throw new \Exception('Failed to decode image data');
                }
                
                Log::info('Successfully decoded base64 data');
                
                try {
                    $image = $manager->read($decodedData);
                    Log::info('Successfully created image from decoded data');
                } catch (\Exception $e) {
                    Log::error('Failed to create image from decoded data: ' . $e->getMessage());
                    throw new \Exception('Failed to process image data');
                }
                
                $filename = 'profile-photos/' . uniqid() . '.webp';
                $fullPath = storage_path('app/public/' . $filename);
                
                try {
                    $image->toWebp(80)->save($fullPath);
                    Log::info('Successfully saved image to: ' . $fullPath);
                } catch (\Exception $e) {
                    Log::error('Failed to save image: ' . $e->getMessage());
                    throw new \Exception('Failed to save image file');
                }
                
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
                session()->flash('error', 'There was an error processing the image: ' . $e->getMessage());
                return;
            }
        }

        $updateData = [
            'about' => $this->about,
            'experience' => $this->experience,
            'achievements' => $this->achievements,
            'languages' => json_encode($this->languages),
            'is_optimized' => true,
            'offers_online_consultation' => $this->offersOnlineConsultation,
            'offers_inhouse_consultation' => $this->offersInhouseConsultation,
            'office_address' => $this->office_address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'show_office_address' => $this->show_office_address,
        ];
        
        if ($photoPath) {
            $updateData['photo_path'] = $photoPath;
            Log::info('Adding new photo_path to update data: ' . $photoPath);
        } elseif ($this->existingPhoto && !$this->photo && !$this->croppedPhoto) {
            $updateData['photo_path'] = $this->existingPhoto;
            Log::info('Retaining existing photo_path: ' . $this->existingPhoto);
        } else {
            Log::info('No photo provided and no existing photo to retain');
        }

        $success = false;
        if ($profile) {
            Log::info('Updating law firm profile with data: ', array_keys($updateData));
            $success = $profile->update($updateData);
        } else {
            Log::error('User does not have a law firm profile');
            session()->flash('error', 'Profile not found');
            return;
        }

        if ($success) {
             if ($photoPath) {
                $this->existingPhoto = $photoPath; // Update existingPhoto with the new path
                Log::info('Updated existingPhoto property to: ' . $photoPath);
             }
            session()->flash('message', 'Profile optimized successfully!');
            // Use both a dispatch for Livewire and a direct browser event
            $this->dispatch('profile-optimized');
            // Also dispatch a browser event for more compatibility
            $this->dispatch('profile-optimized-js', ['scrollToTop' => true]);
        } else {
            // Check if the error flash was already set by photo processing
            if (!session()->has('error')) {
                session()->flash('error', 'Failed to update profile. Please try again.');
            }
        }
    }

    public function render()
    {
        return view('livewire.law-firm.optimize-profile');
    }
}
