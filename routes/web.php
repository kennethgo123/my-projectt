<?php

use App\Livewire\Auth\Register;
use App\Livewire\Profile\CompleteProfile;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\ServiceManagement;
use App\Livewire\Admin\SalesPanel;
use App\Livewire\Home;
use App\Livewire\Services;
use App\Livewire\Lawyers\LawyerProfile;
use App\Livewire\Lawyers\LawyerSearch;
use App\Livewire\Lawyers\ManageCases;
use App\Livewire\Messages\Chat;
use App\Livewire\Providers\Show;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profile\OptimizeProfileController;
use App\Http\Controllers\LawFirm\LawyerController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Auth\Access\AuthorizesActions;
use Illuminate\Auth\Access\AuthorizationException;

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
    ->middleware('auth', 'throttle:6,1')
    ->name('verification.send');

Route::get('/', Home::class)->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role->name === 'lawyer') {
            return redirect()->route('lawyer.dashboard');
        } elseif (auth()->user()->role->name === 'client') {
            return redirect()->route('client.welcome');
        } elseif (auth()->user()->role->name === 'law_firm') {
            return redirect()->route('law-firm.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');
});

// Custom Authentication Routes
Route::get('/register', Register::class)->name('register');

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/complete', CompleteProfile::class)->name('profile.complete');
    Route::get('/profile/pending', function () {
        return view('profile.pending');
    })->name('profile.pending');
    Route::get('/account/deactivated', function () {
        return view('auth.account-deactivated');
    })->name('account.deactivated');
});

// Lawyer Routes
Route::middleware(['auth', 'profile.completed'])->group(function () {
    Route::get('/lawyers', LawyerSearch::class)->name('lawyers.search');
    Route::get('/lawyers/{lawyer}', LawyerProfile::class)->name('lawyers.profile');
    
    // Use the client messages view for clients, otherwise use the regular Chat component
    Route::get('/messages', function() {
        if (auth()->check() && auth()->user()->role->name === 'client') {
            return view('client.messages.index');
        }
        return view('messages.index');
    })->name('messages');
    
    Route::get('/messages/{userId?}', Chat::class)->name('messages.chat');
    Route::get('/lawyers/profile/optimize', \App\Livewire\Lawyers\OptimizeProfile::class)->name('lawyers.optimize-profile');
    Route::get('/lawyers/search', [App\Http\Controllers\LawyerController::class, 'search'])->name('lawyers.search');
    Route::get('/notifications', \App\Livewire\Notifications\AllNotifications::class)->name('notifications.all');
});

// Provider Routes
Route::get('/providers/{user}', Show::class)->name('providers.show');
Route::get('/law-firms/{lawFirmProfile}', \App\Http\Controllers\LawFirmController::class)->name('law-firms.show');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [App\Livewire\Admin\Dashboard::class, '__invoke'])->name('admin.dashboard');
    Route::get('/pending-users', App\Livewire\Admin\PendingUsers::class)->name('admin.pending-users');
    Route::get('/user-status-management', App\Livewire\Admin\UserStatusManagement::class)->name('admin.user-status-management');
    Route::get('/deactivated-users', App\Livewire\Admin\DeactivatedUsers::class)->name('admin.deactivated-users');
    Route::get('/service-management', App\Livewire\Admin\ServiceManagement::class)->name('admin.service-management');
    Route::get('/client-report-management', App\Livewire\Admin\ClientReportManagement::class)->name('admin.client-report-management');
    Route::get('/investigation/{reportId}', App\Livewire\Admin\InvestigationDashboard::class)->name('admin.investigation.dashboard');
    Route::get('/investigation/attachment/download/{attachmentId}', [App\Http\Controllers\InvestigationAttachmentController::class, 'download'])->name('investigation.attachment.download');
    Route::get('/sales-panel', App\Livewire\Admin\SalesPanel::class)->name('admin.sales-panel');
    
    // Subscription Management
    Route::get('/subscriptions', [App\Http\Controllers\Admin\SubscriptionManagementController::class, 'index'])->name('admin.subscriptions.index');
    Route::get('/subscriptions/{subscription}', [App\Http\Controllers\Admin\SubscriptionManagementController::class, 'show'])->name('admin.subscriptions.show');
    Route::post('/subscriptions/{subscription}/cancel', [App\Http\Controllers\Admin\SubscriptionManagementController::class, 'cancel'])->name('admin.subscriptions.cancel');
    
    // Maintenance Management
    Route::get('/maintenance', [App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('admin.maintenance.index');
    Route::post('/maintenance', [App\Http\Controllers\Admin\MaintenanceController::class, 'store'])->name('admin.maintenance.store');
    Route::post('/maintenance/immediate', [App\Http\Controllers\Admin\MaintenanceController::class, 'enableImmediate'])->name('admin.maintenance.immediate');
    Route::patch('/maintenance/{schedule}/cancel', [App\Http\Controllers\Admin\MaintenanceController::class, 'cancel'])->name('admin.maintenance.cancel');
    Route::get('/maintenance/status', [App\Http\Controllers\Admin\MaintenanceController::class, 'status'])->name('admin.maintenance.status');
});

