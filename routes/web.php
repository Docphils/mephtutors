<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Livewire\Admin\AdminDashboardController;
use App\Livewire\Admin\AdminIndexTestimonials;
use App\Livewire\Admin\Lesson\Create;
use App\Livewire\Admin\Lesson\EditLesson;
use App\Livewire\Admin\Newsletter;
use App\Livewire\Admin\TutorprofileManager;
use App\Livewire\Client\CodingAndClubs;
use App\Livewire\Client\CodingAndClubsIndex;
use App\Livewire\TermsOfService;
use App\Livewire\Client\TutorRequests\CreateRequest;
use App\Livewire\Admin\TutorRequests\RequestIndex;
use App\Livewire\Client\TutorRequests\ClientRequests;
use App\Livewire\Client\TutorRequests\EditRequest;
use App\Livewire\Client\TutorRequests\ShowRequest;
use App\Livewire\Testimonials\Testimonials;
use App\Livewire\Testimonials\IndexTestimonials;
use App\Livewire\Tutor\DashboardController;


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
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/bootcamp', function () {
    return view('bootcamp');
});

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
});



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
    Route::get('/client/dashboard', [ClientDashboardController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('client.dashboard');
    Route::get('/client/lessons', [BookingController::class, 'clientBookings'])->name('client.lessons');

    //Crm Routes
    Route::get('client/coding-tutor-requests', CodingAndClubsIndex::class)->name('codingRequest.index');
    Route::get('client/club-requests', CodingAndClubs::class)->name('clubRequest.index');
    Route::get('client/crm/{id}/edit', [CrmController::class, 'clientedit'])->name('client.crm.edit');
    Route::put('client/crm/{id}', [CrmController::class, 'update'])->name('client.crm.update');
    Route::delete('client/crm/{id}', [CrmController::class, 'clientDestroy'])->name('client.crm.destroy');
    //Tutor Requests Routes
    Route::get('client/tutor-requests', ClientRequests::class)->name('client.tutorRequests.index');
    Route::get('/client/tutor-requests/{id}/edit', EditRequest::class)->name('client.tutorRequests.edit');
    Route::get('/tutor-requests/create', CreateRequest::class)->name('client.tutorRequests.create');
    Route::get('/tutor-requests/{id}', ShowRequest::class)->name('client.tutorRequests.show');

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
    Route::get('admin/tutor-requests', RequestIndex::class)->name('tutorRequests.index');
    Route::get('admin/testimonials', AdminIndexTestimonials::class)->name('admin.testimonials');

    //Bookings
    Route::get('/lessons/create', Create::class)->name('bookings.create');
    Route::get('/lessons/{id}/edit', EditLesson::class)->name('bookings.edit');
    Route::get('admin/lessons', [BookingController::class, 'index'])->name('lessons.index');

    //Crm Routes
    Route::get('admin/crm', [CrmController::class, 'index'])->name('admin.crm.index');
    Route::get('admin/crm/{id}', [CrmController::class, 'show'])->name('admin.crm.show');
    Route::get('admin/crm/{id}/edit', [CrmController::class, 'edit'])->name('admin.crm.edit');
    Route::delete('admin/crm/{id}', [CrmController::class, 'destroy'])->name('admin.crm.destroy');
    Route::patch('admin/crm/{id}/status', [CrmController::class, 'updateStauts'])->name('admin.crm.updateStatus');

    //Payments Routes
    Route::get('admin/payments', [PaymentController::class, 'index'])->name('admin.payments.index');


    //Users Management Routes
    Route::get('admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('admin/tutor-profile-management', TutorprofileManager::class)->name('admin.tutorProfile');
    Route::get('admin/newsletter', Newsletter::class)->name('admin.newsletter');
  

});

require __DIR__.'/auth.php';
