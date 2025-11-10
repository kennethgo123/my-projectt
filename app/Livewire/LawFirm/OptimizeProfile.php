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
    public $defaultLanguages = [
        'English',
        'Filipino (Tagalog)',
        'Cebuano',
        'Ilocano',
        'Waray',
        'Kapampangan',
        'Pangasinan',
    ];
    public $showAddLanguageInput = false;
    public $newLanguage = '';
    public $photo;
    public $existingPhoto;
    public $croppedPhoto;
    public $offersOnlineConsultation = false;
    public $offersInhouseConsultation = false;
    public $office_address = '';
    public $city = '';
    public $barangay = '';
    public $barangays = [];
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
            $this->city = $profile->city ?? '';
            $this->barangay = $profile->barangay ?? '';
            $this->barangays = $this->getBarangaysByCity($this->city);
            $this->lat = $profile->lat;
            $this->lng = $profile->lng;
            $this->show_office_address = $profile->show_office_address ?? false;
        }
    }

    public function updatedCity()
    {
        $this->barangay = '';
        $this->barangays = $this->getBarangaysByCity($this->city);
    }

    public function toggleLanguage(string $language): void
    {
        $language = trim($language);
        $index = array_search($language, $this->languages, true);
        if ($index === false) {
            $this->languages[] = $language;
        } else {
            unset($this->languages[$index]);
            $this->languages = array_values($this->languages);
        }
    }

    public function addLanguage(): void
    {
        if (!empty($this->newLanguage)) {
            $this->languages[] = trim($this->newLanguage);
            $this->newLanguage = '';
            $this->showAddLanguageInput = false;
        }
    }

    public function removeLanguage(int $index): void
    {
        unset($this->languages[$index]);
        $this->languages = array_values($this->languages);
    }

    private function getBarangaysByCity(string $city): array
    {
        $map = [
            // Alfonso
            'Alfonso' => [
                'Amuyong','Barangay I','Barangay II','Barangay III','Barangay IV','Barangay V','Bilog','Buck Estate',
                'Esperanza Ibaba','Esperanza Ilaya','Kaysuyo','Kaytitinga I','Kaytitinga II','Kaytitinga III',
                'Luksuhin','Luksuhin Ilaya','Mangas I','Mangas II','Marahan I','Marahan II','Matagbak I','Matagbak II',
                'Pajo','Palumlum','Santa Teresa','Sikat','Sinaliw Malaki','Sinaliw na Munti','Sulsugin',
                'Taywanak Ibaba','Taywanak Ilaya','Upli',
            ],
            // Amadeo
            'Amadeo' => [
                'Banaybanay','Barangay I','Barangay II','Barangay III','Barangay IV','Barangay IX','Barangay V',
                'Barangay VI','Barangay VII','Barangay VIII','Barangay X','Barangay XI','Barangay XII','Bucal','Buho',
                'Dagatan','Halang','Loma','Maitim I','Maymangga','Minantok Kanluran','Minantok Silangan','Pangil',
                'Salaban','Talon','Tamacan',
            ],
            // Bacoor (City of Bacoor)
            'City of Bacoor' => [
                'Alima','Aniban I','Aniban II','Aniban III','Aniban IV','Aniban V','Banalo','Bayanan','Campo Santo',
                'Daang Bukid','Digman','Dulong Bayan','Habay I','Habay II','Kaingin','Ligas I','Ligas II','Ligas III',
                'Mabolo I','Mabolo II','Mabolo III','Maliksi I','Maliksi II','Maliksi III','Mambog I','Mambog II',
                'Mambog III','Mambog IV','Mambog V','Molino I','Molino II','Molino III','Molino IV','Molino V',
                'Molino VI','Molino VII','Niog I','Niog II','Niog III','P. F. Espiritu I','P. F. Espiritu II',
                'P. F. Espiritu III','P. F. Espiritu IV','P. F. Espiritu V','P. F. Espiritu VI','P. F. Espiritu VII',
                'P. F. Espiritu VIII','Queens Row Central','Queens Row East','Queens Row West','Real I','Real II',
                'Salinas I','Salinas II','Salinas III','Salinas IV','San Nicolas I','San Nicolas II','San Nicolas III',
                'Sineguelasan','Tabing Dagat','Talaba I','Talaba II','Talaba III','Talaba IV','Talaba V','Talaba VI',
                'Talaba VII','Zapote I','Zapote II','Zapote III','Zapote IV','Zapote V',
            ],
            // City of Carmona
            'City of Carmona' => [
                'Bancal','Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Barangay 6','Barangay 7',
                'Barangay 8','Cabilang Baybay','Lantic','Mabuhay','Maduya','Milagrosa',
            ],
            // Cavite City
            'Cavite City' => [
                'Barangay 1','Barangay 10','Barangay 10-A','Barangay 10-B','Barangay 11','Barangay 12','Barangay 13',
                'Barangay 14','Barangay 15','Barangay 16','Barangay 17','Barangay 18','Barangay 19','Barangay 2',
                'Barangay 20','Barangay 21','Barangay 22','Barangay 22-A','Barangay 23','Barangay 24','Barangay 25',
                'Barangay 26','Barangay 27','Barangay 28','Barangay 29','Barangay 29-A','Barangay 3','Barangay 30',
                'Barangay 31','Barangay 32','Barangay 33','Barangay 34','Barangay 35','Barangay 36','Barangay 36-A',
                'Barangay 37','Barangay 37-A','Barangay 38','Barangay 38-A','Barangay 39','Barangay 4','Barangay 40',
                'Barangay 41','Barangay 42','Barangay 42-A','Barangay 42-B','Barangay 42-C','Barangay 43','Barangay 44',
                'Barangay 45','Barangay 45-A','Barangay 46','Barangay 47','Barangay 47-A','Barangay 47-B',
                'Barangay 48','Barangay 48-A','Barangay 49','Barangay 49-A','Barangay 5','Barangay 50','Barangay 51',
                'Barangay 52','Barangay 53','Barangay 53-A','Barangay 53-B','Barangay 54','Barangay 54-A','Barangay 55',
                'Barangay 56','Barangay 57','Barangay 58','Barangay 58-A','Barangay 59','Barangay 6','Barangay 60',
                'Barangay 61','Barangay 61-A','Barangay 62','Barangay 62-A','Barangay 62-B','Barangay 7','Barangay 8',
                'Barangay 9',
            ],
            // City of Dasmariñas
            'City of Dasmariñas' => [
                'Burol','Burol I','Burol II','Burol III','Datu Esmael','Emmanuel Bergado I','Emmanuel Bergado II',
                'Fatima I','Fatima II','Fatima III','H-2','Langkaan I','Langkaan II','Luzviminda I','Luzviminda II',
                'Paliparan I','Paliparan II','Paliparan III','Sabang','Saint Peter I','Saint Peter II','Salawag',
                'Salitran I','Salitran II','Salitran III','Salitran IV','Sampaloc I','Sampaloc II','Sampaloc III',
                'Sampaloc IV','Sampaloc V','San Agustin I','San Agustin II','San Agustin III','San Andres I',
                'San Andres II','San Antonio de Padua I','San Antonio de Padua II','San Dionisio','San Esteban',
                'San Francisco I','San Francisco II','San Isidro Labrador I','San Isidro Labrador II','San Jose',
                'San Juan','San Lorenzo Ruiz I','San Lorenzo Ruiz II','San Luis I','San Luis II','San Manuel I',
                'San Manuel II','San Mateo','San Miguel','San Miguel II','San Nicolas I','San Nicolas II','San Roque',
                'San Simon','Santa Cristina I','Santa Cristina II','Santa Cruz I','Santa Cruz II','Santa Fe',
                'Santa Lucia','Santa Maria','Santo Cristo','Santo Niño I','Santo Niño II','Victoria Reyes',
                'Zone I','Zone I-B','Zone II','Zone III','Zone IV',
            ],
            // General Emilio Aguinaldo
            'General Emilio Aguinaldo' => [
                'A. Dalusag','Batas Dao','Castaños Cerca','Castaños Lejos','Kabulusan','Kaymisas','Kaypaaba','Lumipa',
                'Narvaez','Poblacion I','Poblacion II','Poblacion III','Poblacion IV','Tabora',
            ],
            // General Mariano Alvarez
            'General Mariano Alvarez' => [
                'Aldiano Olaes','Barangay 1 Poblacion','Barangay 2 Poblacion','Barangay 3 Poblacion',
                'Barangay 4 Poblacion','Barangay 5 Poblacion','Benjamin Tirona','Bernardo Pulido','Epifanio Malia',
                'Fiorello Calimag','Francisco Reyes','Francisco de Castro','Gavino Maderan','Gregoria de Jesus',
                'Inocencio Salud','Jacinto Lumbreras','Kapitan Kua','Koronel Jose P. Elises','Macario Dacon',
                'Marcelino Memije','Nicolasa Virata','Pantaleon Granados','Ramon Cruz','San Gabriel','San Jose',
                'Severino de Las Alas','Tiniente Tiago',
            ],
            // City of General Trias
            'City of General Trias' => [
                'Alingaro','Arnaldo Poblacion','Bacao I','Bacao II','Bagumbayan Poblacion','Biclatan',
                'Buenavista I','Buenavista II','Buenavista III','Corregidor Poblacion','Dulong Bayan Poblacion',
                'Gov. Ferrer Poblacion','Javalera','Manggahan','Navarro','Ninety Sixth Poblacion','Panungyanan',
                'Pasong Camachile I','Pasong Camachile II','Pasong Kawayan I','Pasong Kawayan II','Pinagtipunan',
                'Prinza Poblacion','Sampalucan Poblacion','San Francisco','San Gabriel Poblacion','San Juan I',
                'San Juan II','Santa Clara','Santiago','Tapia','Tejero','Vibora Poblacion',
            ],
            // City of Imus
            'City of Imus' => [
                'Alapan I-A','Alapan I-B','Alapan I-C','Alapan II-A','Alapan II-B','Anabu I-A','Anabu I-B',
                'Anabu I-C','Anabu I-D','Anabu I-E','Anabu I-F','Anabu I-G','Anabu II-A','Anabu II-B','Anabu II-C',
                'Anabu II-D','Anabu II-E','Anabu II-F','Anabu II-G','Bagong Silang','Bayan Luma I','Bayan Luma II',
                'Bayan Luma III','Bayan Luma IV','Bayan Luma IX','Bayan Luma V','Bayan Luma VI','Bayan Luma VII',
                'Bayan Luma VIII','Bucandala I','Bucandala II','Bucandala III','Bucandala IV','Bucandala V',
                'Buhay na Tubig','Carsadang Bago I','Carsadang Bago II','Magdalo','Maharlika','Malagasang I-A',
                'Malagasang I-B','Malagasang II-A','Malagasang II-B','Malagasang II-C','Malagasang II-D',
                'Malagasang II-E','Malagasang II-F','Malagasang II-G','Mariano Espeleta I','Mariano Espeleta II',
                'Mariano Espeleta III','Medicion I-A','Medicion I-B','Medicion I-C','Medicion I-D','Medicion II-A',
                'Medicion II-B','Medicion II-C','Medicion II-D','Medicion II-E','Medicion II-F','Pag-asa I',
                'Pag-asa II','Pag-asa III','Palico I','Palico II','Palico III','Palico IV','Poblacion I-A',
                'Poblacion I-B','Poblacion I-C','Poblacion II-A','Poblacion II-B','Poblacion III-A','Poblacion III-B',
                'Poblacion IV-A','Poblacion IV-B','Poblacion IV-C','Poblacion IV-D','Tanzang Luma I','Tanzang Luma II',
                'Tanzang Luma III','Tanzang Luma IV','Tanzang Luma V','Tanzang Luma VI','Tanzang Luma VII',
                'Toclong I-A','Toclong I-B','Toclong I-C','Toclong II-A','Toclong II-B',
            ],
            // Indang
            'Indang' => [
                'Agus-us','Alulod','Banaba Cerca','Banaba Lejos','Bancod','Barangay 1','Barangay 2','Barangay 3',
                'Barangay 4','Buna Cerca','Buna Lejos I','Buna Lejos II','Calumpang Cerca','Calumpang Lejos I',
                'Carasuchi','Daine I','Daine II','Guyam Malaki','Guyam Munti','Harasan','Kayquit I','Kayquit II',
                'Kayquit III','Kaytambog','Kaytapos','Limbon','Lumampong Balagbag','Lumampong Halayhay',
                'Mahabangkahoy Cerca','Mahabangkahoy Lejos','Mataas na Lupa','Pulo','Tambo Balagbag','Tambo Ilaya',
                'Tambo Kulit','Tambo Malaki',
            ],
            // Kawit
            'Kawit' => [
                'Balsahan-Bisita','Batong Dalig','Binakayan-Aplaya','Binakayan-Kanluran','Congbalay-Legaspi','Gahak',
                'Kaingen','Magdalo','Manggahan-Lawin','Marulas','Panamitan','Poblacion','Pulvorista','Samala-Marquez',
                'San Sebastian','Santa Isabel','Tabon I','Tabon II','Tabon III','Toclong','Tramo-Bantayan',
                'Wakas I','Wakas II',
            ],
            // Magallanes
            'Magallanes' => [
                'Baliwag','Barangay 1','Barangay 2','Barangay 3','Barangay 4','Barangay 5','Bendita I','Bendita II',
                'Caluangan','Kabulusan','Medina','Pacheco','Ramirez','San Agustin','Tua','Urdaneta',
            ],
            // Maragondon
            'Maragondon' => [
                'Bucal I','Bucal II','Bucal III A','Bucal III B','Bucal IV A','Bucal IV B','Caingin Poblacion',
                'Garita I A','Garita I B','Layong Mabilog','Mabato','Pantihan I','Pantihan II','Pantihan III',
                'Pantihan IV','Patungan','Pinagsanhan I A','Pinagsanhan I B','Poblacion I A','Poblacion I B',
                'Poblacion II A','Poblacion II B','San Miguel I A','San Miguel I B','Talipusngo','Tulay Kanluran',
                'Tulay Silangan',
            ],
            // Mendez
            'Mendez' => [
                'Anuling Cerca I','Anuling Cerca II','Anuling Lejos I','Anuling Lejos II','Asis I','Asis II','Asis III',
                'Banayad','Bukal','Galicia I','Galicia II','Galicia III','Miguel Mojica','Palocpoc I','Palocpoc II',
                'Panungyan I','Panungyan II','Poblacion I','Poblacion II','Poblacion III','Poblacion IV','Poblacion V',
                'Poblacion VI','Poblacion VII',
            ],
            // Naic
            'Naic' => [
                'Bagong Karsada','Balsahan','Bancaan','Bucana Malaki','Bucana Sasahan','Calubcob','Capt. C. Nazareno',
                'Gomez-Zamora','Halang','Humbac','Ibayo Estacion','Ibayo Silangan','Kanluran','Labac','Latoria',
                'Mabolo','Makina','Malainen Bago','Malainen Luma','Molino','Munting Mapino','Muzon','Palangue 1',
                'Palangue 2 & 3','Sabang','San Roque','Santulan','Sapa','Timalan Balsahan','Timalan Concepcion',
            ],
            // Noveleta
            'Noveleta' => [
                'Magdiwang','Poblacion','Salcedo I','Salcedo II','San Antonio I','San Antonio II','San Jose I',
                'San Jose II','San Juan I','San Juan II','San Rafael I','San Rafael II','San Rafael III','San Rafael IV',
                'Santa Rosa I','Santa Rosa II',
            ],
            // Rosario
            'Rosario' => [
                'Bagbag I','Bagbag II','Kanluran','Ligtong I','Ligtong II','Ligtong III','Ligtong IV','Muzon I',
                'Muzon II','Poblacion','Sapa I','Sapa II','Sapa III','Sapa IV','Silangan I','Silangan II',
                'Tejeros Convention','Wawa I','Wawa II','Wawa III',
            ],
            // Silang
            'Silang' => [
                'Acacia','Adlas','Anahaw I','Anahaw II','Balite I','Balite II','Balubad','Banaba','Barangay I',
                'Barangay II','Barangay III','Barangay IV','Barangay V','Batas','Biga I','Biga II','Biluso','Bucal',
                'Buho','Bulihan','Cabangaan','Carmen','Hoyo','Hukay','Iba','Inchican','Ipil I','Ipil II','Kalubkob',
                'Kaong','Lalaan I','Lalaan II','Litlit','Lucsuhin','Lumil','Maguyam','Malabag','Malaking Tatyao',
                'Mataas na Burol','Munting Ilog','Narra I','Narra II','Narra III','Paligawan','Pasong Langka','Pooc I',
                'Pooc II','Pulong Bunga','Pulong Saging','Puting Kahoy','Sabutan','San Miguel I','San Miguel II',
                'San Vicente I','San Vicente II','Santol','Tartaria','Tibig','Toledo','Tubuan I','Tubuan II',
                'Tubuan III','Ulat','Yakal',
            ],
            // Tagaytay City
            'Tagaytay City' => [
                'Asisan','Bagong Tubig','Calabuso','Dapdap East','Dapdap West','Francisco','Guinhawa North',
                'Guinhawa South','Iruhin East','Iruhin South','Iruhin West','Kaybagal East','Kaybagal North',
                'Kaybagal South','Mag-Asawang Ilat','Maharlika East','Maharlika West','Maitim 2nd Central',
                'Maitim 2nd East','Maitim 2nd West','Mendez Crossing East','Mendez Crossing West','Neogan',
                'Patutong Malaki North','Patutong Malaki South','Sambong','San Jose','Silang Junction North',
                'Silang Junction South','Sungay North','Sungay South','Tolentino East','Tolentino West','Zambal',
            ],
            // Tanza
            'Tanza' => [
                'Amaya I','Amaya II','Amaya III','Amaya IV','Amaya V','Amaya VI','Amaya VII','Bagtas',
                'Barangay I','Barangay II','Barangay III','Barangay IV','Biga','Biwas','Bucal','Bunga','Calibuyo',
                'Capipisa','Daang Amaya I','Daang Amaya II','Daang Amaya III','Halayhay','Julugan I','Julugan II',
                'Julugan III','Julugan IV','Julugan V','Julugan VI','Julugan VII','Julugan VIII','Lambingan','Mulawin',
                'Paradahan I','Paradahan II','Punta I','Punta II','Sahud Ulan','Sanja Mayor','Santol','Tanauan',
                'Tres Cruses',
            ],
            // Ternate
            'Ternate' => [
                'Bucana','Poblacion I','Poblacion I A','Poblacion II','Poblacion III','San Jose','San Juan I',
                'San Juan II','Sapang I','Sapang II',
            ],
            // Trece Martires City
            'Trece Martires City' => [
                'Aguado','Cabezas','Cabuco','Conchu','De Ocampo','Gregorio','Inocencio','Lallana','Lapidario','Luciano',
                'Osorio','Perez','San Agustin',
            ],
        ];
        return $map[$city] ?? [];
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
