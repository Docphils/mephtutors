# eBooks launch checklist

Local implementation is complete through Phase 8's reusable catalog/hub. Production launch is still gated on the owner and hosting items below. Do not publish a book or accept production payment until these are closed.

## Code and local verification

- [x] Paystack initialization uses the server-side NGN price; callback and signed webhook verify reference, order metadata, amount, currency, and transaction status.
- [x] Callback, webhook replay, and reconciliation share an idempotent locked transition to paid.
- [x] Fulfillment runs as a unique queue job, stamps each PDF page, stores the result privately, and sends a hashed-token link.
- [x] Download expiry/count limits, generic email-bound resend, private response headers, and noindex thank-you/download pages are covered by tests.
- [x] Reconciliation recovers a simulated missed webhook and enqueues fulfillment.
- [x] Admin-only operations dashboard and secret-free `/health` are tested.
- [x] eBook publishing is enabled when all three database-backed legal pages are published; administrators can edit and unpublish them.
- [x] Payment checkout remains independently disabled by default and enforces the published-policy gate.
- [x] Full PHPUnit suite passes: 56 tests, 248 assertions.
- [x] Real two-page Ghostscript stamping test passes locally.

## Owner inputs required

- [ ] Final title and slug for each title; author names; naira price; owner-approved page copy and FAQ.
- [ ] Final cover, source PDFs, free chapter text, and cheat-sheet PDFs.
- [ ] Qualified Nigerian legal review and owner approval of the live draft refund, terms, and privacy policy text.
- [ ] Alert recipient email addresses and confirmation that production mail delivery works.
- [ ] Confirm the selected 72-hour expiry and five-download limit.
- [ ] Confirm Selar terms permit the same-title sale and create the Selar listing manually if desired.

## Production host and transaction checks

- [ ] Confirm PHP/Laravel requirements, Ghostscript binary/version, memory/time limits, and stamping performance with the real source PDF.
- [ ] Configure `EBOOK_QUEUE_CONNECTION` to a durable production queue and supervise a worker listening to the `ebooks` queue. Confirm retries and failed-job visibility.
- [ ] Install Laravel's scheduler cron and verify `ebooks:reconcile-payments` completes every ten minutes.
- [ ] Confirm the existing production Paystack public/secret keys and `/paystack/webhook` endpoint; eBook payments reuse the existing integration and do not require a second webhook or callback configuration.
- [ ] Complete a full Paystack test-mode purchase, webhook replay, download, expiry/exhaustion, resend, and reconciliation recovery on the production-like host.
- [ ] Confirm mail receipt and admin notices arrive and a simulated fulfillment failure emits one deduplicated alert.
- [ ] Configure an external uptime monitor for `/health` and the published landing page.
- [ ] Confirm the thank-you route returns `noindex`, the sitemap excludes thank-you/download routes and includes only published content, and structured data validates.
- [ ] Check mobile rendering and Core Web Vitals on the deployed landing and free chapter pages.
- [ ] After all checks pass, perform the owner's approved low-value live transaction and verify delivery/refund operations.

## Launch decision

**eBook catalog publishing is enabled; production sales are not ready.** Live legal pages are editable in the admin eBook Legal Pages screen and gate publication. Their starting text is a draft and still requires qualified Nigerian legal review and owner approval. Sales remain disabled by default, and book assets/content, alert recipients, production queue/scheduler, production Paystack configuration, and host-level stamping/mail checks remain outstanding.