// Super Admin Routes
Route::middleware(['auth', \App\Http\Middleware\SuperAdminMiddleware::class])->prefix('super-admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('super-admin.dashboard');
    
    // Departments
    Route::get('/departments', [App\Http\Controllers\SuperAdmin\DepartmentController::class, 'index'])->name('super-admin.departments.index');
    Route::post('/departments', [App\Http\Controllers\SuperAdmin\DepartmentController::class, 'store'])->name('super-admin.departments.store');
    Route::get('/departments/{department}/edit', [App\Http\Controllers\SuperAdmin\DepartmentController::class, 'edit'])->name('super-admin.departments.edit');
    Route::put('/departments/{department}', [App\Http\Controllers\SuperAdmin\DepartmentController::class, 'update'])->name('super-admin.departments.update');
    Route::delete('/departments/{department}', [App\Http\Controllers\SuperAdmin\DepartmentController::class, 'destroy'])->name('super-admin.departments.destroy');
    
    // Permissions
    Route::get('/permissions', [App\Http\Controllers\SuperAdmin\PermissionController::class, 'index'])->name('super-admin.permissions.index');
    Route::post('/permissions', [App\Http\Controllers\SuperAdmin\PermissionController::class, 'store'])->name('super-admin.permissions.store');
    Route::get('/permissions/{permission}/edit', [App\Http\Controllers\SuperAdmin\PermissionController::class, 'edit'])->name('super-admin.permissions.edit');
    Route::put('/permissions/{permission}', [App\Http\Controllers\SuperAdmin\PermissionController::class, 'update'])->name('super-admin.permissions.update');
    Route::delete('/permissions/{permission}', [App\Http\Controllers\SuperAdmin\PermissionController::class, 'destroy'])->name('super-admin.permissions.destroy');
    
    // Department Users
    Route::get('/users', [App\Http\Controllers\SuperAdmin\UserController::class, 'index'])->name('super-admin.users.index');
    Route::post('/users', [App\Http\Controllers\SuperAdmin\UserController::class, 'store'])->name('super-admin.users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\SuperAdmin\UserController::class, 'edit'])->name('super-admin.users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\SuperAdmin\UserController::class, 'update'])->name('super-admin.users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\SuperAdmin\UserController::class, 'destroy'])->name('super-admin.users.destroy');
    
    // User Permissions (Direct Assignment)
    Route::get('/user-permissions', [App\Http\Controllers\SuperAdmin\UserPermissionController::class, 'index'])->name('super-admin.user-permissions.index');
    Route::get('/user-permissions/{user}/edit', [App\Http\Controllers\SuperAdmin\UserPermissionController::class, 'edit'])->name('super-admin.user-permissions.edit');
    Route::put('/user-permissions/{user}', [App\Http\Controllers\SuperAdmin\UserPermissionController::class, 'update'])->name('super-admin.user-permissions.update');
});

