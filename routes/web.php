<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Admin Livewire Components
use App\Livewire\Admin\AdminDashboardController;
use App\Livewire\Admin\AdminIndexTestimonials;
use App\Livewire\Admin\BookingManager;
use App\Livewire\Admin\BootcampManager;
use App\Livewire\Admin\ServiceCatalogManager;
use App\Livewire\Admin\Newsletter;
use App\Livewire\Admin\TutorprofileManager;
use App\Livewire\TermsOfService;
use App\Livewire\Admin\RequestManager;
use App\Livewire\Admin\ClientManager;
use App\Livewire\Admin\ContactMessages;
use App\Livewire\Admin\UserManager;
use App\Livewire\Admin\InstitutionRequestManager;
use App\Livewire\Admin\PaymentsManager;
use App\Livewire\Admin\TutorProfileView;

// Tutor Routes
use App\Livewire\Tutor\DashboardController;
use App\Livewire\Tutor\TutorLessons;
use App\Livewire\Tutor\Payments;
use App\Livewire\Tutor\TutorProfiles;
use App\Livewire\Tutor\InstitutionAssignments;
use App\Livewire\Seo\ServiceCatalogPage;
use App\Livewire\Seo\ServiceItemLandingPage;
use App\Livewire\Pages\WelcomePage;
use App\Livewire\Pages\AboutPage;
use App\Livewire\Pages\ContactPage;
use App\Livewire\Pages\BootcampPage;
use App\Livewire\Pages\PrivacyPolicyPage;

use Illuminate\Http\Request;
use App\Models\ServiceItem;
use App\Models\User;

// Client Livewire Components
use App\Livewire\Client\CrmManager;
use App\Livewire\Client\DashboardController as ClientDashboard;
use App\Livewire\Client\TutorRequestsManager;
use App\Livewire\Client\Lessons;
use App\Livewire\Requests\CrmRequestWizard;
use App\Livewire\Requests\TutorRequestWizard;
use App\Livewire\Testimonials\Testimonials;
use App\Livewire\Testimonials\IndexTestimonials;
use App\Http\Controllers\PaystackController;

use App\Livewire\Partials\UserProfileEditor;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', WelcomePage::class)->name('welcome');

Route::get('/services', ServiceCatalogPage::class)->name('services');
Route::get('/services/{serviceItem:slug}', ServiceItemLandingPage::class)->name('services.show');

Route::get('/about', AboutPage::class)->name('about');

Route::get('/contact', ContactPage::class)->name('contact');

Route::get('/bootcamp', BootcampPage::class)->name('bootcamp');

Route::get('/privacy-policy', PrivacyPolicyPage::class)->name('privacy-policy');

// Guest-accessible request forms (multi-step UI)
Route::get('/apply/tutor/{serviceItem:slug}', TutorRequestWizard::class)->name('apply.tutor');
Route::get('/apply/crm/{serviceItem:slug}', CrmRequestWizard::class)->name('apply.crm');
Route::get('/apply/bootcamp/{serviceItem:slug}', BootcampPage::class)->name('apply.bootcamp');


Route::get('/terms-of-service', TermsOfService::class)->name('terms.service');
Route::get('testimonials', IndexTestimonials::class)->name('testimonials.index');

//Unsubscribe Route
Route::get('/unsubscribe/{user}', function (Request $request, User $user) {
    if (! $request->hasValidSignature()) {
        return response()->view('newsletter.unsubscribe', [
            'success' => false,
            'message' => 'This unsubscribe link is invalid or has expired.',
        ], 401);
    }

    $user->update(['is_subscribed' => false]);

    return view('newsletter.unsubscribe', [
        'success' => true,
        'message' => 'You have been successfully unsubscribed from MephEd newsletters.',
    ]);
})->name('newsletter.unsubscribe');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('add-testimonial', Testimonials::class)->name('testimonial');
});

Route::middleware(['auth', 'can:AdminOrClient'])->group(function () {
    Route::get('user-profile-editor', UserProfileEditor::class)->name('userProfile');
});

Route::middleware(['auth', 'can:Client'])->group(function () {
    // Client Dashboard
    Route::get('/dashboard', ClientDashboard::class)->name('client.dashboard');
    Route::get('/client/lessons', Lessons::class)->name('client.lessons');
    // Livewire manager pages
    Route::get('client/crm-manager', CrmManager::class)->name('client.crm.manager');
    Route::get('client/tutor-requests-manager', TutorRequestsManager::class)->name('client.tutorRequests.manager');
    Route::get('/paystack/callback', [PaystackController::class, 'callback'])->name('paystack.callback');
    Route::post('/paystack/webhook', [PaystackController::class, 'webhook']);
    
});

Route::middleware(['auth', 'can:Tutor', 'verified'])->group(function () {
    // Tutor Dashboard
    Route::get('/tutor/dashboard', DashboardController::class)->name('tutor.dashboard');
    Route::get('/tutor/lessons', TutorLessons::class)->name('tutor.lessons');
    Route::get('/tutor/tutor-profile', TutorProfiles::class)->name('tutor.tutor-profile');
    Route::get('/tutor/payments', Payments::class)->name('tutor.payments');
    Route::get('/tutor/institution-assignments', InstitutionAssignments::class)->name('tutor.institution-assignments');
   
});

Route::middleware(['auth', 'can:Admin', 'verified'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');    
    //Tutor Request Routes
    Route::get('admin/tutor-requests', RequestManager::class)->name('tutorRequests.index');
    Route::get('admin/testimonials', AdminIndexTestimonials::class)->name('admin.testimonials');

    //Bookings (consolidated booking manager)
    Route::get('admin/lessons', BookingManager::class)->name('admin.lessons');
    Route::get('admin/bootcamps', BootcampManager::class)->name('admin.bootcamps');
    Route::get('admin/service-catalog', ServiceCatalogManager::class)->name('admin.serviceCatalog');

    //Crm Routes
    Route::get('admin/intitution-requests', InstitutionRequestManager::class)->name('admin.crm.index');

    //Payments Routes
    Route::get('admin/payments', PaymentsManager::class)->name('admin.payments.index');

    //Users Management Routes
    Route::get('admin/user-manager', UserManager::class)->name('admin.users');
    Route::get('admin/tutor-profile-management', TutorprofileManager::class)->name('admin.tutorProfile');
    Route::get('/admin/tutors/{profile}', TutorProfileView::class)->name('admin.tutors.view');
    Route::get('admin/client-management', ClientManager::class)->name('admin.clientManager');

    Route::get('admin/contact-messages', ContactMessages::class)->name('admin.contactMessages');
    Route::get('admin/newsletter', Newsletter::class)->name('admin.newsletter');
  

});

require __DIR__.'/auth.php';
