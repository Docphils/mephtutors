<div class="min-h-screen bg-slate-100 p-4 sm:p-6 lg:p-8">
    <x-slot name="header">
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Digital publishing</p><h1 class="text-2xl font-black text-slate-900">eBook Manager</h1><p class="mt-1 text-sm text-slate-500">Prepare catalog entries and keep source PDFs in private storage.</p></div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,0.9fr)]">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="mb-5 text-lg font-black text-slate-900">{{ $editingId ? 'Edit eBook' : 'Add an eBook draft' }}</h2>
                <form wire:submit="saveEbook" class="space-y-4">
                    @if ($errors->any())
                        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                            <p class="font-bold">Unable to save this eBook:</p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">Title<input wire:model="title" maxlength="200" required class="mt-1 w-full rounded-xl border border-slate-300">@error('title')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Slug<input wire:model="slug" maxlength="160" placeholder="Generated from title" class="mt-1 w-full rounded-xl border border-slate-300">@error('slug')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Author name<input wire:model="authorName" maxlength="160" required class="mt-1 w-full rounded-xl border border-slate-300">@error('authorName')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Price (naira)<input type="number" min="1" step="1" wire:model="priceNaira" required class="mt-1 w-full rounded-xl border border-slate-300">@error('priceNaira')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700">Subtitle<input wire:model="subtitle" maxlength="300" class="mt-1 w-full rounded-xl border border-slate-300"></label>
                    <section class="space-y-4 rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-sm text-cyan-950">
                        <div>
                            <h3 class="font-bold">AI copy suggestions</h3>
                            <p class="mt-1">Add the free chapter as text or PDF, then ask AI to draft the landing copy, audience, contents, FAQs, and SEO fields. Review every suggestion before publishing.</p>
                        </div>
                        <label class="block font-semibold">Free chapter text <span class="font-normal text-slate-500">(optional if you upload a PDF)</span><textarea wire:model="freeChapterContent" rows="8" maxlength="100000" class="mt-1 w-full rounded-xl border border-slate-300 bg-white text-slate-900"></textarea>@error('freeChapterContent')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="block font-semibold">Free chapter PDF <span class="font-normal text-slate-500">(optional if you add text; max 50 MB)</span><input type="file" wire:model="freeChapterPdf" accept="application/pdf,.pdf" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white p-2 text-sm text-slate-900"><span wire:loading.remove wire:target="freeChapterPdf">@if($freeChapterPdf)<span role="status" class="text-xs text-emerald-700">PDF uploaded and ready for AI and saving.</span>@elseif($existingFreeChapterPath)<span class="text-xs text-emerald-700">A free chapter PDF is saved.</span>@endif</span><span wire:loading wire:target="freeChapterPdf" role="status" class="mt-1 block text-xs font-semibold text-cyan-800">Uploading free chapter PDF…</span>@error('freeChapterPdf')<span role="alert" class="block text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        @if(session('copywriter_success'))<p class="font-semibold">{{ session('copywriter_success') }}</p>@endif
                        @error('copywriter')<p role="alert" class="text-rose-700">{{ $message }}</p>@enderror
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-cyan-900">Generated copy is a draft. Review it before saving or publishing.</p>
                            <button type="button" wire:click="generateCopy" wire:loading.attr="disabled" wire:target="generateCopy,freeChapterPdf" class="shrink-0 rounded-lg bg-cyan-800 px-4 py-2 font-bold text-white disabled:cursor-not-allowed disabled:opacity-60">Generate suggestions</button>
                        </div>
                    </section>
                    <label class="block text-sm font-semibold text-slate-700">Landing page copy<textarea wire:model="description" rows="5" maxlength="20000" class="mt-1 w-full rounded-xl border border-slate-300"></textarea></label>
                    <label class="block text-sm font-semibold text-slate-700">Who this is for<textarea wire:model="audience" rows="3" maxlength="10000" class="mt-1 w-full rounded-xl border border-slate-300"></textarea></label>
                    <label class="block text-sm font-semibold text-slate-700">What is inside<textarea wire:model="contents" rows="4" maxlength="10000" class="mt-1 w-full rounded-xl border border-slate-300"></textarea></label>
                    <div class="space-y-3"><div class="flex items-center justify-between"><h3 class="text-sm font-bold text-slate-800">Frequently asked questions</h3><button type="button" wire:click="addFaqRow" class="text-sm font-bold text-cyan-800">+ Add question</button></div>@foreach($faqRows as $index => $faqRow)<div wire:key="ebook-faq-{{ $index }}" class="rounded-xl border border-slate-200 p-3"><div class="flex justify-end"><button type="button" wire:click="removeFaqRow({{ $index }})" class="text-xs font-bold text-rose-700">Remove</button></div><label class="block text-sm font-semibold text-slate-700">Question<input wire:model="faqRows.{{ $index }}.question" maxlength="300" class="mt-1 w-full rounded-lg border border-slate-300"></label><label class="mt-3 block text-sm font-semibold text-slate-700">Answer<textarea wire:model="faqRows.{{ $index }}.answer" rows="2" maxlength="3000" class="mt-1 w-full rounded-lg border border-slate-300"></textarea></label>@error("faqRows.$index.question")<span class="text-xs text-rose-600">{{ $message }}</span>@enderror @error("faqRows.$index.answer")<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</div>@endforeach</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">SEO title<input wire:model="metaTitle" maxlength="200" class="mt-1 w-full rounded-xl border border-slate-300">@error('metaTitle')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Primary keyword<input wire:model="primaryKeyword" maxlength="180" class="mt-1 w-full rounded-xl border border-slate-300">@error('primaryKeyword')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700">SEO description<textarea wire:model="metaDescription" maxlength="320" rows="2" class="mt-1 w-full rounded-xl border border-slate-300"></textarea>@error('metaDescription')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">Cover image <span class="font-normal text-slate-400">(JPG, PNG, WebP; max 4 MB)</span><input type="file" wire:model="cover" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm">@error('cover')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Source PDF <span class="font-normal text-slate-400">(max 50 MB; private storage)</span><input type="file" wire:model="pdf" accept="application/pdf,.pdf" class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm">@error('pdf')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Free cheat sheet PDF <span class="font-normal text-slate-400">(max 20 MB; private storage)</span><input type="file" wire:model="cheatSheet" accept="application/pdf,.pdf" class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm">@error('cheatSheet')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700">Publication status<select wire:model="status" class="mt-1 w-full rounded-xl border border-slate-300"><option value="draft">Draft</option><option value="published">Published</option></select>@error('status')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                    <p class="text-xs text-slate-500">To publish, provide an author and price; cover, source PDF, free chapter text or PDF, and cheat sheet; description, audience, contents, and at least one complete FAQ; SEO title, description, and keyword. The keyword must appear in the title, description, SEO title, and SEO description, and its slug form must appear in the slug. All three legal pages must be published. Checkout remains disabled until production payment, queue, and mail checks pass.</p>
                    <div class="flex gap-2">@if($editingId)<button type="button" wire:click="resetForm" class="rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700">Cancel edit</button>@endif<button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-cyan-700 px-5 py-3 text-sm font-bold text-white hover:bg-cyan-800 disabled:opacity-60">{{ $status === 'published' ? 'Publish eBook' : ($editingId ? 'Save eBook' : 'Create draft') }}</button></div>
                </form>
            </div>
            <div class="space-y-4">
                <h2 class="text-lg font-black text-slate-900">Catalog ({{ $ebooks->count() }})</h2>
                @forelse($ebooks as $ebook)
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3"><div><h3 class="font-bold text-slate-900">{{ $ebook->title }}</h3><p class="mt-1 text-xs text-slate-500">{{ $ebook->slug }} · ₦{{ number_format($ebook->price_kobo / 100) }} · {{ $ebook->orders_count }} orders</p></div><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $ebook->status === \App\Enums\EbookStatus::Published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($ebook->status->value) }}</span></div>
                        <button wire:click="editEbook({{ $ebook->id }})" class="mt-3 text-xs font-bold text-cyan-800 hover:underline">Edit</button>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">No eBooks entered yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
