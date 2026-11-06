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
use App\Livewire\Admin\OnlineMeetingManager;
use App\Livewire\Client\OnlineMeetings as ClientOnlineMeetings;
use App\Livewire\Tutor\OnlineMeetings as TutorOnlineMeetings;

// Tutor Routes
use App\Livewire\Tutor\DashboardController;
use App\Livewire\Tutor\TutorLessons;
use App\Livewire\Tutor\Payments;
use App\Livewire\Tutor\TutorProfiles;
use App\Livewire\Tutor\InstitutionAssignments;
use App\Livewire\Tutor\ProgrammeAssignments;
use App\Livewire\Seo\ServiceCatalogPage;
use App\Livewire\Seo\ServiceItemLandingPage;
use App\Livewire\Pages\WelcomePage;
use App\Livewire\Pages\ProgrammePage;
use App\Livewire\Pages\AboutPage;
use App\Livewire\Pages\ContactPage;
use App\Livewire\EnrollmentForm;
use App\Livewire\Pages\PrivacyPolicyPage;
use App\Livewire\Pages\ChurchLeadForm;
use App\Livewire\Pages\ProductsPage;
use App\Livewire\Requests\ProgrammeEnquiryWizard;
use App\Livewire\Requests\ProgrammeConsultationForm;
use App\Livewire\Admin\ProgrammeSettingsManager;
use App\Livewire\Admin\ProgrammeEnquiriesManager;
use App\Livewire\Admin\ChurchLeadsManager;
use App\Livewire\Admin\ProductsManager;
use App\Livewire\Admin\EbooksManager;
use App\Livewire\Admin\EbookPoliciesManager;
use App\Livewire\Admin\EbookOrdersManager;
use App\Livewire\Admin\EbookOperationsDashboard;
use App\Livewire\Ebooks\EbookLandingPage;
use App\Livewire\Ebooks\EbookHubPage;
use App\Livewire\Ebooks\FreeChapterPage;
use App\Livewire\Ebooks\EbookThankYouPage;
use App\Livewire\Ebooks\EbookDownloadExpiredPage;
use App\Http\Controllers\EbookPaymentController;
use App\Http\Controllers\EbookFulfillmentController;

use Illuminate\Http\Request;
use App\Models\AcademicProgramme;
use App\Models\EbookPolicy;
use App\Models\ServiceItem;
use App\Models\User;

// Client Livewire Components
use App\Livewire\Client\CrmManager;
use App\Livewire\Client\DashboardController as ClientDashboard;
use App\Livewire\Client\InterventionRequestCreate;
use App\Livewire\Client\ProgrammeRequestsManager;
use App\Livewire\Client\TutorRequestsManager;
use App\Livewire\Client\Lessons;
use App\Livewire\Requests\CrmRequestWizard;
use App\Livewire\Requests\TutorRequestWizard;
use App\Livewire\Testimonials\Testimonials;
use App\Livewire\Testimonials\IndexTestimonials;
use App\Livewire\Pages\BootcampEnrollmentSuccess;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\OnlineMeetingController;

use App\Livewire\Partials\UserProfileEditor;

use App\Http\Controllers\BlogFeedController;
use App\Livewire\Admin\BlogEditor;
use App\Livewire\Admin\BlogPreview;
use App\Livewire\Admin\BlogSettingsManager;
use App\Livewire\Admin\BlogStudio;
use App\Livewire\Blog\BlogArticle;
use App\Livewire\Blog\BlogIndex;
use App\Models\BlogPost;

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
Route::view('/offline', 'offline')->name('offline');
Route::get('/programmes/enquiry', ProgrammeConsultationForm::class)->name('programmes.enquiry');
Route::get('/programmes/{academicProgramme:slug}/request', ProgrammeEnquiryWizard::class)->name('programmes.request');
Route::get('/programmes/enquiry/{programme}', function (string $programme) {
    return redirect()->route('programmes.enquiry', ['programme' => $programme]);
})->name('programmes.enquiry.prefilled');
Route::get('/programmes/{academicProgramme:slug}', ProgrammePage::class)->name('programmes.show');

