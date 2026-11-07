# MephEd Blog and Automated Content Studio — Implementation Plan

## Purpose

Build a public, search-friendly MephEd blog with a secure editorial workspace that can produce one useful article draft each day. The system should use current, verifiable education-sector developments where relevant, reflect MephEd’s voice, and naturally point readers to appropriate services or published eBooks. Each draft should include a purpose-made image, be saved for review, and trigger an email with a secure route to the editor. Facebook is the only social channel in scope.

Implementation has begun at the user's direction. The supplied sample `https://www.zaramed.ng/blog/top-5-best-cbt-software-providers-in-nigeria-2026` was reviewed. Its style is practical and reader-oriented, with clear evaluation criteria, concrete feature explanations, balanced limitations, transparent pricing caveats, and a concise decision-focused conclusion. This guides structure only; each claim still needs independent verification and editorial review.

### Implementation status

The foundation adds dedicated blog storage, Livewire public and authenticated editorial pages, private previews, safe rich-content rendering, published-only public pages, category/tag archives, a public RSS feed, sitemap entries, and published-slug redirects. The article form uses the project's existing Trix rich-text editor and supports validated JPG, PNG, and WebP featured-image uploads, previews, replacement, and removal; saved article content is sanitized server-side. The administrator approved public exposure after foundation completion. The automation uses an editorial topic bank: AI suggestions remain pending until an administrator approves them, and generation cannot use an unapproved topic. It uses Tavily web search only—no RSS/Atom feeds as research sources—with current-affairs news search and general evidence/idea research for other topics. Preferred domains guide searches but do not restrict sources. The workflow adds an idempotent daily run record and isolated database-backed `blog` queue, Groq and Gemini text adapters, configurable article length, per-run author attribution, draft provenance and validation, links only to published MephEd resources, branded generated artwork, review email, signed authenticated review links, and topic/generation status in Blog Studio. Editors can request field-specific AI suggestions, inspect a preview, and explicitly apply it. The scheduler checks the configured Africa/Lagos time each minute; manual generation is available when the server-side generation flag is enabled. Published posts now create an idempotent Facebook Page share record, use the Meta Pages API when enabled/configured, display a manual-share fallback in Blog Studio, and stop on ambiguous delivery results rather than risk duplicate posts.

Recorded decisions: public byline combines the selected article author's name with the configured company name (`BLOG_AUTHOR_BYLINE`); topics are organized as Current affairs, eBook-inspired, Opinion/analysis, Practical/how-to, and Education research; Nigeria is primary with relevant global coverage; daily run time 4:00 AM Africa/Lagos; review recipients and schedule are configurable in the application; approved topics are mandatory, with admin-triggered and low-watermark topic suggestion batches; article length has a configurable default and per-generation override; AI editor suggestions require preview and explicit application; manual drafts use the requesting admin and scheduled drafts use a configured verified admin; Groq and Gemini are approved text-provider options with a zero-cost budget; the user reports completing Gemini compliance review and authorizes its unpaid tier; Tavily's account allowance is 1,500 credits/month; Facebook should support API publishing and a manual fallback. The MephEd Educational Services Facebook Page is `https://www.facebook.com/mephed`; the user reports Page admin and app/permissions readiness. The user confirmed cPanel cron and periodic queue-worker support.

