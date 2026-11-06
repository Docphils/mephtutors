<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Private preview</p><h1 class="text-xl font-black text-slate-900">{{ $post->title }}</h1></div>
            <a wire:navigate href="{{ route('admin.blog.edit', $post->id) }}" class="rounded-lg bg-cyan-800 px-4 py-2 text-sm font-bold text-white hover:bg-cyan-900">Back to editor</a>
        </div>
    </x-slot>
    @include('livewire.blog.article-content', ['post' => $post])
</div>