// Law Firm Routes - for management
Route::middleware(['auth', 'profile.completed', 'approved'])->prefix('law-firm')->group(function () {
    Route::get('/lawyers', function() { return view('law-firm.lawyers'); })->name('law-firm.lawyers');
    Route::get('/invoices', function() { return view('law-firm.invoices'); })->name('law-firm.invoices');
    Route::get('/cases', function() { return view('law-firm.cases'); })->name('law-firm.cases');
    Route::get('/consultations', function() { return view('law-firm.consultations'); })->name('law-firm.consultations');
    Route::get('/lawyers/create', [LawyerController::class, 'create'])->name('law-firm.lawyers.create');
    Route::post('/lawyers', [LawyerController::class, 'store'])->name('law-firm.lawyers.store');
    Route::get('/case/details/{case}', \App\Livewire\LawFirm\CaseDetails::class)->name('law-firm.case-details');
    Route::get('/case/setup/{case}', function(\App\Models\LegalCase $case) {
        return view('law-firm.case-setup', ['case' => $case]);
    })->name('law-firm.case.setup');
    Route::get('/availability', \App\Livewire\LawFirm\SetAvailability::class)->name('law-firm.availability');
    Route::get('/start-case', \App\Livewire\LawFirm\StartCase::class)->name('law-firm.start-case');
    Route::get('/start-case/{consultation}', \App\Livewire\LawFirm\StartCase::class)->name('law-firm.start-case-from-consultation');
});

// Test Route - Direct middleware
Route::get('/test-law-firm', function () {
    return 'Law Firm Middleware Working!';
})->middleware(\App\Http\Middleware\LawFirmMiddleware::class);

Route::get('/services', Services::class)->name('services');

// Dashboard Routes
Route::middleware(['auth', 'verified', 'not.deactivated'])->group(function () {
    Route::get('/lawyer/welcome', function () {
        return view('lawyers.welcome');
    })->middleware(\App\Http\Middleware\LawyerMiddleware::class)->name('lawyer.welcome');

    Route::get('/lawyer/cases', ManageCases::class)
        ->middleware(\App\Http\Middleware\LawyerMiddleware::class)
        ->name('lawyer.cases');

    Route::get('/law-firm/dashboard', function () {
        return view('dashboards.law-firm');
    })->name('law-firm.dashboard');
});

// Profile Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile/optimize', [OptimizeProfileController::class, 'edit'])
        ->name('profile.optimize');
    Route::put('/profile/optimize', [OptimizeProfileController::class, 'update'])
        ->name('profile.optimize.update');
    Route::get('/law-firm/optimize-profile', \App\Livewire\LawFirm\OptimizeProfile::class)
        ->name('law-firm.optimize-profile');
});

// Client routes
Route::prefix('client')->middleware(['auth', 'verified', 'not.deactivated', 'profile.completed', 'client'])->group(function () {
    Route::get('/welcome', App\Livewire\Client\Dashboard::class)->name('client.welcome');
    
    Route::get('/nearby-lawyers', App\Livewire\Lawyers\NearbyLawyers::class)->name('client.nearby-lawyers');
    
    Route::get('/cases', function () {
        return view('client.cases.index');
    })->name('client.cases');
    
    Route::get('/cases/{case}', App\Livewire\Client\ViewCase::class)->name('client.cases.show');
    Route::get('/case/details/{case}', App\Livewire\Client\CaseDetails::class)->name('client.case.details');
    Route::get('/contract/review/{case}', App\Livewire\Client\ContractReview::class)->name('client.contract.review');
    Route::get('/case/overview/{case}', App\Livewire\Client\CaseView::class)->name('client.case.overview');
    Route::get('/case/view/{case}', App\Livewire\Client\CaseView::class)->name('client.case.view');
    
    Route::get('/consultations', function () {
        return view('client.consultations.index');
    })->name('client.consultations');
    
    Route::get('/book-consultation/{lawyer_id}', App\Livewire\Client\BookConsultation::class)->name('client.book-consultation');
    Route::get('/start-case', App\Livewire\Client\StartCase::class)->name('client.start-case');
    Route::get('/start-case/{lawyer_id}', App\Livewire\Client\StartCase::class)->name('client.start-case.with-lawyer');
    
    Route::get('/payment/card/{invoice}', [App\Http\Controllers\PaymentController::class, 'showCardPayment'])->name('client.payment.card');
    Route::post('/payment/card/process', [App\Http\Controllers\PaymentController::class, 'processCardPayment'])->name('client.payment.card.process');
    
    // New Invoice Management Route
    Route::get('/invoices', function () {
        return view('client.invoices.index');
    })->name('client.invoices');
});

// Add search lawyers route
Route::get('/search-lawyers', function() {
    return redirect()->route('client.nearby-lawyers', request()->query());
})->name('search.lawyers');

