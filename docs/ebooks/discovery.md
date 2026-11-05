# MephEd eBooks repository discovery

Discovery completed before eBook feature work, 28 October 2026.

## Stack and conventions

- Laravel 13.39.0, PHP 8.4.16, Livewire 4, Blade, and Tailwind CSS. Composer scripts do not define a test command; PHPUnit 12 is configured in `phpunit.xml` and tests live in `tests/Feature` and `tests/Unit`.
- Admin UI uses Livewire components under `app/Livewire/Admin`, Blade views under `resources/views/livewire/admin`, and the `Admin` Gate. Existing manager components call `Gate::authorize('Admin')` in actions and rendering. Admin routes require `auth`, `can:Admin`, and `verified`.
- Eloquent models use explicit `$fillable` and `$casts`; migrations use Laravel Schema APIs. Test config has SQLite available, but PHPUnit does not currently force SQLite; `phpunit.xml` has the SQLite overrides commented out.
- Existing payment integration is `App\Services\PaystackService`; eBook checkout reuses its shared Paystack credentials and the existing `/paystack/callback` and `/paystack/webhook` endpoints. The webhook routes eBook events by their `payment_for=ebook` metadata while preserving current booking, CRM, programme, bootcamp, and Solar Sapient handling.

## Operations and security

- Queue config supports `sync`, database, Redis, Beanstalkd, and SQS. `.env.example` currently defaults to `QUEUE_CONNECTION=sync`; production queue driver and whether a worker is supervised or cron-driven are not documented. This must be confirmed on the live host before fulfilment is enabled. Do not claim background fulfilment works in production until then.
- Database queue migrations exist. Scheduler is in `app/Console/Kernel.php`; current scheduled commands run every five minutes or six hours. Production scheduler installation is not documented.
- Mail uses Laravel's SMTP configuration, with Mailpit in `.env.example`. No shared mail notification abstraction was found; `InterventionStatusNotifier` is the closest existing notification pattern. Alert recipients and production delivery are not documented.
- `PaystackController` validates its existing webhook with HMAC SHA-512 and `hash_equals`; `VerifyCsrfToken` excludes only `paystack/webhook`. New webhook behavior must preserve that narrow exception and avoid changing legacy flows.
- Public files live under `public`; private local storage is configured by Laravel's local disk. The existing `.htaccess` rewrites requests to `public`. Private eBook assets must use a non-public disk/path and authenticated controller streaming.
- No PDF stamping package is installed or declared. A local Ghostscript 10.07.1 binary is available at `C:\Program Files\gs\gs10.07.1\bin\gswin64c.exe`; `EbookPdfStamper` invokes Ghostscript as a bounded subprocess and a real two-page test confirms one email stamp on each page. The production host's Ghostscript installation, memory limits, and real book size remain unverified.
- Logging uses Laravel's configured stack. No external error-reporting integration was identified.

## Brand and content

- Existing brand assets include `public/images/MephEd.png` and `public/images/MephEd.svg`. Navy `#0B2A47` and teal `#17C3D6` from the supplied plan are not defined as central Tailwind tokens; existing pages often use Tailwind slate/cyan classes. Confirm and centralize tokens when building public templates.
- No `docs/ebooks/content` files or eBook cover, PDF, sample, cheat sheet, author, final title, price, landing copy, FAQ, policies, or alert recipients are present. The first eBook must remain a draft until owner-provided inputs are available. Do not invent marketing or legal copy.
- Public eBook pages and Admin catalog/order management are intentionally separate. Client/tutor dashboards and permissions do not manage orders; public book pages are available without authentication.

## Deviations and open operational inputs

- Repository already has a general `/products` page; the eBook plan calls for a separate `/ebooks` hub. Keep the domains separate unless the owner directs otherwise.
- Hosting type, actual deployed queue worker and scheduler, mail transport, PDF stamping tools/memory constraints, production error reporting, and monitoring alert recipients need confirmation before launch.

## Updated implementation findings — 28 October 2026

- eBook source, fulfilled, and cheat-sheet PDFs use the private `local` disk rooted at `storage/app`; only cover images use the public disk. Downloads stream through controllers after state/path checks. The local Ghostscript integration is covered by an actual PDF generation and text extraction test.
- The eBook queue defaults independently to `database`, even though the general `.env.example` queue defaults to `sync`. Production must run the database queue worker and Laravel scheduler. The dashboard reports the database queue backlog and last reconciliation run. A scheduler self-check cannot alert if the scheduler itself is fully stopped, so an external monitor/operator check remains necessary.
- The application has no external error-reporting or uptime integration. `/health` is a secret-free liveness endpoint; configure an external uptime monitor for it and the published landing page.
- The eBook dashboard and alert service use email. `EBOOK_ALERT_EMAILS` is intentionally empty in the example configuration; without owner recipients the application logs a warning instead of silently discarding the alert.
- Buyer-facing legal policy text, first/second book copy and files, author names, prices, email recipients, production Paystack keys, and production host capabilities are owner/host inputs and are not available in the repository.
- The supplied plan is the feature brief; this discovery records implementation facts and does not treat the document's agent-directed wording as authorization to invent missing content or claim production readiness.
