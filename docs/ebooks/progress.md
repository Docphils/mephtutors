# MephEd eBooks implementation progress

## Phase 0 — Discovery

Completed 28 October 2026. Findings and operational gaps are documented in [discovery.md](discovery.md).

## Phase 1 — Data and admin

Implemented initial eBook, order, download, and event schemas; lifecycle enums and a transactional, row-locked order transition service; Eloquent models and factories; and Admin-gated Livewire catalog and order screens at `/admin/ebooks` and `/admin/ebook-orders`. Source PDFs are stored on the private local disk. Order rows retain an eBook via a restrictive foreign key. The order screen supports status filtering, refund status recording, and admin-triggered buyer link resend. Tests cover transitions and idempotency, snapshots, token hashing, event privacy fields, enum casts, deletion integrity, and admin authorization.

Verification at Phase 1: full PHPUnit suite passed on an in-memory SQLite database; all new PHP files passed `php -l`; admin routes use the verified Admin middleware group.

The first title is not seeded because owner supplied author, final copy, price, and assets are missing. It must remain unpublished until those inputs are supplied. Order management records a manual refunded status only; the actual Paystack refund remains a separate operator action.

## Phase 2 — Landing page and free chapter

Implemented public, guest-accessible published-book and free-chapter pages, canonical/meta tags, Book/Offer JSON-LD, and dynamic sitemap entries. Draft books return 404 and are excluded from the sitemap. Publishing requires a private source PDF, owner-supplied sample chapter, author, price, SEO title/description, and a primary keyword included in the slug and metadata. Landing/sample views are recorded with referrer path and UTM fields; referrer query strings and IP addresses are not stored. Public pages do not require or expose client/tutor accounts.

Verification at Phase 2: full PHPUnit suite passed on in-memory SQLite (40 tests, 140 assertions); focused eBook tests covered public versus draft visibility, sitemap inclusion, event attribution privacy, order integrity, and Admin authorization. New PHP files passed syntax checks and Pint; `git diff --check` passed.

Remaining requirements before the book is launch-ready: owner-supplied content/assets and the fulfilment, legal, monitoring, and launch phases. Payment can now be tested in Paystack test mode, but production sales must wait until fulfilment is implemented and exercised.

## Phase 3 — Payment

Implemented guest checkout initialization using the database price and currency, Paystack Popup access codes, callback verification, the app's existing raw-body HMAC-SHA512 signed `/paystack/webhook`, and idempotent payment state/event recording. eBooks reuse `services.paystack.public` and `services.paystack.secret`, and Paystack returns through the existing `/paystack/callback`; the shared webhook dispatches by `payment_for=ebook` metadata. Callback and webhook payloads must match the stored Paystack reference, order metadata, amount, and currency. Invalid signatures are rejected before parsing; mismatches are logged and never mark the order paid. Checkout is rate-limited per normalized email and eBook; eBook verification requests are throttled. Paystack credentials are configured once through the existing `config/services.php` setup and `.env.example`.

Verification at Phase 3: full PHPUnit suite passed on in-memory SQLite (45 tests, 173 assertions). Payment feature tests covered guest checkout UI, database-sourced amount, callback/webhook replay, mismatched amount/currency, invalid signatures, and failed payment transitions. Pint, PHP syntax checks, and `git diff --check` passed.

## Phase 4 — Fulfilment

Implemented Ghostscript stamping with a page-end filter that stamps once per rendered page, private per-order PDF storage, paid-order-only unique queue jobs, 72-hour/5-download defaults, streamed downloads with row-locked count reservation, signed short-lived thank-you/download access, and email-bound, rate-limited link resend. Download tokens are derived with HMAC from the order reference and stored generation, while only SHA-256 hashes are persisted; queue retries therefore reuse the same link without storing its raw token. Failures create event records, logs, and deduplicated owner alerts. Cheat-sheet PDFs are stored privately and served through a published-book-only instrumented route. Private responses are `no-store`, `noindex`, `nosniff`, and `no-referrer`.

Verification: a real two-page PDF integration test creates a fixture, stamps it with the local Ghostscript binary, extracts its text, and confirms the buyer email appears exactly once on each page. Feature tests verify private storage, hashed token persistence, download quota, cache/index headers, cheat-sheet visibility, email-bound resends, and signed thank-you access.

## Phase 5 — Monitoring and alerts

Implemented an Admin-only operations dashboard covering sales windows, per-book sales, payment states, funnel and conversion rates, overdue pending orders, webhook failures, fulfillment health, queue backlog when the database driver is used, link exhaustion, traffic-source groups, and last webhook/scheduler runs. `ebooks:reconcile-payments` runs every ten minutes, verifies overdue pending orders through the same locked/idempotent payment service, queues overdue fulfillment, records task-run counts/errors, and alerts on recovery/mismatch/failure conditions. `/health` returns a secret-free liveness response. System-level webhook signature rejection events are stored without a fabricated book/order association.

Verification: integration tests simulate a withheld webhook, have Paystack verification recover the order, assert exactly one paid event and fulfillment dispatch, and verify admin-only dashboard access and health response.

Operational dependency: production queue worker, scheduler, mail transport, alert recipient list, and external uptime monitor are not confirmed. If the scheduler is completely stopped, its in-app stale-run checker cannot execute; the external monitor remains required.

## Phase 6 — Legal and polish

Implemented database-backed, administrator-editable `/legal/refund`, `/legal/terms`, and `/legal/privacy` pages, with draft copy prepared for MephEd Educational Services Ltd and the owner-selected digital-download refund stance. Administrators can edit, publish, and unpublish each page; eBook catalog publication requires all three policies to be live. The pages are drafts that require qualified Nigerian legal review and owner approval. Public eBook landing pages render owner-entered description, audience, contents, FAQ and matching FAQ, Book/Offer, Organization, and Breadcrumb structured data. Publication also requires the book copy and assets. Sales remain independently disabled by default via `EBOOK_SALES_ENABLED=false`, and checkout checks that all required policy pages are published.

## Phase 7 — Launch checks

Automated local payment, fulfillment, reconciliation, privacy, rate-limit-facing route, schema, and real PDF checks pass. A live Paystack purchase, mail deliverability, external uptime monitor, mobile Core Web Vitals, real PDF size/host resource test, production key/webhook setup, and Selar listing cannot be verified from this local workspace. Track these in [launch-checklist.md](launch-checklist.md); production sales remain gated until owner and host inputs are supplied and verified.

## Phase 8 — Second eBook

The public hub at `/ebooks` and Admin catalog support arbitrary published titles, so the second title uses existing data-entry flow and reusable views. It is not entered or published because the owner-supplied title, author, price, copy, FAQ, cover, source PDF, free chapter, and cheat-sheet files are absent. No title-specific code was added.

## Current verification

Full PHPUnit suite: **56 tests, 248 assertions passed** on the configured test database. Pint passed for the changed PHP files. The production-specific checks listed in the launch checklist remain open.