// Lawyer routes
Route::prefix('lawyer')->middleware([
    'auth',
    'verified',
    \App\Http\Middleware\LawyerMiddleware::class,
    'profile.completed',
    'not.deactivated'
])->group(function () {
    Route::get('/dashboard', App\Livewire\Lawyer\Dashboard::class)->name('lawyer.dashboard');
    Route::get('/welcome', function () {
        return redirect()->route('lawyer.dashboard');
    })->name('lawyer.welcome');
    Route::get('/consultations', App\Livewire\Lawyers\ManageConsultations::class)->name('lawyer.consultations');
    
    // Apply availability middleware to control access to the availability tab
    Route::get('/availability', function () {
        return redirect()->route('lawyer.consultations', ['activeTab' => 'availability']);
    })->middleware(\App\Http\Middleware\LawyerAvailabilityMiddleware::class)->name('lawyer.availability');
    
    Route::get('/cases', App\Livewire\Lawyers\ManageCases::class)->name('lawyer.cases');
    Route::get('/cases/{case}', App\Livewire\Lawyer\CaseDetails::class)->name('lawyer.cases.show');
    Route::get('/case/setup/{case}', App\Livewire\Lawyer\CaseSetup::class)->name('lawyer.case.setup');
    
    // Signature route
    Route::get('/signature/{caseId}', [App\Http\Controllers\SignatureController::class, 'showSignature'])->name('signature.show');
    
    // New Invoice Management Route
    Route::get('/invoices', App\Livewire\Lawyer\InvoiceManagement::class)->name('lawyer.invoices');
    
    // Consultation routes
    Route::prefix('consultations')->name('consultations.')->group(function () {
        // Create legal case from consultation
        Route::get('{consultation}/create-case', [App\Http\Controllers\Lawyer\ConsultationController::class, 'showCreateCaseForm'])->name('create-case-form');
        Route::post('{consultation}/create-case', [App\Http\Controllers\Lawyer\ConsultationController::class, 'createCase'])->name('create-case');
    });
});

// Payment Routes
Route::post('/payments/webhook', [App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');
Route::get('/payments/success/{invoice}', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
Route::get('/payments/failed/{invoice}', [App\Http\Controllers\PaymentController::class, 'failed'])->name('payment.failed');

// Terms and Privacy routes
Route::get('/terms-of-service', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy-policy', function () {
    return view('privacy');
})->name('policy');

// Subscription routes - add these routes for both lawyers and law firms
Route::middleware(['auth', 'verified', 'profile.completed', 'not.deactivated'])
    ->prefix('subscriptions')
    ->group(function () {
        Route::get('/', [App\Http\Controllers\SubscriptionController::class, 'index'])
            ->name('subscriptions.index');
        Route::get('/checkout/{plan}', [App\Http\Controllers\SubscriptionController::class, 'checkout'])
            ->name('subscriptions.checkout');
        Route::post('/process', [App\Http\Controllers\SubscriptionController::class, 'process'])
            ->name('subscriptions.process');
        Route::post('/create-payment-intent', [App\Http\Controllers\SubscriptionController::class, 'createPaymentIntent'])
            ->name('subscriptions.create-payment-intent');
        Route::post('/process-subscription', [App\Http\Controllers\SubscriptionController::class, 'processSubscription'])
            ->name('subscriptions.process-subscription');
        Route::post('/process-card-payment', [App\Http\Controllers\SubscriptionController::class, 'processCardPayment'])
            ->name('subscriptions.process-card-payment');
        Route::get('/success', [App\Http\Controllers\SubscriptionController::class, 'success'])
            ->name('subscriptions.success');
        Route::post('/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])
            ->name('subscriptions.cancel');
    });

// Account Management Routes
Route::middleware(['auth', 'verified', 'profile.completed', 'not.deactivated'])
    ->prefix('account')
    ->group(function () {
        Route::get('/subscription', [App\Http\Controllers\SubscriptionController::class, 'accountSubscription'])
            ->name('account.subscription');
    });

// Add this route where appropriate, e.g., in a client or lawyer middleware group
Route::get('/cases/create', \App\Livewire\CaseCreation::class)->name('case.create');

// Client Document Upload
Route::get('/client/documents/upload/{case}', [App\Http\Controllers\Client\DocumentController::class, 'upload'])->name('client.documents.upload');

// Add lawyer and law firm profile routes
Route::get('/lawyer/{user}', App\Livewire\LawyerProfile::class)->name('lawyer.profile');
Route::get('/law-firm/{user}', App\Livewire\LawFirmProfile::class)->name('law-firm.profile');
