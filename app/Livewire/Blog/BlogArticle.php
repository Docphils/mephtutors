<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use App\Models\BlogSlugRedirect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.visitor')]
class BlogArticle extends Component
{
    #[Locked]
    public BlogPost $post;

    public function mount(string $slug)
    {
        abort_unless(config('blog.public_enabled'), 404);
        $post = BlogPost::query()
            ->published()
            ->with(['author', 'category', 'tags'])
            ->where('slug', $slug)->first();
        if ($post === null) {
            $redirect = BlogSlugRedirect::query()->with('post')->where('old_slug', $slug)->first();
            if ($redirect?->post?->status === BlogPost::STATUS_PUBLISHED && $redirect->post->published_at?->isPast()) {
                return redirect()->route('blog.show', $redirect->post->slug, 301);
            }

            abort(404);
        }

        $this->post = $post;
    }

    public function render()
    {
        $description = $this->post->meta_description
            ?: $this->post->excerpt
            ?: str($this->post->body)->stripTags()->limit(160);
        $authorName = trim((string) $this->post->author?->name);
        $companyName = trim((string) config('blog.author_byline'));
        $authorByline = $authorName !== '' && $companyName !== ''
            ? $authorName.' | '.$companyName
            : ($authorName ?: $companyName);
        $canonical = $this->post->canonical_url ?: route('blog.show', $this->post->slug);
        $heroImageExtension = strtolower(pathinfo((string) $this->post->hero_image_path, PATHINFO_EXTENSION));
        $ogImage = $this->post->hero_image_path
            && in_array($heroImageExtension, ['jpg', 'jpeg', 'png', 'webp'], true)
            ? Storage::disk('public')->url($this->post->hero_image_path)
            : asset('images/MephEd.png');
        $relatedPosts = BlogPost::query()
            ->published()
            ->with(['category', 'tags'])
            ->whereKeyNot($this->post->getKey())
            ->when(
                $this->post->category_id,
                fn (Builder $query) => $query->where('category_id', $this->post->category_id)
            )
            ->latest('published_at')
            ->limit(3)
            ->get();
        $structuredData = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $this->post->title,
            'description' => $description,
            'datePublished' => $this->post->published_at?->toAtomString(),
            'dateModified' => $this->post->updated_at?->toAtomString(),
            'author' => $authorName !== ''
                ? ['@type' => 'Person', 'name' => $authorName]
                : ['@type' => 'Organization', 'name' => $companyName],
            'publisher' => ['@type' => 'Organization', 'name' => 'MephEd'],
            'image' => $ogImage,
            'mainEntityOfPage' => $canonical,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);

        return view('livewire.blog.article', [
            'relatedPosts' => $relatedPosts,
            'authorByline' => $authorByline,
        ])->title($this->post->meta_title ?: $this->post->title.' | MephEd Blog')->layoutData([
            'metaDescription' => $description,
            'canonicalUrl' => $canonical,
            'ogType' => 'article',
            'ogImage' => $ogImage,
            'structuredData' => $structuredData,
        ]);
    }
}
