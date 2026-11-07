<?php

namespace App\Livewire\Admin;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class BlogPreview extends Component
{
    #[Locked]
    public BlogPost $post;

    public function mount(int $postId): void
    {
        Gate::authorize('Admin');
        $this->post = BlogPost::query()->with(['author', 'category', 'tags'])->findOrFail($postId);
    }

    public function render()
    {
        Gate::authorize('Admin');

        $authorName = trim((string) $this->post->author?->name);
        $companyName = trim((string) config('blog.author_byline'));
        $authorByline = $authorName !== '' && $companyName !== ''
            ? $authorName.' | '.$companyName
            : ($authorName ?: $companyName);

        return view('livewire.admin.blog.preview', compact('authorByline'))
            ->title($this->post->meta_title ?: $this->post->title.' | Private Preview');
    }
}
