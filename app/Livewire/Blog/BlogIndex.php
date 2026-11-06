<?php

namespace App\Livewire\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.visitor')]
class BlogIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Locked]
    public string $archiveType = 'index';

    #[Locked]
    public ?string $archiveSlug = null;

    public function mount(?string $slug = null): void
    {
        abort_unless(config('blog.public_enabled'), 404);
        $this->archiveSlug = $slug;
        $this->archiveType = match (true) {
            request()->routeIs('blog.category') => 'category',
            request()->routeIs('blog.tag') => 'tag',
            default => 'index',
        };

        if ($this->archiveType === 'category' && $slug !== null) {
            BlogCategory::query()->where('slug', $slug)
                ->whereHas('posts', fn (Builder $posts) => $posts->published())
                ->firstOrFail();
        } elseif ($this->archiveType === 'tag' && $slug !== null) {
            BlogTag::query()->where('slug', $slug)
                ->whereHas('posts', fn (Builder $posts) => $posts->published())
                ->firstOrFail();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $archive = match ($this->archiveType) {
            'category' => BlogCategory::query()->where('slug', $this->archiveSlug)->firstOrFail(),
            'tag' => BlogTag::query()->where('slug', $this->archiveSlug)->firstOrFail(),
            default => null,
        };

        $query = BlogPost::query()->published()->with(['category', 'tags']);

        if ($archive instanceof BlogCategory) {
            $query->where('category_id', $archive->id);
        } elseif ($archive instanceof BlogTag) {
            $query->whereHas('tags', fn (Builder $tags) => $tags->whereKey($archive->id));
        }

        $search = mb_substr(trim($this->search), 0, 100);
        if ($search !== '' && $this->archiveType === 'index') {
            $query->where(fn (Builder $posts) => $posts
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('excerpt', 'like', '%'.$search.'%')
                ->orWhere('body', 'like', '%'.$search.'%'));
        }

        $heading = match (true) {
            $archive instanceof BlogCategory => $archive->name,
            $archive instanceof BlogTag => '#'.$archive->name,
            default => 'The MephEd Blog',
        };
        $title = $heading === 'The MephEd Blog' ? 'Blog | MephEd' : $heading.' | MephEd Blog';
        $canonicalUrl = match (true) {
            $archive instanceof BlogCategory => route('blog.category', $archive->slug),
            $archive instanceof BlogTag => route('blog.tag', $archive->slug),
            default => route('blog.index'),
        };

        return view('livewire.blog.index', [
            'posts' => $query->latest('published_at')->paginate(9),
            'categories' => BlogCategory::query()
                ->whereHas('posts', fn (Builder $posts) => $posts->published())
                ->orderBy('name')->get(),
            'heading' => $heading,
        ])->title($title)->layoutData([
            'metaDescription' => 'Practical ideas and insights for learners, parents, educators, and school leaders.',
            'canonicalUrl' => $canonicalUrl,
        ]);
    }
}
