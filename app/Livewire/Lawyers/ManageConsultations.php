<?php

namespace App\Livewire\Lawyers;

use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\BlockedTimeSlot;
use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LawyerAvailability;
use Carbon\Carbon;
use App\Models\AvailabilitySlot;
use Illuminate\Support\Facades\Storage;

class ManageConsultations extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Tab control
    public $activeTab = 'consultations';
    public $canSetAvailability = true;

    // Selected consultation and action states
    public $selectedConsultation = null;
    public $selectedDates = [];
    public $customMeetingLink = '';
    public $declineReason = '';
    public $consultationResults = '';
    public $meetingNotes = '';
    public $caseTitle = '';
    public $caseDescription = '';
    public $contractDocument = null;
    public $consultationDocument = null;
    public $debugMessage = 'Initial state';

    // Case Title Templates
    public $caseTitleTemplates = [];
    public $selectedCaseTitleTemplate = '';

    // Modal states
    public $showDeclineModal = false;
    public $showCompleteModal = false;
    public $showStartCaseModal = false;
    public $showCustomLinkModal = false;
    public $showMeetingLinkSuccessModal = false;

    // Properties for Review Contract Modal
    public $showReviewContractModal = false;
    public $reviewCaseTitle = '';
    public $reviewCaseDescription = '';
    public $reviewContractPath = '';
    public $reviewCaseId = null;
    public $selectedConsultationForReview = null;

    public $googleMeetLink = '';
    public $showGoogleMeetInput = [];

    protected $listeners = [
        'refreshConsultations' => '$refresh',
        'refreshSingleConsultation' => 'refreshConsultation'
    ];

    protected $rules = [
        'declineReason' => 'required|min:10',
        'customMeetingLink' => 'nullable|url',
        'consultationResults' => 'required|min:10',
        'meetingNotes' => 'nullable|min:10',
        'caseTitle' => 'required|min:5|max:255',
        'caseDescription' => 'required|min:10',
        'contractDocument' => 'required|file|max:10240|mimes:pdf',
        'consultationDocument' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png'
    ];

    public function mount()
    {
        // Initialize any required data
        $this->showGoogleMeetInput = [];
        
        // Initialize case title templates
        $this->caseTitleTemplates = $this->buildCaseTitleTemplates();

        // Check if lawyer can set availability
        $user = auth()->user();
        $this->canSetAvailability = true;
        
        if ($user && $user->firm_id) {
            $lawFirm = \App\Models\User::find($user->firm_id);
            if ($lawFirm && $lawFirm->lawFirmProfile && !$lawFirm->lawFirmProfile->allow_lawyer_availability) {
                $this->canSetAvailability = false;
                
                // If trying to access 'availability' tab but not allowed, switch to consultations
                if ($this->activeTab === 'availability') {
                    $this->activeTab = 'consultations';
                    session()->flash('error', 'Access to manage availability has been restricted by your firm. Kindly refer to your firm for details.');
                }
            }
        }
    }

    public function updatedSelectedCaseTitleTemplate($value)
    {
        if ($value === '__other__') {
            // Allow manual typing
            $this->caseTitle = '';
            return;
        }
        // Auto-fill caseTitle based on selected template, but keep editable
        $this->caseTitle = $value;
    }

    private function buildCaseTitleTemplates(): array
    {
        // Build grouped options with "label" => category and "options" => [ ['value' => '...', 'text' => '...'] ]
        // Values are what populate the editable title; texts are what display in the dropdown
        $groups = [];

        $add = function (string $group, string $code, string $title, string $status, array $subcategories = []) use (&$groups) {
            $display = $code . ' - ' . $title . ' [' . $status . ']';
            // Base option
            $groups[$group]['label'] = $group;
            $groups[$group]['options'][] = [
                'value' => $display,
                'text' => $display,
            ];
            // Add subcategory detail options (optional; still fill the base title for clarity)
            foreach ($subcategories as $sub) {
                $subDisplay = $display . ' - ' . $sub;
                $groups[$group]['options'][] = [
                    'value' => $subDisplay,
                    'text' => $subDisplay,
                ];
            }
        };

        // CRIMINAL
        $add('Criminal', 'Act No. 3815', 'Revised Penal Code (1930)', 'ACTIVE - Multiple amendments', [
            'Art. 293-299 - Robbery',
            'Art. 308-311 - Theft',
            'Art. 312-316 - Usurpation',
            'Art. 317-318 - Swindling/Estafa',
            'Art. 246-266 - Crimes Against Persons',
            'Art. 355-362 - Libel and Defamation'
        ]);
        $add('Criminal', 'PD 1866', 'Illegal Possession of Firearms', 'ACTIVE');
        $add('Criminal', 'RA 10591', 'Comprehensive Firearms and Ammunition Regulation Act', 'ACTIVE - Does not repeal PD 1866');
        $add('Criminal', 'RA 10883', 'New Anti-Carnapping Act of 2016', 'ACTIVE');
        $add('Criminal', 'RA 6539', 'Anti-Carnapping Act of 1972', 'SUPERSEDED by RA 10883');
        $add('Criminal', 'RA 7438', 'Rights of Persons Arrested, Detained or Under Custodial Investigation', 'ACTIVE');
        $add('Criminal', 'RA 7610', 'Special Protection of Children Against Abuse, Exploitation and Discrimination Act', 'ACTIVE');
        $add('Criminal', 'RA 7877', 'Anti-Sexual Harassment Act of 1995', 'ACTIVE');
        $add('Criminal', 'RA 8049', 'Anti-Hazing Law', 'SUPERSEDED by RA 11053');
        $add('Criminal', 'RA 11053', 'Anti-Hazing Act of 2018', 'ACTIVE');
        $add('Criminal', 'RA 8353', 'Anti-Rape Law of 1997', 'ACTIVE');
        $add('Criminal', 'RA 6425', 'Dangerous Drugs Act of 1972', 'REPEALED by RA 9165');
        $add('Criminal', 'RA 9165', 'Comprehensive Dangerous Drugs Act of 2002', 'ACTIVE');
        $add('Criminal', 'RA 9208', 'Anti-Trafficking in Persons Act of 2003', 'AMENDED by RA 10364');
        $add('Criminal', 'RA 10364', 'Expanded Anti-Trafficking in Persons Act of 2012', 'ACTIVE');
        $add('Criminal', 'RA 9262', 'Anti-Violence Against Women and Their Children Act of 2004 (VAWC)', 'ACTIVE');
        $add('Criminal', 'RA 9344', 'Juvenile Justice and Welfare Act of 2006', 'AMENDED by RA 10630');
        $add('Criminal', 'RA 10630', 'Amendments to Juvenile Justice and Welfare Act', 'ACTIVE');
        $add('Criminal', 'RA 9775', 'Anti-Child Pornography Act of 2009', 'ACTIVE');
        $add('Criminal', 'RA 10175', 'Cybercrime Prevention Act of 2012', 'ACTIVE');
        $add('Criminal', 'RA 11313', 'Safe Spaces Act (Bawal Bastos Law)', 'ACTIVE');
        $add('Criminal', 'RA 11479', 'Anti-Terrorism Act of 2020', 'ACTIVE');
        $add('Criminal', 'RA 11930', 'Anti-Online Sexual Abuse or Exploitation of Children (OSAEC)', 'ACTIVE');
        $add('Criminal', 'RA 9995', 'Anti-Photo and Video Voyeurism Act of 2009', 'ACTIVE');
        $add('Criminal', 'RA 10353', 'Anti-Enforced or Involuntary Disappearance Act of 2012', 'ACTIVE');

        // CIVIL
        $add('Civil', 'RA 386', 'Civil Code of the Philippines (1949)', 'ACTIVE', [
            'Art. 1-18 - Human Relations',
            'Art. 19-21 - Abuse of Rights',
            'Art. 1144-1155 - Prescription',
            'Art. 1156-1304 - Obligations and Contracts',
            'Art. 712-1103 - Property',
            'Art. 1305-1422 - Sales, Lease, Contracts',
            'Art. 1732-1766 - Common Carriers',
            'Art. 2176-2194 - Quasi-Delicts/Torts',
        ]);
        $add('Civil', 'RA 26', 'Reconstitution of Lost or Destroyed Torrens Certificates', 'ACTIVE');
        $add('Civil', 'RA 6552', 'Maceda Law (Realty Installment Buyer Protection Act)', 'ACTIVE');
        $add('Civil', 'RA 9048', 'Clerical Error Law', 'AMENDED by RA 10172');
        $add('Civil', 'RA 10172', 'Correcting Entries in the Civil Registry', 'ACTIVE');
        $add('Civil', 'RA 9255', 'Allowing Illegitimate Children to Use Surname of Father', 'ACTIVE');
        $add('Civil', 'RA 7691', 'Expanded Jurisdiction of MTC/MMTC/MCTC', 'ACTIVE');
        $add('Civil', 'RA 8552', 'Domestic Adoption Act of 1998', 'LARGELY SUPERSEDED by RA 11642');
        $add('Civil', 'RA 11642', 'Domestic Administrative Adoption and Alternative Child Care Act', 'ACTIVE');
        $add('Civil', 'RA 8043', 'Inter-Country Adoption Act of 1995', 'ACTIVE');
        $add('Civil', 'BP 129', 'Judiciary Reorganization Act of 1980', 'ACTIVE - Multiple amendments');

        // FAMILY
        $add('Family', 'EO 209', 'Family Code of the Philippines (1987)', 'ACTIVE', [
            'Art. 1-67 - Marriage',
            'Art. 36 - Psychological Incapacity',
            'Art. 40-41 - Judicial Declaration of Nullity',
            'Art. 45-54 - Legal Separation',
            'Art. 55-62 - Property Relations',
            'Art. 68-73 - Family Home',
            'Art. 142-182 - Parental Authority',
            'Art. 164-182 - Support',
        ]);
        $add('Family', 'RA 9262', 'Anti-Violence Against Women and Their Children Act (VAWC)', 'ACTIVE');
        $add('Family', 'RA 9710', 'Magna Carta of Women', 'ACTIVE');
        $add('Family', 'RA 8552', 'Domestic Adoption Act of 1998', 'LARGELY SUPERSEDED by RA 11642');
        $add('Family', 'RA 11642', 'Domestic Administrative Adoption and Alternative Child Care Act', 'ACTIVE');
        $add('Family', 'RA 10165', 'Foster Care Act of 2012', 'ACTIVE');

        // LABOR
        $add('Labor', 'PD 442', 'Labor Code of the Philippines', 'ACTIVE - Multiple amendments', [
            'Art. 106-109 - Labor-Only Contracting',
            'Art. 279-283 - Security of Tenure',
            'Art. 294-299 - Termination of Employment',
            'Art. 223-229 - Jurisdiction and Venue',
        ]);
        $add('Labor', 'RA 6715', 'Herrera Law (Amending the Labor Code)', 'ACTIVE');
        $add('Labor', 'RA 10361', 'Domestic Workers Act (Kasambahay Law)', 'ACTIVE');
        $add('Labor', 'RA 11210', '105-Day Expanded Maternity Leave Law', 'ACTIVE');
        $add('Labor', 'RA 8972', "Solo Parents' Welfare Act of 2000", 'AMENDED by RA 11861');
        $add('Labor', 'RA 11861', 'Expanded Solo Parents\' Welfare Act', 'ACTIVE');
        $add('Labor', 'RA 7875', 'National Health Insurance Act (PhilHealth)', 'AMENDED by RA 10606');
        $add('Labor', 'RA 10606', 'National Health Insurance Act of 2013', 'ACTIVE');
        $add('Labor', 'RA 8282', 'Social Security Act of 1997', 'AMENDED by RA 11199');
        $add('Labor', 'RA 11199', 'Social Security Act of 2018', 'ACTIVE');
        $add('Labor', 'RA 9679', 'Pag-IBIG Law', 'ACTIVE');
        $add('Labor', 'RA 11058', 'Occupational Safety and Health Standards Act', 'ACTIVE');
        $add('Labor', 'RA 11466', 'Salary Standardization Law', 'ACTIVE');
        $add('Labor', 'RA 11165', 'Telecommuting Act', 'ACTIVE');
        $add('Labor', 'RA 12066', 'Extended Breastfeeding Breaks for Working Mothers', 'ACTIVE - 2024');

        // PROPERTY & LAND
        $add('Property & Land', 'PD 1529', 'Property Registration Decree', 'ACTIVE');
        $add('Property & Land', 'RA 6657', 'Comprehensive Agrarian Reform Law (CARL)', 'ACTIVE - Multiple extensions');
        $add('Property & Land', 'RA 7279', 'Urban Development and Housing Act (UDHA)', 'ACTIVE');
        $add('Property & Land', 'RA 7160', 'Local Government Code of 1991', 'ACTIVE');
        $add('Property & Land', 'RA 7942', 'Philippine Mining Act of 1995', 'ACTIVE');
        $add('Property & Land', 'RA 10121', 'Disaster Risk Reduction and Management Act', 'ACTIVE');
        $add('Property & Land', 'RA 7076', 'People\'s Small-Scale Mining Act of 1991', 'ACTIVE');
        $add('Property & Land', 'RA 10752', 'Right-of-Way Act', 'ACTIVE');

        // BUSINESS & COMMERCIAL
        $add('Business & Commercial', 'BP 68', 'Corporation Code', 'SUPERSEDED by RA 11232');
        $add('Business & Commercial', 'RA 11232', 'Revised Corporation Code of 2018', 'ACTIVE');
        $add('Business & Commercial', 'RA 8792', 'Electronic Commerce Act of 2000', 'ACTIVE');
        $add('Business & Commercial', 'RA 10173', 'Data Privacy Act of 2012', 'ACTIVE');
        $add('Business & Commercial', 'RA 8293', 'Intellectual Property Code of 1997', 'ACTIVE');
        $add('Business & Commercial', 'RA 11057', 'Personal Property Security Act', 'ACTIVE');
        $add('Business & Commercial', 'RA 9178', 'BMBEs Act', 'ACTIVE');
        $add('Business & Commercial', 'RA 10667', 'Philippine Competition Act', 'ACTIVE');
        $add('Business & Commercial', 'RA 8799', 'Securities Regulation Code', 'ACTIVE');
        $add('Business & Commercial', 'RA 8556', 'Financing Company Act of 1998', 'ACTIVE');
        $add('Business & Commercial', 'RA 11032', 'Ease of Doing Business Act', 'ACTIVE');
        $add('Business & Commercial', 'RA 10870', 'Philippine Credit Card Industry Regulation Act', 'ACTIVE');
        $add('Business & Commercial', 'RA 8424', 'National Internal Revenue Code (Tax Code)', 'ACTIVE - Multiple amendments');
        $add('Business & Commercial', 'RA 10963', 'TRAIN', 'ACTIVE');
        $add('Business & Commercial', 'RA 11534', 'CREATE', 'AMENDED by RA 11976');
        $add('Business & Commercial', 'RA 11976', 'CREATE MORE Act', 'ACTIVE - 2024');
        $add('Business & Commercial', 'RA 11961', 'Ease of Paying Taxes Act', 'ACTIVE - 2024');
        $add('Business & Commercial', 'RA 12252', 'Financial Product Innovation Act', 'ACTIVE - 2025');

        // CONSUMER
        $add('Consumer', 'RA 7394', 'Consumer Act of the Philippines', 'ACTIVE');
        $add('Consumer', 'RA 7581', 'Price Act', 'ACTIVE');
        $add('Consumer', 'RA 10642', 'Philippine Lemon Law', 'ACTIVE');
        $add('Consumer', 'RA 7432', 'Senior Citizens Act', 'AMENDED by RA 9994');
        $add('Consumer', 'RA 9994', 'Expanded Senior Citizens Act', 'ACTIVE');
        $add('Consumer', 'RA 7277', 'Magna Carta for Disabled Persons', 'AMENDED by RA 10754');
        $add('Consumer', 'RA 10754', 'Expanding Benefits and Privileges of PWDs', 'ACTIVE');
        $add('Consumer', 'RA 9442', 'Magna Carta for PWD Amendments', 'FURTHER AMENDED by RA 10754');

        // TRANSPORTATION
        $add('Transportation', 'RA 4136', 'Land Transportation and Traffic Code', 'ACTIVE - Multiple amendments');
        $add('Transportation', 'RA 10913', 'Anti-Distracted Driving Act', 'ACTIVE');
        $add('Transportation', 'RA 10916', 'Road Speed Limiter Act', 'ACTIVE');
        $add('Transportation', 'RA 10054', 'Motorcycle Helmet Act of 2009', 'ACTIVE');
        $add('Transportation', 'RA 11235', 'Motorcycle Crime Prevention Act', 'ACTIVE');
        $add('Transportation', 'RA 10666', 'Children\'s Safety on Motorcycles Act', 'ACTIVE');
        $add('Transportation', 'RA 11229', 'Child Safety in Motor Vehicles Act', 'ACTIVE');

        // BANKING & FINANCE
        $add('Banking & Finance', 'RA 8791', 'General Banking Law of 2000', 'ACTIVE');
        $add('Banking & Finance', 'RA 1405', 'Bank Secrecy Law', 'ACTIVE');
        $add('Banking & Finance', 'RA 9160', 'Anti-Money Laundering Act (AMLA)', 'ACTIVE - Multiple amendments');
        $add('Banking & Finance', 'RA 10365', 'AMLA Amendments (2013)', 'ACTIVE');
        $add('Banking & Finance', 'RA 10927', 'AMLA Amendments (2017) - Casino Coverage', 'ACTIVE');
        $add('Banking & Finance', 'RA 11521', 'FIST Act', 'ACTIVE');
        $add('Banking & Finance', 'RA 3591', 'PDIC Charter', 'AMENDED by RA 10846');
        $add('Banking & Finance', 'RA 10846', 'PDIC Charter Amendments', 'ACTIVE');
        $add('Banking & Finance', 'RA 7653', 'New Central Bank Act', 'AMENDED by RA 11211');
        $add('Banking & Finance', 'RA 11211', 'BSP Charter Amendments', 'ACTIVE');

        // ANTI-CORRUPTION
        $add('Anti-Corruption', 'RA 3019', 'Anti-Graft and Corrupt Practices Act', 'ACTIVE');
        $add('Anti-Corruption', 'RA 1379', 'Forfeiture of Unlawfully Acquired Property', 'ACTIVE');
        $add('Anti-Corruption', 'RA 6713', 'Code of Conduct and Ethical Standards for Public Officials', 'ACTIVE');
        $add('Anti-Corruption', 'RA 9485', 'Anti-Red Tape Act of 2007', 'AMENDED by RA 11032');
        $add('Anti-Corruption', 'RA 11032', 'Ease of Doing Business...', 'ACTIVE');
        $add('Anti-Corruption', 'RA 6770', 'Ombudsman Act of 1989', 'ACTIVE');

        // ELECTION
        $add('Election Laws', 'BP 881', 'Omnibus Election Code', 'ACTIVE');
        $add('Election Laws', 'RA 9189', 'Overseas Absentee Voting Act', 'AMENDED by RA 10590');
        $add('Election Laws', 'RA 10590', 'Overseas Voting Act of 2013', 'ACTIVE');
        $add('Election Laws', 'RA 10367', 'Mandatory Biometrics Voter Registration Act', 'ACTIVE');
        $add('Election Laws', 'RA 8436', 'Automated Election System', 'AMENDED by RA 9369');
        $add('Election Laws', 'RA 9369', 'Amendments to Automated Election System', 'ACTIVE');

        // HEALTH & MEDICAL
        $add('Health & Medical', 'RA 11223', 'Universal Health Care Act', 'ACTIVE');
        $add('Health & Medical', 'RA 7875', 'National Health Insurance Act (PhilHealth)', 'SUPERSEDED by RA 10606');
        $add('Health & Medical', 'RA 10606', 'National Health Insurance Act of 2013', 'ACTIVE');
        $add('Health & Medical', 'RA 11036', 'Mental Health Act', 'ACTIVE');
        $add('Health & Medical', 'RA 10354', 'Responsible Parenthood and Reproductive Health Act (RH Law)', 'ACTIVE');
        $add('Health & Medical', 'RA 11332', 'Mandatory Reporting of Notifiable Diseases', 'ACTIVE');
        $add('Health & Medical', 'RA 11525', 'COVID-19 Vaccination Program Act', 'ACTIVE');
        $add('Health & Medical', 'RA 9211', 'Tobacco Regulation Act of 2003', 'ACTIVE');
        $add('Health & Medical', 'RA 10643', 'Graphic Health Warnings Law', 'ACTIVE');
        $add('Health & Medical', 'RA 11467', 'Malasakit Centers Act', 'ACTIVE');
        $add('Health & Medical', 'RA 10747', 'Rare Diseases Act of the Philippines', 'ACTIVE');
        $add('Health & Medical', 'RA 11215', 'National Integrated Cancer Control Act', 'ACTIVE');
        $add('Health & Medical', 'RA 12028', 'Comprehensive Guide Dog Act', 'ACTIVE - 2024');

        // EDUCATION
        $add('Education', 'BP 232', 'Education Act of 1982', 'ACTIVE');
        $add('Education', 'RA 10533', 'Enhanced Basic Education Act (K-12)', 'ACTIVE');
        $add('Education', 'RA 10931', 'Universal Access to Quality Tertiary Education Act', 'ACTIVE');
        $add('Education', 'RA 7722', 'Higher Education Act of 1994 (CHED)', 'ACTIVE');
        $add('Education', 'RA 9155', 'Governance of Basic Education Act (DepEd)', 'ACTIVE');
        $add('Education', 'RA 10627', 'Anti-Bullying Act of 2013', 'ACTIVE');
        $add('Education', 'RA 10912', 'Continuing Professional Development (CPD) Act', 'ACTIVE');
        $add('Education', 'RA 11984', 'No Permit, No Exam Prohibition Act', 'ACTIVE - 2024');
        $add('Education', 'RA 12056', 'ARAL Program Act', 'ACTIVE - 2024');

        // ENVIRONMENTAL
        $add('Environmental', 'PD 1151', 'Environmental Code', 'ACTIVE');
        $add('Environmental', 'RA 8749', 'Clean Air Act', 'ACTIVE');
        $add('Environmental', 'RA 9003', 'Ecological Solid Waste Management Act', 'ACTIVE');
        $add('Environmental', 'RA 9275', 'Clean Water Act', 'ACTIVE');
        $add('Environmental', 'RA 9729', 'Climate Change Act', 'ACTIVE');
        $add('Environmental', 'RA 10174', 'People\'s Survival Fund', 'ACTIVE');
        $add('Environmental', 'RA 7586', 'NIPAS Act', 'AMENDED by RA 11038');
        $add('Environmental', 'RA 11038', 'Expanded NIPAS Act', 'ACTIVE');
        $add('Environmental', 'RA 12225', 'Sustainable Products Act', 'ACTIVE - 2024');

        // SPECIAL & GOVERNANCE
        $add('Special & Governance', 'RA 11054', 'Bangsamoro Organic Law', 'ACTIVE');
        $add('Special & Governance', 'RA 9147', 'Wildlife Resources Conservation and Protection Act', 'ACTIVE');
        $add('Special & Governance', 'RA 8371', 'Indigenous Peoples Rights Act (IPRA)', 'ACTIVE');
        $add('Special & Governance', 'RA 10121', 'Disaster Risk Reduction and Management Act', 'ACTIVE');
        $add('Special & Governance', 'RA 11055', 'Philippine Identification System Act (PhilSys)', 'ACTIVE');
        $add('Special & Governance', 'RA 10801', 'OWWA Charter', 'ACTIVE');
        $add('Special & Governance', 'RA 8042', 'Migrant Workers Act', 'AMENDED by RA 10022');
        $add('Special & Governance', 'RA 10022', 'Amendments to Migrant Workers Act', 'ACTIVE');
        $add('Special & Governance', 'RA 12064', 'Philippine Maritime Zones Act', 'ACTIVE - 2024');
        $add('Special & Governance', 'RA 12065', 'Philippine Archipelagic Sea Lanes Act', 'ACTIVE - 2024');
        $add('Special & Governance', 'RA 12023', 'National Government Rightsizing Program Act', 'ACTIVE - 2024');

        // RECENT 2024-2025
        $add('Recent (2024-2025)', 'RA 11961', 'Ease of Paying Taxes Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 11976', 'CREATE MORE Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 11984', 'No Permit, No Exam Prohibition Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12066', 'Extended Breastfeeding Breaks for Working Mothers', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12021', 'Kabataan Party-List Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 11930', 'Anti-OSAEC Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12023', 'National Government Rightsizing Program Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12064', 'Philippine Maritime Zones Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12065', 'Philippine Archipelagic Sea Lanes Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12028', 'Comprehensive Guide Dog Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12056', 'ARAL Program Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12225', 'Sustainable Products Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12226', 'Green Jobs Act', 'ACTIVE - 2024');
        $add('Recent (2024-2025)', 'RA 12252', 'Financial Product Innovation Act', 'ACTIVE - 2025');
        $add('Recent (2024-2025)', 'RA 12283', 'Agricultural Free Patent Reform Act', 'ACTIVE - 2025');
        $add('Recent (2024-2025)', 'RA 12297', 'Investment Leasing Act', 'ACTIVE - 2025');
        $add('Recent (2024-2025)', 'RA 11590', 'POGO Taxation Act', 'ACTIVE - 2020');

        // Sort options within groups alphabetically
        foreach ($groups as &$group) {
            if (isset($group['options'])) {
                usort($group['options'], function ($a, $b) {
                    return strcmp($a['text'], $b['text']);
                });
            }
        }
        unset($group);

        return $groups;
    }

    public function updated($name, $value)
    {
        // Debug when property is updated
        if (strpos($name, 'selectedDates.') === 0) {
            session()->flash('message', 'Date selected: ' . $value);
        }
    }

    public function showCustomLinkForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->resetValidation();
        $this->showCustomLinkModal = true;
    }

    public function saveCustomMeetingLink($consultationId = null)
    {
        // Validate custom meeting link
        $this->validate([
            'customMeetingLink' => 'required|url'
        ]);
        
        // Determine which consultation to update (inline or modal)
        $id = $consultationId ?: $this->selectedConsultation;
        $consultation = Consultation::findOrFail($id);
        
        // Check if the lawyer is either directly assigned, assigned through a law firm, or belongs to the law firm
        $user = auth()->user();
        $isAuthorized = $consultation->lawyer_id === $user->id || 
                        $consultation->specific_lawyer_id === $user->id ||
                        ($user->firm_id && $consultation->lawyer_id === $user->firm_id);

        if (!$isAuthorized) {
            session()->flash('error', 'You are not authorized to update this consultation.');
            return;
        }

        // Update the meeting link
        $consultation->update([
            'meeting_link' => $this->customMeetingLink
        ]);

        // Send notification to the client
        NotificationService::consultationLinkUpdated($consultation);

        // Reset input state and hide modal
        $this->customMeetingLink = '';
        $this->selectedConsultation = null;
        $this->showCustomLinkModal = false;
        session()->flash('message', 'Meeting link updated successfully.');
        $this->dispatch('notification-received');
    }

    public function acceptConsultation($consultationId)
    {   
        $consultation = Consultation::findOrFail($consultationId);
        
        if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
            session()->flash('error', 'You are not authorized to accept this consultation.');
            return;
        }

        // Since clients can only select one time frame, automatically use the requested date/time
        // Priority: start_time > first preferred date > selected_date
        if ($consultation->start_time) {
            $selectedDate = $consultation->start_time;
        } else {
            $preferredDates = json_decode($consultation->preferred_dates, true);
            if (!empty($preferredDates) && is_array($preferredDates)) {
                $selectedDate = $preferredDates[0];
            } elseif (!empty($preferredDates) && is_string($preferredDates)) {
                $selectedDate = $preferredDates;
            } elseif ($consultation->selected_date) {
                $selectedDate = $consultation->selected_date;
            } else {
                session()->flash('error', 'No requested date/time available. Cannot accept consultation.');
                return;
            }
        }

        // Parse the selected date to get start and end times
        $startDateTime = \Carbon\Carbon::parse($selectedDate);
        $endDateTime = $startDateTime->copy()->addHour(); // Default 1-hour consultation
        
        // If consultation already has specific start/end times, use those
        if ($consultation->start_time && $consultation->end_time) {
            $startDateTime = \Carbon\Carbon::parse($consultation->start_time);
            $endDateTime = \Carbon\Carbon::parse($consultation->end_time);
        }

        // Check for time slot conflicts
        $lawyerId = $consultation->specific_lawyer_id ?: $consultation->lawyer_id;
        if (\App\Models\BlockedTimeSlot::hasConflict($lawyerId, $startDateTime, $endDateTime)) {
            session()->flash('error', 'This time slot conflicts with another consultation or blocked time. Please choose a different time.');
            return;
        }

        // First, accept with a default or custom meeting link
        $consultation->update([
            'status' => 'accepted',
            'selected_date' => $selectedDate,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'meeting_link' => $consultation->consultation_type === 'Online Consultation'
                ? ($this->customMeetingLink ?: $this->generateMeetingLink())
                : null
        ]);

        // Create blocked time slot to prevent double booking
        \App\Models\BlockedTimeSlot::createForConsultation($consultation);

        // If custom link was used, reset it
        $this->customMeetingLink = '';

        // Send notification to the client
        NotificationService::consultationAccepted($consultation);

        // Dispatch Livewire event for real-time updates
        $this->dispatch('notification-received');
        $this->dispatch('consultation-accepted');
        
        // If this is an online consultation, prompt to provide a custom link
        if ($consultation->consultation_type === 'Online Consultation') {
            $this->selectedConsultation = $consultationId;
            $this->showCustomLinkModal = true;
            session()->flash('message', 'Consultation accepted. You can now provide a custom meeting link or use the automatically generated one.');
        } else {
            session()->flash('message', 'Consultation request accepted successfully.');
        }
    }

    public function showDeclineForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->resetValidation();
        $this->showDeclineModal = true;
    }

    public function declineConsultation()
    {
        $this->validate([
            'declineReason' => 'required|min:10'
        ]);

        $consultation = Consultation::findOrFail($this->selectedConsultation);
        
        if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
            session()->flash('error', 'You are not authorized to decline this consultation.');
            return;
        }

        $consultation->update([
            'status' => 'declined',
            'decline_reason' => $this->declineReason
        ]);

        // Remove any blocked time slots for this consultation
        if ($consultation->blockedTimeSlot) {
            $consultation->blockedTimeSlot->delete();
        }

        // Send notification to the client
        NotificationService::consultationDeclined($consultation);

        $this->showDeclineModal = false;
        $this->declineReason = '';
        $this->selectedConsultation = null;

        // Dispatch Livewire event for real-time updates
        $this->dispatch('notification-received');
        $this->dispatch('consultation-declined');
        
        session()->flash('message', 'Consultation request declined.');
    }

    public function showCompleteForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->resetValidation();
        $this->consultationResults = '';
        $this->meetingNotes = '';
        $this->showCompleteModal = true;
    }

    public function markConsultationComplete()
    {
        // Simple validation
        if (empty($this->consultationResults)) {
            session()->flash('error', 'Please provide consultation results before completing.');
            return;
        }

        if (strlen($this->consultationResults) < 10) {
            session()->flash('error', 'Please provide more detailed consultation results.');
            return;
        }

        try {
            $consultation = Consultation::find($this->selectedConsultation);
            
            if (!$consultation) {
                session()->flash('error', 'Consultation not found.');
                return;
            }

            // Check authorization
            if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
                session()->flash('error', 'You are not authorized to complete this consultation.');
                return;
            }

            // Update consultation
            $consultation->status = 'completed';
            $consultation->is_completed = true;
            $consultation->consultation_results = $this->consultationResults;
            $consultation->meeting_notes = $this->meetingNotes;
            $consultation->can_start_case = true;
            $consultation->save();

            // Handle document upload if provided
            if ($this->consultationDocument) {
                $path = $this->consultationDocument->store('consultation-documents', 'public');
                $consultation->consultation_document_path = $path;
                $consultation->save();
            }

            // Remove blocked time slot
            if ($consultation->blockedTimeSlot) {
                $consultation->blockedTimeSlot->delete();
            }

            // Send notification
            try {
                NotificationService::consultationCompleted($consultation);
            } catch (\Exception $e) {
                \Log::warning('Failed to send completion notification: ' . $e->getMessage());
            }

            // Reset form and close modal
            $this->showCompleteModal = false;
            $this->consultationResults = '';
            $this->meetingNotes = '';
            $this->consultationDocument = null;

            session()->flash('message', 'Consultation completed successfully! You can now start a case with this client.');

        } catch (\Exception $e) {
            \Log::error('Error completing consultation', [
                'error' => $e->getMessage(),
                'consultation_id' => $this->selectedConsultation,
                'user_id' => auth()->id()
            ]);
            
            session()->flash('error', 'Failed to complete consultation. Please try again.');
        }
    }

    public function openStartCaseModal($consultationId)
    {
        $consultation = Consultation::find($consultationId);

        if (!$consultation) {
            session()->flash('error', 'Consultation not found.');
            return;
        }

        if ($consultation->status !== 'completed') {
            session()->flash('error', 'Consultation must be completed first.');
            return;
        }
        
        // Check authorization
        if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
            session()->flash('error', 'You are not authorized to start a case for this consultation.');
            return;
        }

        $this->selectedConsultation = $consultationId;
        $this->caseTitle = '';
        $this->caseDescription = '';
        $this->contractDocument = null;
        $this->showStartCaseModal = true;
    }

    public function createNewCase()
    {
        // Simple validation
        if (empty($this->caseTitle)) {
            session()->flash('error', 'Please provide a case title.');
            return;
        }

        if (empty($this->caseDescription)) {
            session()->flash('error', 'Please provide a case description.');
            return;
        }

        if (!$this->contractDocument) {
            session()->flash('error', 'Please upload a contract document.');
            return;
        }

        // Validate that the file is a PDF
        $fileExtension = strtolower($this->contractDocument->getClientOriginalExtension());
        $mimeType = $this->contractDocument->getMimeType();
        
        if ($fileExtension !== 'pdf' || $mimeType !== 'application/pdf') {
            session()->flash('error', 'Kindly upload a pdf file');
            $this->contractDocument = null;
            return;
        }

        try {
            $consultation = Consultation::find($this->selectedConsultation);
            
            if (!$consultation) {
                session()->flash('error', 'Consultation not found.');
                return;
            }

            // Check authorization
            if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
                session()->flash('error', 'You are not authorized to start a case for this consultation.');
                return;
            }

            // Check if case already exists
            $existingCase = LegalCase::where('consultation_id', $consultation->id)->first();
            if ($existingCase) {
                session()->flash('error', 'A case already exists for this consultation.');
                return;
            }

            // Store contract document
            $contractPath = $this->contractDocument->store('contracts', 'public');

            // Create the case
            $case = LegalCase::create([
                'consultation_id' => $consultation->id,
                'client_id' => $consultation->client_id,
                'lawyer_id' => auth()->id(),
                'title' => $this->caseTitle,
                'description' => $this->caseDescription,
                'status' => 'contract_sent',
                'case_completion' => 'pending',
                'contract_path' => $contractPath,
                'contract_status' => 'sent',
                'case_number' => 'CASE-' . date('Ymd') . '-' . uniqid()
            ]);

            // Send notification
            try {
                NotificationService::caseStarted($case);
            } catch (\Exception $e) {
                \Log::warning('Failed to send case notification: ' . $e->getMessage());
            }

            // Reset form and close modal
            $this->showStartCaseModal = false;
            $this->caseTitle = '';
            $this->caseDescription = '';
            $this->contractDocument = null;

            session()->flash('message', 'Case created successfully! The client has been notified.');

        } catch (\Exception $e) {
            \Log::error('Error creating case', [
                'error' => $e->getMessage(),
                'consultation_id' => $this->selectedConsultation,
                'user_id' => auth()->id()
            ]);
            
            session()->flash('error', 'Failed to create case. Please try again.');
        }
    }

    public function showReviewContractModal($consultationId)
    {
        $consultation = Consultation::with('case.client.clientProfile')->findOrFail($consultationId);
        
        // Check if lawyer is authorized
        $lawyerId = auth()->id();
        if ($consultation->lawyer_id !== $lawyerId && $consultation->specific_lawyer_id !== $lawyerId) {
            session()->flash('error', 'You are not authorized to view this consultation.');
            return;
        }
        
        if ($consultation->case) {
            $case = $consultation->case;
            $this->selectedConsultationForReview = $consultation;
            $this->reviewCaseTitle = $case->title;
            $this->reviewCaseDescription = $case->description;
            $this->reviewContractPath = $case->contract_path;
            $this->reviewCaseId = $case->id;
            $this->resetValidation();
            $this->showReviewContractModal = true;
        } else {
            session()->flash('error', 'No case contract found for this consultation.');
            $this->selectedConsultationForReview = null;
            $this->reviewContractPath = null;
            $this->reviewCaseId = null;
        }
    }

    protected function generateMeetingLink()
    {
        // This is a placeholder. In a real implementation, 
        // this would integrate with a video conferencing service
        return 'https://meet.lexcav.com/' . uniqid();
    }

    public function showGoogleMeetLinkInput($consultationId)
    {
        $this->showGoogleMeetInput[$consultationId] = true;
        $consultation = Consultation::findOrFail($consultationId);
        $this->googleMeetLink = $consultation->meeting_link; // Pre-fill with current link
    }

    public function addMeetingLink($consultationId)
    {
        // Add debugging to see if the method is being called and with what value
        \Illuminate\Support\Facades\Log::info('Adding meeting link', [
            'consultation_id' => $consultationId,
            'google_meet_link' => $this->googleMeetLink
        ]);

        $this->validate([
            'googleMeetLink' => 'required|url',
        ], [
            'googleMeetLink.required' => 'Please enter a meeting link.',
            'googleMeetLink.url' => 'Please enter a valid URL (e.g., https://meet.google.com/xxx or https://zoom.us/j/xxx).',
        ]);

        $consultation = Consultation::findOrFail($consultationId);
        
        // Check if the lawyer is either directly assigned, assigned through a law firm, or belongs to the law firm
        $user = auth()->user();
        $isAuthorized = $consultation->lawyer_id === $user->id || 
                        $consultation->specific_lawyer_id === $user->id ||
                        ($user->firm_id && $consultation->lawyer_id === $user->firm_id);

        if (!$isAuthorized) {
            session()->flash('error', 'You are not authorized to update this consultation.');
            return;
        }
        
        if ($consultation->consultation_type !== 'Online Consultation' || $consultation->status !== 'accepted') {
            session()->flash('error', 'Meeting links can only be added to accepted online consultations.');
            return;
        }
        
        // Update the consultation with the provided meeting link
        $consultation->update([
            'meeting_link' => $this->googleMeetLink
        ]);
        
        // Send notification to the client
        NotificationService::consultationLinkUpdated($consultation);
        
        // Reset the input and hide the input field
        $this->googleMeetLink = '';
        $this->showGoogleMeetInput[$consultationId] = false;
        
        session()->flash('message', 'Meeting link updated successfully.');
        $this->dispatch('notification-received');
    }

    public function refreshConsultation($consultationId)
    {
        // This method will be called by the frontend to refresh a specific consultation
        // The component itself will be refreshed through Livewire's automatic rendering
        \Illuminate\Support\Facades\Log::info('Refreshing consultation', [
            'consultation_id' => $consultationId
        ]);
    }

    public function updateMeetingLink($consultationId)
    {
        // Add debugging to see if the method is being called
        \Illuminate\Support\Facades\Log::info('Direct method called for meeting link update', [
            'consultation_id' => $consultationId,
            'google_meet_link' => $this->googleMeetLink
        ]);

        // Validate the link
        $this->validate([
            'googleMeetLink' => 'required|url',
        ], [
            'googleMeetLink.required' => 'Please enter a meeting link.',
            'googleMeetLink.url' => 'Please enter a valid URL.',
        ]);

        try {
            // Find the consultation
            $consultation = Consultation::findOrFail($consultationId);
            
            // Check authorization
            if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
                session()->flash('error', 'You are not authorized to update this consultation.');
                return;
            }
            
            // Check consultation type and status
            if ($consultation->consultation_type !== 'Online Consultation' || $consultation->status !== 'accepted') {
                session()->flash('error', 'Meeting links can only be added to accepted online consultations.');
                return;
            }
            
            // Store the link before updating
            $newLink = $this->googleMeetLink;
            
            // Directly update the meeting link
            $consultation->meeting_link = $newLink;
            $consultation->save();
            
            // Reload the consultation to ensure we have the latest data
            $consultation = Consultation::findOrFail($consultationId);
            
            // Send notification
            NotificationService::consultationLinkUpdated($consultation);
            
            // Reset input but keep the consultation model updated in the view
            $this->googleMeetLink = '';
            $this->showGoogleMeetInput[$consultationId] = false;
            
            // Show success modal
            $this->showMeetingLinkSuccessModal = true;
            
            // Trigger a full component refresh to ensure the UI shows the updated link
            $this->dispatch('refreshConsultations');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating meeting link', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to update meeting link: ' . $e->getMessage());
        }
        
        $this->dispatch('notification-received');
    }

    public function render()
    {
        $userId = auth()->id();
        
        // Get consultations where the lawyer is either directly assigned (lawyer_id)
        // or specifically assigned by a law firm (specific_lawyer_id)
        $consultations = Consultation::where(function($query) use ($userId) {
                // Directly assigned to the lawyer
                $query->where('lawyer_id', $userId)
                // OR specifically assigned to the lawyer by a law firm
                ->orWhere('specific_lawyer_id', $userId);
            })
            ->with(['client.clientProfile', 'lawyer.lawFirmProfile', 'case' => function($query) {
                $query->select('id', 'consultation_id', 'title', 'description', 'contract_path', 'lawyer_id');
            }]) // Eager load the 'case' relationship with contract_path
            ->latest()
            ->paginate(10);

        return view('livewire.lawyers.manage-consultations', [
            'consultations' => $consultations,
            'activeTab' => $this->activeTab
        ])->layout('components.layouts.app', [
            'header' => 'Manage Consultations',
            'title' => 'Manage Consultations'
        ]);
    }
} 