Route::get('/services', ServiceCatalogPage::class)->name('services');
Route::get('/services/{serviceItem:slug}', ServiceItemLandingPage::class)->name('services.show');
Route::get('/products', ProductsPage::class)->name('products.index');
Route::get('/ebooks', EbookHubPage::class)->name('ebooks.index');
Route::get('/health', fn () => response()->json(['status' => 'ok'], 200)->header('Cache-Control', 'no-store'))->name('health');
Route::get('/legal/{policy}', function (string $policy) {
    $policy = EbookPolicy::query()
        ->where('slug', $policy)
        ->where('is_published', true)
        ->firstOrFail();

    return view('ebooks.policy', compact('policy'));
})->whereIn('policy', EbookPolicy::REQUIRED_SLUGS)->name('ebooks.policy');
Route::get('/ebooks/{slug}/free-chapter', FreeChapterPage::class)->name('ebooks.free-chapter');
Route::get('/ebooks/{slug}/free-chapter.pdf', [EbookFulfillmentController::class, 'freeChapter'])->name('ebooks.free-chapter.pdf');
Route::get('/ebooks/{slug}/cheat-sheet', [EbookFulfillmentController::class, 'cheatSheet'])->middleware('throttle:ebooks-downloads')->name('ebooks.cheat-sheet');
Route::get('/ebooks/{slug}', EbookLandingPage::class)->name('ebooks.show');
Route::get('/ebooks/thank-you/{reference}', EbookThankYouPage::class)->middleware(['signed', 'private.nostore'])->name('ebooks.thank-you');
Route::get('/ebooks/thank-you/{reference}/download', [EbookFulfillmentController::class, 'thankYouDownload'])->middleware(['signed', 'private.nostore', 'throttle:ebooks-downloads'])->name('ebooks.thank-you.download');
Route::post('/ebooks/thank-you/{reference}/resend', [EbookFulfillmentController::class, 'thankYouResend'])->middleware(['signed', 'private.nostore', 'throttle:5,1'])->name('ebooks.thank-you.resend');
Route::get('/ebooks/download/{token}/expired', EbookDownloadExpiredPage::class)->middleware(['private.nostore', 'throttle:ebooks-downloads'])->name('ebooks.download.expired');
Route::get('/ebooks/download/{token}', [EbookFulfillmentController::class, 'download'])->middleware(['private.nostore', 'throttle:ebooks-downloads'])->name('ebooks.download');
Route::post('/ebooks/download/{token}/resend', [EbookFulfillmentController::class, 'resend'])->middleware('throttle:5,1')->name('ebooks.download.resend');
Route::post('/ebooks/payments/verify', [EbookPaymentController::class, 'verify'])->middleware('throttle:10,1')->name('ebooks.payments.verify');

Route::get('/about', AboutPage::class)->name('about');

Route::get('/contact', ContactPage::class)->name('contact');

Route::get('/blog', BlogIndex::class)->name('blog.index');
Route::get('/blog/rss.xml', BlogFeedController::class)->name('blog.feed');
Route::get('/blog/category/{slug}', BlogIndex::class)->name('blog.category');
Route::get('/blog/tag/{slug}', BlogIndex::class)->name('blog.tag');
Route::get('/blog/{slug}', BlogArticle::class)->name('blog.show');

Route::get('/bootcamp', EnrollmentForm::class)->name('bootcamp');
Route::get('/bootcamp/enrollment/success/{enrollee}', BootcampEnrollmentSuccess::class)
    ->name('bootcamp.enrollment.success');

Route::get('/privacy-policy', PrivacyPolicyPage::class)->name('privacy-policy');
Route::get('/church-management/enquiry', ChurchLeadForm::class)->name('church-leads.create');

//Recipe Aid's Privacy Policy
Route::get('/recipe-aid-privacy-policy', function () {
    return view('recipe-aid-privacy-policy');
})->name('recipe-aid-privacy-policy');

Route::get('/paystack/callback', [PaystackController::class, 'callback'])->name('paystack.callback');
Route::post('/paystack/webhook', [PaystackController::class, 'webhook'])->name('paystack.webhook');