Image decision: no verified, production-suitable, zero-cost hosted image-generation tier was found. [Google announced Gemini 2.5 Flash Image at $0.039/image](https://developers.googleblog.com/en/introducing-gemini-2-5-flash-image/); [Hugging Face's pricing documentation](https://huggingface.co/docs/inference-providers/en/pricing) lists no monthly inference credits for free accounts; and local open-weight generation does not fit the user's cPanel/no-GPU host. The user selected article-specific branded programmatic graphics instead of an AI image API. The implementation uses GD to produce PNG artwork for article/social previews, with a sanitized SVG fallback when GD is unavailable; the fallback uses MephEd's default raster image for Open Graph compatibility. No image-provider credentials or per-image charges are required.

Provider decision: Groq is the default text provider and Gemini is selectable through server configuration (`BLOG_AI_PROVIDER`); provider credentials stay in deployment secrets. The user confirms they have completed compliance review and authorize Gemini's unpaid tier. Google's [Gemini API terms](https://ai.google.dev/gemini-api/docs/terms) state that unpaid-tier prompts/responses may be used to improve services and may be human-reviewed, and describe restrictions concerning applications likely accessed by under-18s. Keep use within the user's documented compliance determination; no Gemini image generation is used.

Research discovery uses one Tavily search per generation attempt, with configurable basic (1 credit) or advanced (2 credits) depth, result count, and a monthly credit cap. At one basic search per daily draft, 30–31 drafts use about 30–31 credits/month, roughly 2% of the user's 1,500-credit allowance; retries and manual drafts add searches. Topic suggestion batches and AI field assistance use the configured text model, not Tavily. Current-affairs topics use time-filtered news search; other approved topics use general search for evidence, examples, and supporting ideas. Preferred domains are optional search hints, not an allowlist. The app uses Tavily summaries and citations but does not fetch arbitrary search-result pages. Configure `TAVILY_API_KEY` as a server secret; Tavily's public pricing page may show a different allowance, so use the user's account dashboard for the actual monthly quota. See [Tavily Search](https://docs.tavily.com/documentation/api-reference/endpoint/search) and [pricing](https://www.tavily.com/pricing). Search results are untrusted research data and require editorial review. The separate public blog RSS feed contains only published articles; RSS is not used for research sourcing. X is not scraped; it requires a separately configured official API integration. The Meta posting code follows the [Pages API post guide](https://developers.facebook.com/documentation/pages-api/posts) and [access-token guidance](https://developers.facebook.com/documentation/facebook-login/guides/access-tokens), targeting the versioned `/page_id/feed` endpoint. Verify the Page access token has `pages_manage_posts` and any required Page tasks before enabling.

Generation and Facebook flags remain false by default. Deployment must configure provider, Tavily, and Page credentials as server secrets, recipient addresses, database migrations, the app's mail transport, cPanel's every-minute Laravel scheduler, and a worker for `queue:work blog --queue=blog --stop-when-empty --tries=3 --timeout=240`; then perform staging generation and Page-delivery checks before enabling either workflow. The dedicated database queue uses the existing `jobs` table and does not change the default queue connection. On ambiguous Meta timeouts/server errors, the record requires a human to check the Page before any retry; the manual share URL remains available.

## Production change constraints

This application is already in production. All implementation must be incremental, additive, and backward-compatible with the running site and its existing data. Do not rename, remove, repurpose, or make existing columns stricter; do not change existing routes, model behavior, queue semantics, scheduled tasks, or public page output in a way that breaks current consumers. New blog behavior should be isolated behind new tables, models, routes, jobs, configuration keys, and admin screens wherever practical.

Database migrations must be safe for populated production databases: create new blog tables/indexes and add nullable columns only when a genuine integration need exists. Avoid destructive migrations, data rewrites, long blocking locks, and assumptions that production data matches local fixtures. For any unavoidable existing-table adjustment, use an expand-and-contract sequence: deploy additive schema first, deploy code that tolerates both old and new forms, backfill in bounded resumable batches if needed, verify, and only consider a later cleanup after compatibility is established and separately approved. Rolling back application code must remain possible while keeping the additive schema in place.

Deploy in small, reversible stages with backups and a tested restore path. Run migrations and queue/scheduler changes under production deployment procedures; keep daily generation and Facebook publishing disabled by default until configuration and staging verification are complete. Preserve current queue and mail settings, and configure dedicated workers/provider settings without silently changing unrelated workloads. Add feature flags for public blog exposure, AI generation, and Facebook publishing so each can be enabled independently. Monitor errors, queue health, response times, and existing critical flows after each release; define rollback triggers before enabling automation.

## Product decisions and scope

### Daily automation and publication gate

“Fully automatic” is implemented as an automatic daily pipeline: topic selection, source research, drafting, image creation, saving, and review-email delivery happen without a daily prompt from the administrator. The original request also says the administrator should review and publish. Therefore, **the default design keeps generated articles unpublished until an authorized person approves publication**. Fully automatic public publishing would remove this review gate; it is not included unless explicitly chosen before implementation.

Facebook distribution follows the same gate: after an article is published, the system can automatically publish a Facebook Page post if Meta app permissions and credentials are configured. If publishing is unavailable or fails, the article remains published on MephEd and its social post is queued for retry, with an admin-visible status and a prepared manual-share option.

### Included in the initial release

- Public blog index, category/tag navigation, article page, search, pagination, related articles, and RSS feed.
- Admin content studio with draft review/editing, previews, regeneration controls, source review, publication controls, Facebook status, and a content calendar/history.
- Daily scheduler and queued generation workflow with duplicate prevention, retries, alerting, and a manual “generate now” action.
- AI research and writing using a configurable provider, verified company facts, a curated style guide, and relevant active service/published eBook data.
- Generated article hero image and social image, stored and reviewed alongside the draft.
- Admin notification email containing a signed, short-lived review link that still requires administrator authentication.
- SEO metadata, structured data, canonical URLs, social cards, sitemap integration, and editorial quality checks.
- Facebook Page publishing after the blog publication event, with idempotency and status tracking.

### Out of scope for initial release

- Instagram, other social networks, paid promotion, audience targeting, comments, public user accounts, and automatic newsletter campaigns.
- Publishing AI-generated articles without human approval.
- Unbounded crawling or scraping of news sites and automatic republishing of source material.

## What the current application provides

- Laravel 13, Livewire admin screens, role authorization, queued email patterns, public service and eBook catalogues, and a Laravel scheduler.
- `EbookCopywriter` already calls Groq through Laravel’s HTTP client and validates JSON output. It is a useful integration precedent, but a blog workflow needs separate prompts, response schemas, safety checks, and failure handling.
- `Ebook` and `ServiceItem` provide source facts and URLs for contextual links. Only published eBooks and active services should be considered for public article links.
- `/sitemap.xml` is currently assembled in `routes/web.php`; published articles must be added without exposing drafts.
- The environment example defaults to `QUEUE_CONNECTION=sync` and local mail settings. Production automation requires a real queue worker, scheduler cron, working email transport, and monitoring; a synchronous queue is not suitable for reliable daily AI and image work.
- Admin screens use the existing `auth`, verified-email, and `can:Admin` route protection pattern.

## Editorial voice and content policy

Use a reviewed, versioned style profile rather than asking the model to infer voice from the whole live website on every run. The initial hypothesis to verify against zaramed.ng/blog is: clear and practical educational writing; a warm, informed tone; accessible explanations; useful examples; and promotion that is relevant and restrained.

The admin should be able to maintain:

- Voice and audience guidance, spelling/terminology preferences, article structure, and words or claims to avoid.
- A small set of approved writing samples. Store excerpts or administrator-provided text, not scraped full articles by default.
- Topic pillars, excluded or sensitive topics, geographic emphasis, target length, and service/eBook promotion frequency.
- Approved factual sources for MephEd services, programmes, products, published eBooks, and organizational claims.

The generator must distinguish fact from advice and opinion; avoid fabricated statistics, quotes, credentials, guarantees, and product capabilities; use neutral, careful language for sensitive or fast-moving topics; and not turn every article into an advertisement. Product links should appear only where they genuinely help the reader. Generated content remains an editable suggestion and is not represented as human-authored reporting.

## User experience

### Public blog

- `/blog`: responsive index with featured article, latest articles, categories, search, and pagination.
- `/blog/{slug}`: accessible article layout with author/editor attribution policy, published/updated dates, reading time, table of contents where useful, inline service/eBook links, related posts, and share controls.
- Share controls use the canonical article URL and Open Graph metadata for Facebook. The public page remains readable without JavaScript.
- Add category and tag landing pages only when they contain useful editorial content; avoid thin archive pages.

### Admin content studio

- Dashboard: today’s generation status, current draft, upcoming schedule, failed jobs, and Facebook delivery state.
- Article list/calendar: filter by status, topic, date, channel, and generation run.
- Editor: title, slug, excerpt, rich article body, image, topic/category/tags, SEO fields, selected links, research sources, and Facebook caption/preview.
- Review actions: edit and save, regenerate a selected section, regenerate image, request a full revision with instructions, preview desktop/mobile/social cards, publish now, schedule, or reject/archive.
- Settings: daily local time, cadence enable/disable, topic pillars, style profile, excluded subjects, promotional balance, recipient email, and Facebook Page connection status.
- Preview and source links must be private and must never make a draft indexable or publicly accessible.

## Data model proposal

Use dedicated tables and explicit state transitions, rather than storing the article pipeline in JSON blobs.

1. `blog_posts`: title, slug, excerpt, sanitized body, status (`draft`, `scheduled`, `published`, `rejected`, `archived`), author/editor IDs, featured image path and alt text, SEO title/description/canonical override, topic/category, timestamps (`generated_at`, `published_at`), content hash, and publication provenance.
2. `blog_categories` and `blog_tags`, with pivot tables and unique normalized slugs.
3. `blog_generation_runs`: scheduled date, status, topic, provider/model, prompt/style version, stage timestamps, error classification, retry count, and resulting post ID. Unique scheduled slot prevents duplicate daily generation.
4. `blog_sources`: generation run/post, URL, publisher/title, retrieval timestamp, published date where available, short factual notes, and content hash. Store only the minimum text needed for review and provenance; avoid copying entire copyrighted articles.
5. `blog_post_links`: explicit links to `ServiceItem` and `Ebook` records, including rationale/anchor text. Revalidate eligibility at publication time.
6. `blog_social_publications`: post, platform/page identifier, status, remote post ID, attempt count, timestamps, safe error code, and retry timing. Do not persist access tokens in these rows.
7. `blog_settings` (or a typed settings mechanism): scheduling, content policy, style profile version, recipient, and feature toggles. Keep secrets in environment/secret storage, not these settings.
8. Optional `blog_post_revisions` or activity log: editor, changed fields, action, timestamp, and prior content hash for recoverability and audit.

Add foreign keys, appropriate indexes, uniqueness constraints, and safe cascade behavior. Do not delete historical generation/source/audit records merely because a post is archived. Keep all proposed blog entities in new tables; do not retrofit unrelated production tables for convenience. Ensure migration lock duration and index creation strategy are appropriate for the production database engine and table sizes.

## Automated daily pipeline

Run in `Africa/Lagos` at a configurable quiet time. Use a scheduled Laravel command with `withoutOverlapping`, a unique daily generation-run constraint, and a lock/idempotency key. Enqueue work; do not hold a web request open for model calls.

1. **Choose a topic.** Rotate among approved pillars and inspect recent posts/drafts to reduce repetition. Prefer timely subjects only when a current source supports them.
2. **Research.** Use topic-aware Tavily web search across the open web. Use news search and recency filters for current-affairs topics; use general evidence and idea search for evergreen topics. Optional preferred domains influence search queries but never restrict results. Record returned source titles, URLs, dates, and summaries; validate citation URLs and never fetch arbitrary result pages. If sources are unavailable or cannot corroborate the premise, mark the run as needing attention rather than silently changing the approved topic.
3. **Build factual context.** Fetch only active service items and published eBooks. Supply approved summaries/links, current style profile, topic policy, and source notes. Treat source text as untrusted input, never as instructions.
4. **Draft structured content.** Request validated fields: headline, slug suggestion, excerpt, article sections, SEO title/description, tags, image brief, Facebook caption, and service/eBook link recommendations. Require source references for time-sensitive factual claims and avoid direct copying.
5. **Quality and safety checks.** Validate schema and length, sanitize HTML/Markdown, check links and references, reject unsupported numeric/medical/legal claims, detect near-duplicate titles/content, verify linked catalog records remain eligible, and flag low-confidence or conflicting research. A failed check leaves a visible failed/needs-attention run and does not publish.
6. **Create image.** Send a factual art direction derived from the article, not copyrighted source images or third-party logos. Save generated originals and web/social renditions to controlled storage; validate actual MIME type, dimensions, and size; strip metadata; set descriptive alt text; and retain a safe fallback image when image generation fails.
7. **Persist draft.** Save the article and run in one transaction where possible, retaining source provenance and prompt/model version. Ensure retries resume a stage or safely replace the run’s existing draft instead of creating duplicates.
8. **Notify administrator.** Queue an email with article title, short summary, key research links, image preview, and a signed, expiring link to the authenticated admin editor. Record notification outcome; retry transient mail errors and show permanent failures in the dashboard.
9. **Review and publish.** An authenticated administrator edits and explicitly publishes or schedules the article. Publication rechecks required fields, unique slug, link eligibility, and image availability; writes publication time; then dispatches sitemap/cache and Facebook jobs.
10. **Facebook delivery.** Build a concise caption and canonical link. Publish through Meta Graph API to the configured Page after connection and permission checks. Persist remote ID and status, use idempotency to prevent duplicate shares, retry transient errors with backoff, and surface token expiry/permission errors for administrator action.

## AI and image provider strategy

- Extract a small provider interface for research, text generation, and image generation so one vendor’s API shape does not leak into Livewire components or models.
- Reuse the Laravel HTTP client patterns and structured-output validation used in `EbookCopywriter`; do not reuse its eBook prompts or assume the configured Groq text model can create images.
- Select providers after checking current model availability, image rights/terms, regional account availability, data retention controls, latency, price per daily run, and required credentials. Keep provider/model names configurable and log them without logging keys or full sensitive payloads.
- Set per-stage timeout, bounded retries, rate-limit handling, maximum output/image budget, and an administrator pause switch. Never let provider output execute HTML, code, SQL, URLs, or tool instructions.
- Use a web-search API for research discovery; do not make RSS feeds or a manually maintained domain list a prerequisite for sourcing.

## Security and privacy requirements

- Apply `auth`, verified email, and explicit admin authorization to every editor, preview, generation, settings, and social-connection route/action. Enforce authorization in Livewire actions/policies as well as route definitions; never trust hidden buttons or client-supplied status/IDs.
- Only published posts are reachable publicly. Draft, rejected, archived, and scheduled content must return 404/403 from public routes, have `noindex` protection on authenticated previews, and be excluded from sitemap, RSS, feeds, cache, and social publishing.
- Validate every state transition server-side. Publishing and social actions require explicit CSRF-protected admin actions; use confirmation for publish/delete/connection changes and rate-limit expensive generation/regeneration actions.
- Sanitize rich content with an allowlist sanitizer before storage/rendering. Escape titles/metadata and disallow scripts, event attributes, unsafe embeds, and dangerous URL schemes. Add a strict Content Security Policy where compatible with the existing frontend.
- Research uses Tavily's API and does not fetch arbitrary source pages; validate returned HTTPS citation URLs and never send provider credentials to result hosts. Preferred research domains are optional search hints, not an allowlist.
- Keep AI, image, and Meta credentials in environment/production secret storage. Use the least-privilege Meta Page permissions available, protect OAuth state, encrypt stored tokens if persistence is required, redact secrets from logs, and provide token revocation/rotation guidance.
- Validate and re-encode uploaded/generated images. Store outside executable/public write paths or use a non-executable public disk/CDN; use random paths, size and dimension limits, safe image response headers, and controlled access for unpublished assets.
- Use signed review links only as a convenience layered on top of login and admin authorization; expire them, avoid exposing post body in the URL, and prevent indexing/referrer leakage.
- Add audit events for generation, edits, publication, rejection, deletion/archive, settings changes, and Facebook actions. Restrict logs and source notes to what is required; set retention rules.
- Do not submit personal data, user records, or private business data to content providers. Review provider data retention and applicable privacy obligations before enabling production generation.

## SEO and discoverability

- Stable, unique, lowercase slugs; canonical article URLs; meaningful title and meta description; one clear H1; semantic headings; descriptive internal links; and `Article`/`BlogPosting` JSON-LD with accurate author, dates, publisher, and image.
- Set Open Graph and Twitter-compatible card tags. Use the approved public site URL as canonical base; ensure Facebook previews use absolute image URLs with suitable dimensions and alt text.
- Add published article URLs and accurate `lastmod` values to the existing sitemap. Keep drafts out. Consider a sitemap index only if URL count eventually warrants it.
- Provide RSS/Atom feed with only published articles, stable IDs, canonical links, and safe excerpts.
- Use pagination canonicals thoughtfully, useful archive content, breadcrumbs, and related posts; avoid thin tag pages, keyword stuffing, fabricated schema fields, and duplicate content.
- Provide image optimization and responsive sizes, lazy-load below-the-fold assets, reserve image dimensions to reduce layout shift, and measure Core Web Vitals.
- Support editorial control of canonical/noindex metadata for corrections, republished material, or withdrawn posts. Add redirects when changing an already published slug.
- Track Search Console indexing and structured-data errors after launch; do not promise rankings or use generated traffic as a success measure by itself.

## Reliability and operations

- Configure a persistent production queue, supervised workers, scheduler cron (`schedule:run` every minute), failed-job review, and restart/deploy handling. Keep local development safe with generation disabled or faked unless explicitly enabled.
- Add a run health dashboard: last successful generation, next scheduled run, stage failures, queue age, pending reviews, mail status, Facebook token/permission status, and retry actions.
- Alert the configured administrator after repeated generation failures, stale scheduler runs, notification failure, or repeated Facebook delivery errors. Avoid alert storms with deduplication and cooldowns.
- Store generation configuration/version, but avoid logging full prompts and provider responses by default. Provide retention and cleanup for temporary source/image files.
- Add backup coverage for post content, relationships, audit records, and image storage. Test recovery of a draft and its media.
- Add preview environments and provider fakes so routine application tests do not incur API cost or publish externally.

## Implementation phases and acceptance criteria

### Phase 0 — Confirm editorial and provider decisions

- Review accessible zaramed.ng/blog examples and approve a one-page style profile.
- Confirm that “fully automatic” means automatic daily **drafting with human approval before publication**, as carried forward from the original request.
- Choose daily run time, email recipient(s), company byline, fixed topic types, text/research providers and budget. Research sources are discovered through web search; RSS is not a sourcing dependency.
- Confirm the Facebook Page identity and whether API publishing should be enabled in release one or initially run in prepared-manual-share mode pending Meta access review.

**Complete when:** editorial, provider, source, hosting, and image-fallback decisions are recorded; credentials/permissions requirements are understood; and no provider dependency is assumed to be available without verification.

### Phase 0.5 — Production compatibility and rollout design

- Identify the production database engine/version, deployment procedure, scheduler and queue topology, storage backend, backup/restore process, and maintenance constraints before finalizing migrations.
- Review current application contracts and existing route, model, queue, and mail behavior that must remain stable. Record baseline health and critical user journeys for post-deploy comparison.
- Design additive migrations and independent feature flags. Define deployment order, worker restart behavior, rollback steps, observability, and named rollback triggers.
- Validate migration duration/locking against a production-sized copy or representative staging database. Ensure migration rollback does not destroy new content; prefer retaining additive schema during application rollback.

**Complete when:** a production rollout and rollback runbook is reviewed, schema changes are confirmed additive, and the blog can remain dormant while existing production behavior continues unchanged.

### Phase 1 — Blog foundation

- Add migrations/models, public blog pages, admin-only content studio, policies, safe rendering, categories/tags, preview, and manual article creation/editing.
- Add metadata, structured data, RSS, sitemap integration, image delivery, and slug redirects.

**Complete when:** an administrator can create/edit/preview/publish a manual post; public users can view only published content; drafts are absent from every public feed/index; SEO metadata is rendered correctly; existing public routes and critical application flows pass regression checks.

### Phase 2 — AI-assisted daily draft pipeline

- Add provider adapters, an AI topic bank with explicit approval, topic-aware research, configurable article length, per-run author assignment, generation run records, validations, image generation, daily scheduler/queue, retries, and email review link.
- Add manual topic suggestions, generate-now, per-topic draft regeneration, field-level AI suggestions with preview/apply, run inspection, and failure recovery actions.

**Complete when:** a controlled end-to-end run creates no more than one draft per scheduled day, records provenance, never publishes automatically, and notifies an authenticated administrator. Provider and mail failure tests demonstrate safe recovery without duplicates. Generation remains disabled until deliberately enabled, and existing queue workloads remain healthy.

### Phase 3 — Facebook publishing

- Add Meta Page authorization/credential setup, secure token handling, post preview, post-publication job, delivery state, retries, idempotency, and manual fallback.

**Complete when:** a published article creates at most one Facebook Page post per intended share; errors and revoked permissions remain visible and recoverable; drafts can never be shared; Facebook publishing can be independently disabled without affecting blog publication or existing site behavior.

### Phase 4 — Editorial analytics and optimization

- Add content calendar insights, review latency, article performance inputs where available, internal-link suggestions, stale-post review, and editorial refinements.

**Complete when:** reporting uses reliable available data, informs topic planning, and does not change publication policy or publish content without admin approval.

## Verification plan for implementation

The implementation should include tests for authorization at every endpoint/action; draft non-disclosure; XSS sanitization; malformed model responses; prompt-injection strings in source content; SSRF rejection; invalid/oversized image payloads; duplicate scheduled runs; retry idempotency; publish-state transitions; sitemap/RSS/JSON-LD correctness; signed-link expiry; failed mail; Facebook token expiry and duplicate prevention; and queue/scheduler behavior. Use fakes for AI, image, email, and Meta integrations in automated tests. Add a production-like staging run that generates a draft and sends it only to a test administrator before enabling the daily schedule.

Also verify additive migration behavior against a copy of production-shaped data; application rollback with the new schema retained; deploys with generation and Facebook flags disabled; existing routes and critical workflows before and after each phase; and no unintended changes to current queue or mail configuration. Migrations must be reviewed for production lock/runtime risk, and any backfill must be resumable and bounded.

## Risks and mitigations

| Risk | Mitigation |
|---|---|
| Inaccessible style reference | Obtain representative samples and approve a compact style guide before prompt tuning. |
| Trend article inaccuracies or stale facts | Use dated, reputable sources, require source-grounded claims, and retain editor approval. |
| AI repetition or overly promotional copy | Topic rotation, duplicate detection, promotion limits, service relevance checks, and editorial controls. |
| Copyright or plagiarism concerns | Summarize with attribution, store minimal source notes, avoid copying passages/images, and run similarity checks where feasible. |
| Queue/scheduler misconfiguration | Deployment checklist, health monitoring, stale-run alert, and admin run status. |
| Provider downtime or spending surprises | Bounded attempts/budget, stage-level limits, pause switch, fallback evergreen topic, and alerts. |
| Meta API access or token interruption | Verify Page permissions early; retain share preview/manual fallback and surface delivery state. |
| SEO harm from low-value daily content | Publish only after human editorial approval; quality checklist can hold weak drafts. |
| Production regression during rollout | Additive schema only, isolated feature flags, staged releases, regression monitoring, backups, and a practiced rollback that leaves the new schema intact. |

## Recommended release posture

Start with public blog + admin editor + daily AI draft generation + email notification + manual publication approval. Enable automatic Facebook Page distribution only after Meta credentials/permissions and staging delivery are verified. This preserves a genuinely automated daily content workflow while keeping editorial, factual, security, and reputational control with MephEd.
