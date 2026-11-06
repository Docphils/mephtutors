<section class="mx-auto max-w-5xl space-y-5">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Blog Studio</p><h1 class="text-xl font-black text-slate-900">{{ $postId ? 'Edit article' : 'Create article' }}</h1></div>
            <a wire:navigate href="{{ route('admin.blog.index') }}" class="text-sm font-bold text-cyan-800 underline">Back to article list</a>
        </div>
    </x-slot>

    @if (session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-900">
            <p class="font-bold">A few details need your attention.</p>
            <ul class="mt-2 list-inside list-disc text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        <section class="space-y-5" aria-labelledby="article-content-heading">
            <div><h2 id="article-content-heading" class="text-lg font-black text-slate-900">Article content</h2><p class="mt-1 text-sm text-slate-500">Write for readers first. Your draft is private until you publish it.</p></div>
            <label class="block text-sm font-bold text-slate-700">Headline
                <input wire:model="title" maxlength="200" required class="mt-1.5 w-full rounded-xl border-slate-300 text-lg font-semibold" placeholder="A clear, useful article title">
                @error('title')<span class="mt-1 block text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-bold text-slate-700">URL slug
                <input wire:model="slug" maxlength="180" class="mt-1.5 w-full rounded-xl border-slate-300" placeholder="Generated from the headline if left blank">
                <span class="mt-1 block text-xs font-normal text-slate-500">Changing a published article’s slug automatically creates a redirect.</span>
                @error('slug')<span class="mt-1 block text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-bold text-slate-700">Article summary
                <textarea wire:model="excerpt" rows="3" maxlength="500" class="mt-1.5 w-full rounded-xl border-slate-300" placeholder="A short overview for the blog index and search results"></textarea>
                <span class="mt-1 block text-right text-xs font-normal text-slate-500">{{ mb_strlen($excerpt) }}/500</span>
                @error('excerpt')<span class="text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
            </label>

            <section aria-labelledby="featured-image-heading" class="space-y-3">
                <div>
                    <h3 id="featured-image-heading" class="text-sm font-bold text-slate-700">Featured image</h3>
                    <p class="mt-1 text-xs font-normal text-slate-500">Upload a JPG, PNG, or WebP image up to 5 MB. A landscape image around 1200 × 630 works best for article cards and social sharing.</p>
                </div>
                @if ($heroImage && in_array($heroImage->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true))
                    <img src="{{ $heroImage->temporaryUrl() }}" alt="New featured image preview" class="aspect-[1200/630] w-full max-w-2xl rounded-xl border border-slate-200 bg-slate-50 object-cover">
                    <p class="text-xs font-medium text-cyan-800">New image selected; it will replace the current image when you save.</p>
                @elseif ($heroImage)
                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Selected file: {{ $heroImage->getClientOriginalName() }}. Choose a JPG, PNG, or WebP image to continue.</p>
                @elseif ($existingHeroImagePath)
                    <img src="{{ $existingHeroImageUrl }}" alt="Current featured image" class="aspect-[1200/630] w-full max-w-2xl rounded-xl border border-slate-200 bg-slate-50 object-cover">
                @else
                    <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No featured image yet. Generated article artwork, if available, is kept until you upload a replacement.</p>
                @endif
                <label for="blog-hero-image" class="block text-sm font-semibold text-slate-700">Choose an image
                    <input id="blog-hero-image" wire:model="heroImage" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1.5 block w-full rounded-xl border border-slate-300 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-4 file:py-2 file:font-bold file:text-cyan-900 hover:file:bg-cyan-100">
                </label>
                <span wire:loading wire:target="heroImage" role="status" class="block text-xs font-medium text-cyan-800">Uploading image…</span>
                @error('heroImage')<span class="block text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
                @if ($existingHeroImagePath)
                    <label class="flex items-start gap-2 text-sm font-medium text-slate-700">
                        <input wire:model="removeHeroImage" type="checkbox" class="mt-0.5 rounded border-slate-300 text-rose-700 focus:ring-rose-600">
                        <span>Remove the current featured image when saving</span>
                    </label>
                    @error('removeHeroImage')<span class="block text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
                @endif
            </section>

            <div class="block text-sm font-bold text-slate-700">
                <span>Article body</span>
                <div wire:ignore wire:key="blog-body-trix-{{ $postId ?? 'new' }}" class="blog-editor-richtext mt-1.5">
                    <input id="blog-body-editor" type="hidden" value="{{ $bodyForEditor }}">
                    <trix-editor input="blog-body-editor" role="textbox" aria-label="Article body" aria-multiline="true"
                        x-data x-on:trix-change="$wire.set('body', $event.target.value)"
                        x-on:trix-file-accept.prevent
                        class="trix-content blog-rich-text-editor w-full rounded-xl border border-slate-300 bg-white p-3 text-sm leading-7 focus:border-cyan-700 focus:ring-2 focus:ring-cyan-100">
                    </trix-editor>
                </div>
                <span class="mt-1 block text-xs font-normal text-slate-500">Use the formatting toolbar for headings, lists, emphasis, quotes, and links. Unsafe elements and attributes are removed when saved.</span>
                @error('body')<span class="mt-1 block text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="block text-sm font-bold text-slate-700">Category
                    <input wire:model="category" list="blog-categories" maxlength="100" class="mt-1.5 w-full rounded-xl border-slate-300" placeholder="e.g. Teaching">
                    <datalist id="blog-categories">@foreach ($categories as $category)<option value="{{ $category }}">@endforeach</datalist>
                    @error('category')<span class="mt-1 block text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-bold text-slate-700">Tags
                    <input wire:model="tags" maxlength="500" class="mt-1.5 w-full rounded-xl border-slate-300" placeholder="Learning, study skills">
                    <span class="mt-1 block text-xs font-normal text-slate-500">Separate up to 10 tags with commas.</span>
                    @error('tags')<span class="mt-1 block text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
                </label>
            </div>
        </section>

        <details class="group rounded-xl border border-slate-200">
            <summary class="cursor-pointer list-none p-4 font-bold text-slate-900"><span class="flex items-center justify-between">Search and sharing metadata <span class="text-xs font-semibold text-slate-500 group-open:hidden">Optional</span><span class="hidden text-slate-500 group-open:inline">−</span></span></summary>
            <div class="space-y-4 border-t border-slate-200 p-4">
                <label class="block text-sm font-bold text-slate-700">SEO title
                    <input wire:model="metaTitle" maxlength="70" class="mt-1.5 w-full rounded-xl border-slate-300" placeholder="Defaults to the headline">
                    <span class="mt-1 block text-right text-xs font-normal text-slate-500">{{ mb_strlen($metaTitle) }}/70</span>
                </label>
                <label class="block text-sm font-bold text-slate-700">SEO description
                    <textarea wire:model="metaDescription" rows="2" maxlength="320" class="mt-1.5 w-full rounded-xl border-slate-300" placeholder="Defaults to the summary"></textarea>
                </label>
                <label class="block text-sm font-bold text-slate-700">Canonical URL override
                    <input wire:model="canonicalUrl" type="url" class="mt-1.5 w-full rounded-xl border-slate-300" placeholder="Leave blank to use this article’s URL">
                    @error('canonicalUrl')<span class="mt-1 block text-xs font-medium text-rose-700">{{ $message }}</span>@enderror
                </label>
            </div>
        </details>

        <div class="sticky bottom-0 -mx-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-white/95 px-5 pt-4 backdrop-blur sm:-mx-8 sm:px-8">
            <p class="text-xs text-slate-500"><span wire:loading.remove wire:target="save">Draft changes are saved when you choose Save draft.</span><span wire:loading wire:target="save">Saving and securing your content…</span></p>
            <div class="flex gap-2">
                @if ($postId)
                    <a wire:navigate href="{{ route('admin.blog.preview', $postId) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Preview</a>
                @endif
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-xl bg-cyan-800 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-cyan-900 disabled:opacity-60">Save draft</button>
            </div>
        </div>
    </form>
</section>
