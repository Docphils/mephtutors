<?php

namespace App\Livewire\Admin;

use App\Models\CompanyProduct;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Company Products - MephEd Admin')]
class ProductsManager extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public string $category = 'software';
    public string $releaseStatus = 'available';
    public string $summary = '';
    public string $description = '';
    public string $websiteUrl = '';
    public string $ctaLabel = '';
    public bool $isFeatured = false;
    public bool $isPublished = false;
    public int $displayOrder = 0;
    public $image;
    public ?string $existingImagePath = null;

    public string $pageHeading = '';
    public string $pageIntro = '';
    public string $pageMetaTitle = '';
    public string $pageMetaDescription = '';

    public function mount(): void
    {
        Gate::authorize('Admin');
        $this->pageHeading = (string) SiteSetting::getValue('products_page_heading', 'Products and platforms built by MephEd');
        $this->pageIntro = (string) SiteSetting::getValue('products_page_intro', 'MephEd builds practical products across education, software, and digital publishing. Explore the platforms available today and the product lines in development.');
        $this->pageMetaTitle = (string) SiteSetting::getValue('products_page_meta_title', 'MephEd Products | Software, Education and Publishing');
        $this->pageMetaDescription = (string) SiteSetting::getValue('products_page_meta_description', 'Explore software, education, and digital publishing products built by MephEd.');
    }

    public function savePageContent(): void
    {
        Gate::authorize('Admin');
        $data = $this->validate([
            'pageHeading' => ['required', 'string', 'max:160'],
            'pageIntro' => ['required', 'string', 'max:1200'],
            'pageMetaTitle' => ['required', 'string', 'max:180'],
            'pageMetaDescription' => ['required', 'string', 'max:320'],
        ]);

        foreach ([
            'products_page_heading' => 'pageHeading',
            'products_page_intro' => 'pageIntro',
            'products_page_meta_title' => 'pageMetaTitle',
            'products_page_meta_description' => 'pageMetaDescription',
        ] as $key => $field) {
            SiteSetting::setValue($key, $data[$field]);
        }

        session()->flash('success', 'Products page content saved.');
    }

    public function saveProduct(): void
    {
        Gate::authorize('Admin');
        $this->slug = Str::slug($this->slug ?: $this->name);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:140'],
            'slug' => ['required', 'string', 'max:160', Rule::unique('company_products', 'slug')->ignore($this->editingId)],
            'category' => ['required', Rule::in(array_keys(CompanyProduct::CATEGORIES))],
            'releaseStatus' => ['required', Rule::in(array_keys(CompanyProduct::RELEASE_STATUSES))],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'websiteUrl' => ['nullable', 'url', 'regex:/^https?:\/\//i', 'max:2048'],
            'ctaLabel' => ['nullable', 'string', 'max:60'],
            'isFeatured' => ['boolean'],
            'isPublished' => ['boolean'],
            'displayOrder' => ['required', 'integer', 'min:0', 'max:100000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $product = $this->editingId ? CompanyProduct::findOrFail($this->editingId) : new CompanyProduct();
        $imagePath = $this->existingImagePath;
        if ($this->image) {
            $newImagePath = $this->image->store('company-products', 'public');
            if ($product->image_path && $product->image_path !== $newImagePath) {
                Storage::disk('public')->delete($product->image_path);
            }
            $imagePath = $newImagePath;
        }

        $product->fill([
            'name' => $data['name'], 'slug' => $data['slug'], 'category' => $data['category'],
            'release_status' => $data['releaseStatus'], 'summary' => $data['summary'],
            'description' => $data['description'] ?: null, 'website_url' => $data['websiteUrl'] ?: null,
            'cta_label' => $data['ctaLabel'] ?: null, 'image_path' => $imagePath,
            'is_featured' => (bool) $data['isFeatured'], 'is_published' => (bool) $data['isPublished'],
            'display_order' => $data['displayOrder'],
        ])->save();

        session()->flash('success', $this->editingId ? 'Product updated.' : 'Product created.');
        $this->resetProductForm();
    }

    public function editProduct(int $id): void
    {
        Gate::authorize('Admin');
        $product = CompanyProduct::findOrFail($id);
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->category = $product->category;
        $this->releaseStatus = $product->release_status;
        $this->summary = $product->summary;
        $this->description = $product->description ?? '';
        $this->websiteUrl = $product->website_url ?? '';
        $this->ctaLabel = $product->cta_label ?? '';
        $this->isFeatured = $product->is_featured;
        $this->isPublished = $product->is_published;
        $this->displayOrder = $product->display_order;
        $this->existingImagePath = $product->image_path;
        $this->image = null;
    }

    public function deleteProduct(int $id): void
    {
        Gate::authorize('Admin');
        $product = CompanyProduct::findOrFail($id);
        if ($product->image_path && !str_starts_with($product->image_path, 'images/')) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        if ($this->editingId === $id) $this->resetProductForm();
        session()->flash('success', 'Product removed.');
    }

    public function resetProductForm(): void
    {
        $this->reset([
            'editingId', 'name', 'slug', 'summary', 'description', 'websiteUrl', 'ctaLabel',
            'image', 'existingImagePath',
        ]);
        $this->category = 'software';
        $this->releaseStatus = 'available';
        $this->isFeatured = false;
        $this->isPublished = false;
        $this->displayOrder = 0;
    }

    public function render()
    {
        Gate::authorize('Admin');
        return view('livewire.admin.products-manager', [
            'products' => CompanyProduct::query()->orderBy('display_order')->orderBy('name')->get(),
            'categories' => CompanyProduct::CATEGORIES,
            'releaseStatuses' => CompanyProduct::RELEASE_STATUSES,
        ]);
    }
}
