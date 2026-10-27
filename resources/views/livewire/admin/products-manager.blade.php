<div class="min-h-screen bg-slate-100 p-4 sm:p-6 lg:p-8">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Company website</p><h1 class="text-2xl font-black text-slate-900">Products Manager</h1><p class="mt-1 text-sm text-slate-500">Manage product lines, availability and the public Products page.</p></div>
            <a wire:navigate href="{{ route('products.index') }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">Preview Products page <i class="fas fa-arrow-up-right-from-square text-xs"></i></a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-5"><h2 class="text-lg font-black text-slate-900">Products page content</h2><p class="mt-1 text-sm text-slate-500">This content appears above the product cards and supplies the page’s search metadata.</p></div>
            <form wire:submit="savePageContent" class="grid gap-4 md:grid-cols-2">
                <label class="text-sm font-semibold text-slate-700">Page heading<input wire:model="pageHeading" maxlength="160" class="mt-1 w-full rounded-xl border-slate-300"></label>
                <label class="text-sm font-semibold text-slate-700">SEO title<input wire:model="pageMetaTitle" maxlength="180" class="mt-1 w-full rounded-xl border-slate-300"></label>
                <label class="text-sm font-semibold text-slate-700 md:col-span-2">Introduction<textarea wire:model="pageIntro" rows="3" maxlength="1200" class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
                <label class="text-sm font-semibold text-slate-700 md:col-span-2">SEO description<textarea wire:model="pageMetaDescription" rows="2" maxlength="320" class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
                <div class="md:col-span-2"><button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800">Save page content</button></div>
            </form>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,0.9fr)]">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="mb-5"><h2 class="text-lg font-black text-slate-900">{{ $editingId ? 'Edit product' : 'Add a product line' }}</h2><p class="mt-1 text-sm text-slate-500">Use “Coming soon” for products that have not launched yet, including future eBooks.</p></div>
                <form wire:submit="saveProduct" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">Product name<input wire:model="name" maxlength="140" class="mt-1 w-full rounded-xl border-slate-300">@error('name')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">URL slug<input wire:model="slug" maxlength="160" placeholder="Generated from product name" class="mt-1 w-full rounded-xl border-slate-300">@error('slug')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Product category<select wire:model="category" class="mt-1 w-full rounded-xl border-slate-300">@foreach($categories as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                        <label class="text-sm font-semibold text-slate-700">Availability<select wire:model="releaseStatus" class="mt-1 w-full rounded-xl border-slate-300">@foreach($releaseStatuses as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                    </div>
                    <label class="block text-sm font-semibold text-slate-700">Short description<textarea wire:model="summary" rows="3" maxlength="500" class="mt-1 w-full rounded-xl border-slate-300" placeholder="A concise factual description for the product card"></textarea>@error('summary')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                    <label class="block text-sm font-semibold text-slate-700">Additional details <span class="font-normal text-slate-400">(optional)</span><textarea wire:model="description" rows="4" maxlength="5000" class="mt-1 w-full rounded-xl border-slate-300"></textarea></label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">Product website<input type="url" wire:model="websiteUrl" maxlength="2048" placeholder="https://example.com" class="mt-1 w-full rounded-xl border-slate-300">@error('websiteUrl')<span class="text-xs text-rose-600">Enter a valid URL.</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Button label<input wire:model="ctaLabel" maxlength="60" placeholder="Visit product" class="mt-1 w-full rounded-xl border-slate-300"></label>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">Image or logo <span class="font-normal text-slate-400">(JPG, PNG or WebP; up to 4 MB)</span><input type="file" wire:model="image" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-xl border border-slate-300 p-2 text-sm">@error('image')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                        <label class="text-sm font-semibold text-slate-700">Display order<input type="number" min="0" wire:model="displayOrder" class="mt-1 w-full rounded-xl border-slate-300"></label>
                    </div>
                    @if ($image)<img src="{{ $image->temporaryUrl() }}" alt="Product preview" class="h-32 w-48 rounded-xl border border-slate-200 object-cover">@elseif($existingImagePath)<img src="{{ asset(str_starts_with($existingImagePath, 'images/') ? $existingImagePath : 'storage/'.$existingImagePath) }}" alt="Current product image" class="h-32 w-48 rounded-xl border border-slate-200 object-cover">@endif
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex items-start gap-2 rounded-xl bg-slate-50 p-3 text-sm text-slate-700"><input type="checkbox" wire:model="isPublished" class="mt-0.5 rounded border-slate-300 text-cyan-700"><span><strong>Published</strong><span class="block text-xs text-slate-500">Visible on the public Products page</span></span></label>
                        <label class="flex items-start gap-2 rounded-xl bg-slate-50 p-3 text-sm text-slate-700"><input type="checkbox" wire:model="isFeatured" class="mt-0.5 rounded border-slate-300 text-cyan-700"><span><strong>Featured</strong><span class="block text-xs text-slate-500">Highlight this product card</span></span></label>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-1">
                        @if($editingId)<button type="button" wire:click="resetProductForm" class="rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700">Cancel edit</button>@endif
                        <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-cyan-700 px-5 py-3 text-sm font-bold text-white hover:bg-cyan-800 disabled:opacity-60">{{ $editingId ? 'Save product' : 'Create product' }}</button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                <div><h2 class="text-lg font-black text-slate-900">Products <span class="font-normal text-slate-500">({{ $products->count() }})</span></h2><p class="mt-1 text-sm text-slate-500">Unpublished products remain visible here for administrators only.</p></div>
                @forelse($products as $product)
                    <article class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <img src="{{ $product->image_url }}" alt="" class="h-20 w-24 shrink-0 rounded-xl bg-slate-100 object-cover">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-slate-900">{{ $product->name }}</h3><span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $product->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $product->is_published ? 'Published' : 'Draft' }}</span></div>
                            <p class="mt-1 text-xs text-slate-500">{{ $categories[$product->category] ?? $product->category }} · {{ $releaseStatuses[$product->release_status] ?? $product->release_status }} · Order {{ $product->display_order }}</p>
                            <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $product->summary }}</p>
                            <div class="mt-3 flex gap-4"><button wire:click="editProduct({{ $product->id }})" class="text-xs font-bold text-cyan-800 hover:underline">Edit</button><button wire:click="deleteProduct({{ $product->id }})" wire:confirm="Remove {{ $product->name }} from the products page?" class="text-xs font-bold text-rose-700 hover:underline">Delete</button></div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">No product entries yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