Route::get('/sitemap.xml', function () {
    $pages = [
        ['loc' => route('welcome'), 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => now()->toDateString()],
        ['loc' => route('services'), 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => now()->toDateString()],
        ['loc' => route('products.index'), 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => now()->toDateString()],
        ['loc' => route('ebooks.index'), 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => now()->toDateString()],
        ['loc' => route('bootcamp'), 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => now()->toDateString()],
        ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => now()->toDateString()],
        ['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.6', 'lastmod' => now()->toDateString()],
        ['loc' => route('privacy-policy'), 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => now()->toDateString()],
        ['loc' => route('programmes.enquiry'), 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => now()->toDateString()],
        ['loc' => route('llms'), 'changefreq' => 'weekly', 'priority' => '0.5', 'lastmod' => now()->toDateString()],
        ['loc' => route('llms.full'), 'changefreq' => 'weekly', 'priority' => '0.5', 'lastmod' => now()->toDateString()],
    ];

    $serviceItems = ServiceItem::query()
        ->where('is_active', true)
        ->orderBy('updated_at', 'desc')
        ->get(['slug', 'updated_at'])
        ->map(fn ($item) => [
            'loc' => route('services.show', ['serviceItem' => $item->slug]),
            'changefreq' => 'weekly',
            'priority' => '0.8',
            'lastmod' => optional($item->updated_at)->toDateString() ?? now()->toDateString(),
        ]);

    $programmes = AcademicProgramme::query()
        ->where('is_active', true)
        ->orderBy('updated_at', 'desc')
        ->get(['slug', 'updated_at'])
        ->map(fn ($programme) => [
            'loc' => route('programmes.show', ['academicProgramme' => $programme->slug]),
            'changefreq' => 'weekly',
            'priority' => '0.8',
            'lastmod' => optional($programme->updated_at)->toDateString() ?? now()->toDateString(),
        ]);

    $ebookPages = \App\Models\Ebook::query()
        ->where('status', \App\Enums\EbookStatus::Published)
        ->orderBy('updated_at', 'desc')
        ->get(['slug', 'updated_at', 'free_chapter_content', 'free_chapter_path'])
        ->flatMap(function ($ebook) {
            $lastmod = optional($ebook->updated_at)->toDateString() ?? now()->toDateString();
            $urls = [[
                'loc' => route('ebooks.show', ['slug' => $ebook->slug]),
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => $lastmod,
            ]];

            if ($ebook->free_chapter_content || $ebook->free_chapter_path) {
                $urls[] = [
                    'loc' => route('ebooks.free-chapter', ['slug' => $ebook->slug]),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                    'lastmod' => $lastmod,
                ];
            }

            return $urls;
        });

    $urls = collect($pages)->concat($serviceItems)->concat($programmes)->concat($ebookPages);
    if (config('blog.public_enabled')) {
        $blogPages = BlogPost::query()
            ->published()
            ->orderBy('updated_at', 'desc')
            ->get(['slug', 'updated_at', 'canonical_url'])
            ->map(fn ($post) => [
                'loc' => $post->canonical_url ?: route('blog.show', $post->slug),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => optional($post->updated_at)->toDateString() ?? now()->toDateString(),
            ]);

        $urls = $urls->concat([[
            'loc' => route('blog.index'),
            'changefreq' => 'daily',
            'priority' => '0.7',
            'lastmod' => now()->toDateString(),
        ]])->concat($blogPages);
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

    foreach ($urls as $url) {
        $xml .= '  <url>' . PHP_EOL;
        $xml .= '    <loc>' . e($url['loc']) . '</loc>' . PHP_EOL;
        $xml .= '    <lastmod>' . e($url['lastmod']) . '</lastmod>' . PHP_EOL;
        $xml .= '    <changefreq>' . e($url['changefreq']) . '</changefreq>' . PHP_EOL;
        $xml .= '    <priority>' . e($url['priority']) . '</priority>' . PHP_EOL;
        $xml .= '  </url>' . PHP_EOL;
    }

    $xml .= '</urlset>';

    return response($xml, 200)
        ->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

Route::get('/llms.txt', function () {
    $programmes = AcademicProgramme::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get(['name', 'slug', 'summary']);

    $lines = [
        '# MephEd',
        '',
        '> Nigerian education, technology, and publishing company building learning services and practical digital products.',
        '',
        '## Canonical',
        '- Home: ' . route('welcome'),
        '- Services: ' . route('services'),
        '- Products: ' . route('products.index'),
        '- About MephEd: ' . route('about'),
        '- Interventions: ' . route('programmes.enquiry'),
        '- Contact: ' . route('contact'),
        '- Sitemap: ' . route('sitemap'),
        '',
        '## Academic Interventions',
    ];

    foreach ($programmes as $programme) {
        $lines[] = '- ' . $programme->name . ': ' . route('programmes.show', ['academicProgramme' => $programme->slug]);
        if (!empty($programme->summary)) {
            $lines[] = '  Summary: ' . $programme->summary;
        }
    }

    $lines[] = '';
    $lines[] = '## Policy Notes';
    $lines[] = '- Confidence & Tutor Match Promise applies across programme pages.';
    $lines[] = '- Pricing depends on lesson frequency, duration, mode, and location for home lessons.';

    return response(implode(PHP_EOL, $lines), 200)->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('llms');

Route::get('/llms-full.txt', function () {
    $programmes = AcademicProgramme::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

    $lines = [
        '# MephEd Full Context',
        '',
        'Homepage: ' . route('welcome'),
        'Products: ' . route('products.index'),
        'About MephEd: ' . route('about'),
        'Interventions: ' . route('programmes.enquiry'),
        '',
    ];

    foreach ($programmes as $programme) {
        $lines[] = '## ' . $programme->name;
        $lines[] = 'URL: ' . route('programmes.show', ['academicProgramme' => $programme->slug]);
        $lines[] = 'Summary: ' . ($programme->summary ?: $programme->tagline);
        $lines[] = 'Frequency: ' . implode(', ', $programme->frequency_options ?: []);
        $lines[] = 'Duration: ' . implode(', ', $programme->duration_options ?: []);
        $lines[] = 'Modes: ' . implode(', ', $programme->mode_options ?: []);
        $lines[] = 'Pricing note: ' . ($programme->pricing_note ?: 'Pricing varies by frequency, duration, mode, and location.');
        $lines[] = '';
    }

    return response(implode(PHP_EOL, $lines), 200)->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('llms.full');

// Guest-accessible request forms (multi-step UI)
Route::get('/apply/tutor/{serviceItem:slug}', TutorRequestWizard::class)->name('apply.tutor');
Route::get('/apply/crm/{serviceItem:slug}', CrmRequestWizard::class)->name('apply.crm');
Route::get('/apply/bootcamp/{serviceItem:slug}', EnrollmentForm::class)->name('apply.bootcamp');


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
    Route::get('/client/online-meetings', ClientOnlineMeetings::class)->name('client.online-meetings');
    Route::get('/client/interventions/new', InterventionRequestCreate::class)->name('client.interventions.create');
    Route::get('/client/programme-requests', ProgrammeRequestsManager::class)->name('client.programmeRequests.manager');
    // Livewire manager pages
    Route::get('client/crm-manager', CrmManager::class)->name('client.crm.manager');
    Route::get('client/tutor-requests-manager', TutorRequestsManager::class)->name('client.tutorRequests.manager');
    
});

Route::middleware(['auth', 'can:Tutor', 'verified'])->group(function () {
    // Tutor Dashboard
    Route::get('/tutor/dashboard', DashboardController::class)->name('tutor.dashboard');
    Route::get('/tutor/lessons', TutorLessons::class)->name('tutor.lessons');
    Route::get('/tutor/online-meetings', TutorOnlineMeetings::class)->name('tutor.online-meetings');
    Route::get('/tutor/tutor-profile', TutorProfiles::class)->name('tutor.tutor-profile');
    Route::get('/tutor/payments', Payments::class)->name('tutor.payments');
    Route::get('/tutor/institution-assignments', InstitutionAssignments::class)->name('tutor.institution-assignments');
    Route::get('/tutor/programme-assignments', ProgrammeAssignments::class)->name('tutor.programme-assignments');
   
});

Route::middleware(['auth', 'can:Admin', 'verified'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', AdminDashboardController::class)->name('admin.dashboard');    
    Route::get('/admin/blog', BlogStudio::class)->name('admin.blog.index');
    Route::get('/admin/blog/create', BlogEditor::class)->name('admin.blog.create');
    Route::get('/admin/blog/{postId}/edit', BlogEditor::class)->whereNumber('postId')->name('admin.blog.edit');
    Route::get('/admin/blog/{postId}/preview', BlogPreview::class)->whereNumber('postId')->middleware('private.nostore')->name('admin.blog.preview');
    Route::get('/admin/blog/{postId}/review', BlogPreview::class)
        ->whereNumber('postId')
        ->middleware(['signed', 'private.nostore'])
        ->name('admin.blog.review');
    Route::get('/admin/blog/settings', BlogSettingsManager::class)->name('admin.blog.settings');
    //Tutor Request Routes
    Route::get('admin/tutor-requests', RequestManager::class)->name('tutorRequests.index');
    Route::get('admin/testimonials', AdminIndexTestimonials::class)->name('admin.testimonials');

    //Bookings (consolidated booking manager)
    Route::get('admin/lessons', BookingManager::class)->name('admin.lessons');
    Route::get('admin/online-meetings', OnlineMeetingManager::class)->name('admin.online-meetings');
    Route::get('admin/bootcamps', BootcampManager::class)->name('admin.bootcamps');
    Route::get('admin/service-catalog', ServiceCatalogManager::class)->name('admin.serviceCatalog');
    Route::get('admin/programme-settings', ProgrammeSettingsManager::class)->name('admin.programmeSettings');
    Route::get('admin/programme-enquiries', ProgrammeEnquiriesManager::class)->name('admin.programmeEnquiries');
    Route::get('admin/church-leads', ChurchLeadsManager::class)->name('admin.churchLeads');
    Route::get('admin/products', ProductsManager::class)->name('admin.products');
    Route::get('admin/ebooks', EbooksManager::class)->name('admin.ebooks.index');
    Route::get('admin/ebooks/policies', EbookPoliciesManager::class)->name('admin.ebooks.policies');
    Route::get('admin/ebook-orders', EbookOrdersManager::class)->name('admin.ebooks.orders');
    Route::get('admin/ebooks/dashboard', EbookOperationsDashboard::class)->name('admin.ebooks.dashboard');

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

Route::middleware(['auth'])->group(function () {
    Route::get('/meetings/{meeting}/join', [OnlineMeetingController::class, 'join'])->name('meetings.join');
    Route::post('/meetings/{meeting}/end', [OnlineMeetingController::class, 'endSession'])->name('meetings.end');
});

require __DIR__.'/auth.php';
