<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\PaymentController;
// Admin Livewire Components
use App\Livewire\Admin\AdminDashboardController;
use App\Livewire\Admin\AdminIndexTestimonials;
use App\Livewire\Admin\BookingManager;
use App\Livewire\Admin\Newsletter;
use App\Livewire\Admin\TutorprofileManager;
use App\Livewire\TermsOfService;
use App\Livewire\Admin\RequestManager;
use App\Livewire\Admin\ClientManager;
use App\Livewire\Admin\ContactMessages;
use App\Livewire\Admin\UserManager;

use App\Livewire\Testimonials\Testimonials;
use App\Livewire\Testimonials\IndexTestimonials;
use App\Livewire\Tutor\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\GuestRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
//Client Livewire Components
use App\Livewire\Client\CrmManager;
use App\Livewire\Client\DashboardController as ClientDashboard;
use App\Livewire\Client\TutorRequestsManager;
use App\Livewire\Client\Lessons;
use App\Livewire\Requests\CrmRequestWizard;
use App\Livewire\Requests\TutorRequestWizard;
use App\Http\Controllers\PaystackController;
use App\Models\ServiceItem;


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

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/services', function () {
    return view('services');
})->name('services');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/bootcamp', function () {
    return view('bootcamp');
})->name('bootcamp');

Route::get('/privacy-policy', function () {
    return view('privacy-policy')->name('privacy-policy');
});

// Guest-accessible request forms (multi-step UI)
Route::get('/apply/tutor/{serviceItem:slug}', TutorRequestWizard::class)->name('apply.tutor');
Route::get('/apply/crm/{serviceItem:slug}', CrmRequestWizard::class)->name('apply.crm');


Route::get('/terms-of-service', TermsOfService::class)->name('terms.service');
Route::get('testimonials', IndexTestimonials::class)->name('testimonials.index');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('add-testimonial', Testimonials::class)->name('testimonial');
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
   
    //Bookings
    Route::post('bookings/{booking}/tutorRemarks', [BookingController::class, 'addTutorRemarks'])->name('bookings.addTutorRemarks');

    //Payment Routes
    Route::get('tutor/payments', [PaymentController::class, 'tutorIndex'])->name('tutor.payments.index');
    Route::get('tutor/payments/{id}', [PaymentController::class, 'toturShow'])->name('tutor.payments.show');


});

Route::middleware(['auth', 'can:Admin', 'verified'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');    
    //Tutor Request Routes
    Route::get('admin/tutor-requests', RequestManager::class)->name('tutorRequests.index');
    Route::get('admin/testimonials', AdminIndexTestimonials::class)->name('admin.testimonials');

    //Bookings (consolidated booking manager)
    Route::get('admin/lessons', BookingManager::class)->name('admin.lessons');

    //Crm Routes
    Route::get('admin/crm', [CrmController::class, 'index'])->name('admin.crm.index');
    Route::get('admin/crm/{id}', [CrmController::class, 'show'])->name('admin.crm.show');
    Route::get('admin/crm/{id}/edit', [CrmController::class, 'edit'])->name('admin.crm.edit');
    Route::delete('admin/crm/{id}', [CrmController::class, 'destroy'])->name('admin.crm.destroy');
    Route::patch('admin/crm/{id}/status', [CrmController::class, 'updateStauts'])->name('admin.crm.updateStatus');

    //Payments Routes
    Route::get('admin/payments', [PaymentController::class, 'index'])->name('admin.payments.index');


    //Users Management Routes
    Route::get('admin/user-manager', UserManager::class)->name('admin.users');
    Route::get('admin/tutor-profile-management', TutorprofileManager::class)->name('admin.tutorProfile');
    Route::get('admin/client-management', ClientManager::class)->name('admin.clientManager');

    Route::get('admin/contact-messages', ContactMessages::class)->name('admin.contactMessages');
    Route::get('admin/newsletter', Newsletter::class)->name('admin.newsletter');
  

});

require __DIR__.'/auth.php';
