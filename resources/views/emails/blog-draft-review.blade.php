<div style="font-family:Arial,sans-serif;line-height:1.6;color:#0B2A47;max-width:640px;margin:auto">
    <p style="font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#087E8B">MephEd Editorial Workspace</p>
    <h1 style="font-size:24px;line-height:1.3">A new blog draft is ready for your review</h1>
    <h2 style="font-size:20px">{{ $post->title }}</h2>
    @if ($post->excerpt)
        <p style="color:#475569">{{ $post->excerpt }}</p>
    @endif
    <p>This draft is unpublished. Please review its claims, sources, wording, and artwork before choosing whether to publish it.</p>
    <p><a href="{{ $reviewUrl }}" style="display:inline-block;background:#087E8B;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:bold">Review this draft</a></p>
    <p style="font-size:12px;color:#64748B">This private review link expires in three days and still requires a verified MephEd administrator account.</p>
</div>
