<?php

namespace App\Livewire\Ebooks;

use App\Enums\EbookStatus;
use App\Models\Ebook;
use App\Models\EbookEvent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class FreeChapterPage extends Component
{
    public Ebook $ebook;

    public function mount(string $slug): void
    {
        $this->ebook = Ebook::query()->where('slug', $slug)->where('status', EbookStatus::Published)->firstOrFail();
        abort_unless($this->ebook->free_chapter_content || $this->ebook->free_chapter_path, 404);
        EbookEvent::query()->create([
            'type' => 'free_chapter_view',
            'ebook_id' => $this->ebook->id,
            'referrer' => $this->safeReferrer(),
        ]);
    }

    private function safeReferrer(): ?string
    {
        $parts = parse_url((string) request()->headers->get('referer', ''));
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host']) || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $referrer = strtolower($parts['scheme']).'://'.$parts['host'];
        if (isset($parts['port'])) {
            $referrer .= ':'.$parts['port'];
        }
        $referrer .= $parts['path'] ?? '';

        return mb_substr($referrer, 0, 2048);
    }

    public function render()
    {
        return view('livewire.ebooks.free-chapter')
            ->title('Free chapter: '.$this->ebook->title)
            ->layoutData([
                'metaDescription' => 'Read a free chapter from '.$this->ebook->title.' by '.$this->ebook->author_name.'.',
                'canonicalUrl' => route('ebooks.free-chapter', ['slug' => $this->ebook->slug]),
                'ogType' => 'article',
            ]);
    }
}
