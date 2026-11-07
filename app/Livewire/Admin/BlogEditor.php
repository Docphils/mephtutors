<?php

namespace App\Livewire\Admin;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogSlugRedirect;
use App\Models\BlogTag;
use App\Services\BlogContentSanitizer;
use App\Services\BlogDraftWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Edit Blog Article | MephEd')]
class BlogEditor extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $postId = null;

    #[Locked]
    public ?string $existingHeroImagePath = null;

    public ?TemporaryUploadedFile $heroImage = null;

    public bool $removeHeroImage = false;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $body = '';

    public string $category = '';

    public string $tags = '';

    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $canonicalUrl = '';

    public string $aiAssistInstruction = '';

    public ?string $aiSuggestionField = null;

    public ?string $aiSuggestion = null;

    public ?string $aiAssistError = null;

    public function mount(?int $postId = null): void
    {
        Gate::authorize('Admin');

        if ($postId === null) {
            return;
        }

        $post = BlogPost::query()->with(['category', 'tags'])->findOrFail($postId);
        $this->postId = $post->id;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->excerpt = $post->excerpt ?? '';
        $this->body = $post->body;
        $this->category = $post->category ? $post->category->name : '';
        $this->tags = $post->tags->pluck('name')->implode(', ');
        $this->metaTitle = $post->meta_title ?? '';
        $this->metaDescription = $post->meta_description ?? '';
        $this->canonicalUrl = $post->canonical_url ?? '';
        $this->existingHeroImagePath = $post->hero_image_path;
    }

    public function updatedHeroImage(): void
    {
        $this->removeHeroImage = false;
    }

    public function requestAiSuggestion(string $field, BlogDraftWriter $writer): void
    {
        Gate::authorize('Admin');

        $fields = [
            'title' => 'Headline',
            'excerpt' => 'Article summary',
            'body' => 'Article body',
            'category' => 'Category',
            'tags' => 'Tags',
            'metaTitle' => 'SEO title',
            'metaDescription' => 'SEO description',
        ];
        $properties = [
            'title' => ['property' => 'title', 'limit' => 200],
            'excerpt' => ['property' => 'excerpt', 'limit' => 500],
            'body' => ['property' => 'body', 'limit' => 100000],
            'category' => ['property' => 'category', 'limit' => 100],
            'tags' => ['property' => 'tags', 'limit' => 500],
            'metaTitle' => ['property' => 'metaTitle', 'limit' => 70],
            'metaDescription' => ['property' => 'metaDescription', 'limit' => 320],
        ];
        if (! isset($fields[$field], $properties[$field])) {
            throw new \InvalidArgumentException('AI assistance is not available for this editor field.');
        }
        if (mb_strlen($this->aiAssistInstruction) > 500) {
            throw ValidationException::withMessages([
                'aiAssistInstruction' => 'The AI instruction may not be longer than 500 characters.',
            ]);
        }

        $this->aiAssistError = null;
        $this->aiSuggestion = null;
        $this->aiSuggestionField = $field;

        try {
            $spec = $properties[$field];
            $this->aiSuggestion = $writer->suggestField(
                $field,
                (string) $this->{$spec['property']},
                $this->aiAssistInstruction,
                $fields,
            );
            if (mb_strlen($this->aiSuggestion) > $spec['limit']) {
                $this->aiSuggestion = null;
                $this->aiAssistError = 'The AI suggestion exceeded this field’s character limit. Try again.';
            }
        } catch (\Throwable $exception) {
            Log::warning('AI field assistance failed in the blog editor.', [
                'field' => $field,
                'admin_id' => auth()->id(),
                'exception' => $exception,
            ]);
            $this->aiAssistError = 'The suggestion could not be generated. Check the configured AI provider and application log, then try again.';
        }
    }

    public function applyAiSuggestion(BlogContentSanitizer $sanitizer): void
    {
        Gate::authorize('Admin');

        $properties = [
            'title' => ['property' => 'title', 'limit' => 200],
            'excerpt' => ['property' => 'excerpt', 'limit' => 500],
            'body' => ['property' => 'body', 'limit' => 100000],
            'category' => ['property' => 'category', 'limit' => 100],
            'tags' => ['property' => 'tags', 'limit' => 500],
            'metaTitle' => ['property' => 'metaTitle', 'limit' => 70],
            'metaDescription' => ['property' => 'metaDescription', 'limit' => 320],
        ];
        $field = $this->aiSuggestionField;
        if ($field === null || $this->aiSuggestion === null || ! isset($properties[$field])) {
            throw new \LogicException('There is no valid AI suggestion to apply.');
        }

        $value = $field === 'body' ? $sanitizer->sanitize($this->aiSuggestion) : trim($this->aiSuggestion);
        if ($value === '' || mb_strlen($value) > $properties[$field]['limit']) {
            throw ValidationException::withMessages([
                'aiSuggestion' => 'The suggestion is empty or exceeds this field’s character limit.',
            ]);
        }

        $this->{$properties[$field]['property']} = $value;
        if ($field === 'body') {
            $this->dispatch('blog-body-suggestion-applied', body: $value);
        }
        $this->dismissAiSuggestion();
    }

    public function dismissAiSuggestion(): void
    {
        Gate::authorize('Admin');
        $this->aiSuggestionField = null;
        $this->aiSuggestion = null;
        $this->aiAssistError = null;
    }

    public function save(BlogContentSanitizer $sanitizer): void
    {
        Gate::authorize('Admin');
        $data = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:100000'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:500'],
            'metaTitle' => ['nullable', 'string', 'max:70'],
            'metaDescription' => ['nullable', 'string', 'max:320'],
            'canonicalUrl' => ['nullable', 'url', 'max:2048'],
            'heroImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'removeHeroImage' => ['boolean'],
        ]);
        $slug = $this->uniqueSlug(($data['slug'] ?? '') ?: $data['title']);
        $categoryName = trim((string) ($data['category'] ?? ''));
        if ($categoryName !== '' && (Str::slug($categoryName) === '' || Str::length(Str::slug($categoryName)) > 120)) {
            throw ValidationException::withMessages(['category' => 'Enter a category name containing letters or numbers.']);
        }

        $tagNames = collect(explode(',', $data['tags'] ?? ''))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique(fn (string $name) => Str::slug($name));
        if ($tagNames->count() > 10) {
            throw ValidationException::withMessages(['tags' => 'Use no more than 10 unique tags.']);
        }
        if ($tagNames->contains(fn (string $name) => Str::slug($name) === '' || Str::length(Str::slug($name)) > 120 || Str::length($name) > 100)) {
            throw ValidationException::withMessages(['tags' => 'Each tag must contain letters or numbers and be no longer than 100 characters.']);
        }

        $storedHeroImagePath = null;
        if ($this->heroImage !== null) {
            $storedPath = $this->heroImage->store('blog/heroes', 'public');
            if (! is_string($storedPath)) {
                throw new \RuntimeException('The featured image could not be stored. Please try uploading it again.');
            }
            $storedHeroImagePath = $storedPath;
        }

        $previousHeroImagePath = null;
        $transactionCompleted = false;
        try {
            $post = DB::transaction(function () use ($data, $slug, $sanitizer, $categoryName, $tagNames, $storedHeroImagePath, &$previousHeroImagePath): BlogPost {
                $post = $this->postId ? BlogPost::query()->lockForUpdate()->findOrFail($this->postId) : new BlogPost;
                $previousHeroImagePath = $post->hero_image_path;
                if ($post->exists && $post->status === BlogPost::STATUS_PUBLISHED && $post->slug !== $slug) {
                    BlogSlugRedirect::query()->updateOrCreate(
                        ['old_slug' => $post->slug],
                        ['blog_post_id' => $post->id]
                    );
                }

                $category = $categoryName === '' ? null : BlogCategory::query()->firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    ['name' => $categoryName]
                );
                $post->fill([
                    'title' => $data['title'],
                    'slug' => $slug,
                    'excerpt' => $data['excerpt'] ?? null,
                    'body' => $sanitizer->sanitize($data['body']),
                    'status' => $post->status ?: BlogPost::STATUS_DRAFT,
                    'author_id' => $post->author_id ?: auth()->id(),
                    'editor_id' => auth()->id(),
                    'category_id' => $category?->id,
                    'meta_title' => $data['metaTitle'] ?? null,
                    'meta_description' => $data['metaDescription'] ?? null,
                    'canonical_url' => $data['canonicalUrl'] ?? null,
                    'hero_image_path' => $storedHeroImagePath
                        ?? ($data['removeHeroImage'] ? null : $post->hero_image_path),
                ]);
                $post->save();
                $tagIds = $tagNames->map(function (string $name): int {
                    return BlogTag::query()->firstOrCreate(
                        ['slug' => Str::slug($name)],
                        ['name' => $name]
                    )->id;
                });
                $post->tags()->sync($tagIds);
                BlogSlugRedirect::query()->where('old_slug', $slug)->delete();

                return $post;
            });
            $transactionCompleted = true;
        } finally {
            if (! $transactionCompleted && is_string($storedHeroImagePath)) {
                Storage::disk('public')->delete($storedHeroImagePath);
            }
        }

        if (
            $previousHeroImagePath !== null
            && $previousHeroImagePath !== $post->hero_image_path
            && Storage::disk('public')->exists($previousHeroImagePath)
            && ! Storage::disk('public')->delete($previousHeroImagePath)
        ) {
            Log::warning('A replaced blog featured image could not be deleted.', [
                'blog_post_id' => $post->id,
                'path' => $previousHeroImagePath,
            ]);
        }

        $this->postId = $post->id;
        $this->existingHeroImagePath = $post->hero_image_path;
        $this->heroImage = null;
        $this->removeHeroImage = false;
        session()->flash('success', 'Article saved as a draft.');
        $this->redirectRoute('admin.blog.edit', ['postId' => $post->id], navigate: true);
    }

    private function uniqueSlug(string $value): string
    {
        $slug = Str::slug($value);
        if ($slug === '') {
            throw ValidationException::withMessages(['slug' => 'Enter a title or a valid URL slug.']);
        }
        if (Str::length($slug) > 180) {
            throw ValidationException::withMessages(['slug' => 'The URL slug may not be longer than 180 characters.']);
        }

        $postQuery = BlogPost::query()->where('slug', $slug);
        if ($this->postId !== null) {
            $postQuery->whereKeyNot($this->postId);
        }
        $redirectQuery = BlogSlugRedirect::query()->where('old_slug', $slug);
        if ($this->postId !== null) {
            $redirectQuery->where('blog_post_id', '!=', $this->postId);
        }

        if ($postQuery->exists() || $redirectQuery->exists()) {
            throw ValidationException::withMessages(['slug' => 'That slug is already in use.']);
        }

        return $slug;
    }

    public function render(BlogContentSanitizer $sanitizer)
    {
        Gate::authorize('Admin');

        return view('livewire.admin.blog.editor', [
            'post' => $this->postId ? BlogPost::query()->findOrFail($this->postId) : null,
            'categories' => BlogCategory::query()->orderBy('name')->pluck('name'),
            'aiSuggestionFieldLabel' => match ($this->aiSuggestionField) {
                'title' => 'Headline',
                'excerpt' => 'Article summary',
                'body' => 'Article body',
                'category' => 'Category',
                'tags' => 'Tags',
                'metaTitle' => 'SEO title',
                'metaDescription' => 'SEO description',
                default => null,
            },
            'bodyForEditor' => $sanitizer->sanitize($this->body),
            'existingHeroImageUrl' => $this->existingHeroImagePath !== null
                ? Storage::disk('public')->url($this->existingHeroImagePath)
                : null,
        ]);
    }
}
