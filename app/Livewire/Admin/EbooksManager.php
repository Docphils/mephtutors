<?php

namespace App\Livewire\Admin;

use App\Enums\EbookStatus;
use App\Models\Ebook;
use App\Models\EbookPolicy;
use App\Services\EbookCopywriter;
use App\Services\GroqApiException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('eBooks - MephEd Admin')]
class EbooksManager extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    public string $title = '';

    public string $slug = '';

    public string $subtitle = '';

    public string $description = '';

    public string $audience = '';

    public string $contents = '';

    public array $faqRows = [['question' => '', 'answer' => '']];

    public string $authorName = '';

    public int|string $priceNaira = '';

    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $primaryKeyword = '';

    public string $freeChapterContent = '';

    public string $status = 'draft';

    public $cover;

    public $pdf;

    public $freeChapterPdf;

    public $cheatSheet;

    public ?string $existingCoverPath = null;

    public ?string $existingPdfPath = null;

    public ?string $existingFreeChapterPath = null;

    public ?string $existingCheatSheetPath = null;

    public function mount(): void
    {
        Gate::authorize('Admin');
    }

    public function saveEbook(): void
    {
        Gate::authorize('Admin');
        $this->slug = Str::slug($this->slug ?: $this->title);
        $this->faqRows = array_values(array_filter($this->faqRows, fn ($row) => trim((string) ($row['question'] ?? '')) !== '' || trim((string) ($row['answer'] ?? '')) !== ''));

        $data = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:160', Rule::unique('ebooks', 'slug')->ignore($this->editingId)],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:20000'],
            'audience' => ['nullable', 'string', 'max:10000'],
            'contents' => ['nullable', 'string', 'max:10000'],
            'faqRows' => ['nullable', 'array', 'max:20'],
            'faqRows.*.question' => ['nullable', 'required_with:faqRows.*.answer', 'string', 'max:300'],
            'faqRows.*.answer' => ['nullable', 'required_with:faqRows.*.question', 'string', 'max:3000'],
            'authorName' => ['required', 'string', 'max:160'],
            'priceNaira' => ['required', 'integer', 'min:1', 'max:42949672'],
            'metaTitle' => [$this->status === 'published' ? 'required' : 'nullable', 'string', 'max:200'],
            'metaDescription' => [$this->status === 'published' ? 'required' : 'nullable', 'string', 'max:320'],
            'primaryKeyword' => [$this->status === 'published' ? 'required' : 'nullable', 'string', 'max:180'],
            'freeChapterContent' => ['nullable', 'string', 'max:100000'],
            'freeChapterPdf' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'cheatSheet' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        if ($data['status'] === 'published' && ! EbookPolicy::requiredPoliciesArePublished()) {
            $this->addError('status', 'Publish the refund, terms, and privacy policy pages before publishing eBooks.');

            return;
        }

        $ebook = $this->editingId ? Ebook::query()->findOrFail($this->editingId) : new Ebook;
        $coverPath = $ebook->cover_path;
        $pdfPath = $ebook->pdf_path;
        $cheatSheetPath = $ebook->cheat_sheet_path;
        $freeChapterPath = $ebook->free_chapter_path;

        if ($data['status'] === 'published' && ! $this->pdf && (! $pdfPath || ! Storage::disk('local')->exists($pdfPath))) {
            $this->addError('pdf', 'Upload the source PDF before publishing this eBook.');

            return;
        }
        if ($data['status'] === 'published' && ! $this->cheatSheet && (! $cheatSheetPath || ! Storage::disk('local')->exists($cheatSheetPath))) {
            $this->addError('cheatSheet', 'Upload the owner-supplied free cheat sheet before publishing.');

            return;
        }
        if ($data['status'] === 'published' && ! $this->cover && ! $coverPath) {
            $this->addError('cover', 'Upload the owner-supplied cover before publishing this eBook.');

            return;
        }

        if ($data['status'] === 'published' && trim((string) $data['freeChapterContent']) === '' && ! $this->freeChapterPdf && ! ($freeChapterPath && Storage::disk('local')->exists($freeChapterPath))) {
            $this->addError('freeChapterContent', 'Add the free chapter as text or upload its PDF before publishing.');

            return;
        }

        $faq = collect($data['faqRows'] ?? [])->map(fn ($row) => ['question' => trim((string) ($row['question'] ?? '')), 'answer' => trim((string) ($row['answer'] ?? ''))])->filter(fn ($row) => $row['question'] !== '' && $row['answer'] !== '')->values()->all();
        if ($data['status'] === 'published' && (! $data['description'] || ! $data['audience'] || ! $data['contents'] || $faq === [])) {
            $this->addError('description', 'Publishing needs owner-supplied landing copy, audience, contents, and at least one FAQ.');

            return;
        }

        if ($data['status'] === 'published') {
            $keyword = mb_strtolower($data['primaryKeyword']);
            if (! str_contains(mb_strtolower($data['title']), $keyword)
                || ! str_contains(mb_strtolower($data['description']), $keyword)
                || ! str_contains(mb_strtolower($data['metaTitle']), $keyword)
                || ! str_contains(mb_strtolower($data['metaDescription']), $keyword)
                || ! str_contains($data['slug'], Str::slug($data['primaryKeyword']))) {
                $this->addError('primaryKeyword', 'For SEO, include the primary keyword in the title, opening copy, slug, SEO title, and SEO description.');

                return;
            }
        }

        if ($this->cover) {
            $coverPath = $this->cover->store('ebooks/covers', 'public');
        }
        if ($this->pdf) {
            $pdfPath = $this->pdf->store('ebooks/source', 'local');
        }
        if ($this->freeChapterPdf) {
            $freeChapterPath = $this->freeChapterPdf->store('ebooks/free-chapters', 'local');
        }
        if ($this->cheatSheet) {
            $cheatSheetPath = $this->cheatSheet->store('ebooks/cheat-sheets', 'local');
        }

        $ebook->fill([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'subtitle' => $data['subtitle'] ?: null,
            'description' => $data['description'] ?: null,
            'audience' => $data['audience'] ?: null,
            'contents' => $data['contents'] ?: null,
            'faq' => array_values($faq),
            'author_name' => $data['authorName'],
            'publisher_name' => 'MephEd',
            'price_kobo' => (int) $data['priceNaira'] * 100,
            'currency' => 'NGN',
            'cover_path' => $coverPath,
            'pdf_path' => $pdfPath,
            'cheat_sheet_path' => $cheatSheetPath,
            'meta_title' => $data['metaTitle'] ?: null,
            'meta_description' => $data['metaDescription'] ?: null,
            'primary_keyword' => $data['primaryKeyword'] ?: null,
            'free_chapter_content' => $data['freeChapterContent'] ?: null,
            'free_chapter_path' => $freeChapterPath,
            'status' => EbookStatus::from($data['status']),
        ])->save();

        if ($this->cover && $this->existingCoverPath && $this->existingCoverPath !== $coverPath) {
            Storage::disk('public')->delete($this->existingCoverPath);
        }
        if ($this->pdf && $this->existingPdfPath && $this->existingPdfPath !== $pdfPath) {
            Storage::disk('local')->delete($this->existingPdfPath);
        }
        if ($this->cheatSheet && $this->existingCheatSheetPath && $this->existingCheatSheetPath !== $cheatSheetPath) {
            Storage::disk('local')->delete($this->existingCheatSheetPath);
        }
        if ($this->freeChapterPdf && $this->existingFreeChapterPath && $this->existingFreeChapterPath !== $freeChapterPath) {
            Storage::disk('local')->delete($this->existingFreeChapterPath);
        }

        session()->flash('success', $this->editingId ? 'eBook updated.' : 'eBook created as a draft.');
        $this->resetForm();
    }

    public function editEbook(int $id): void
    {
        Gate::authorize('Admin');
        $ebook = Ebook::query()->findOrFail($id);
        $this->editingId = $ebook->id;
        $this->title = $ebook->title;
        $this->slug = $ebook->slug;
        $this->subtitle = $ebook->subtitle ?? '';
        $this->description = $ebook->description ?? '';
        $this->audience = $ebook->audience ?? '';
        $this->contents = $ebook->contents ?? '';
        $this->faqRows = collect($ebook->faq ?? [])->map(fn ($item) => ['question' => $item['question'] ?? '', 'answer' => $item['answer'] ?? ''])->all() ?: [['question' => '', 'answer' => '']];
        $this->authorName = $ebook->author_name;
        $this->priceNaira = intdiv($ebook->price_kobo, 100);
        $this->metaTitle = $ebook->meta_title ?? '';
        $this->metaDescription = $ebook->meta_description ?? '';
        $this->primaryKeyword = $ebook->primary_keyword ?? '';
        $this->freeChapterContent = $ebook->free_chapter_content ?? '';
        $this->status = $ebook->status->value;
        $this->existingCoverPath = $ebook->cover_path;
        $this->existingPdfPath = $ebook->pdf_path;
        $this->existingFreeChapterPath = $ebook->free_chapter_path;
        $this->existingCheatSheetPath = $ebook->cheat_sheet_path;
        $this->cover = null;
        $this->pdf = null;
        $this->freeChapterPdf = null;
        $this->cheatSheet = null;
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'slug', 'subtitle', 'description', 'audience', 'contents', 'authorName', 'priceNaira', 'metaTitle',
            'metaDescription', 'primaryKeyword', 'freeChapterContent', 'cover', 'pdf', 'freeChapterPdf', 'cheatSheet', 'existingCoverPath', 'existingPdfPath', 'existingFreeChapterPath', 'existingCheatSheetPath',
        ]);
        $this->faqRows = [['question' => '', 'answer' => '']];
        $this->status = 'draft';
        $this->resetValidation();
    }

    public function addFaqRow(): void
    {
        $this->faqRows[] = ['question' => '', 'answer' => ''];
    }

    public function removeFaqRow(int $index): void
    {
        if (count($this->faqRows) > 1) {
            unset($this->faqRows[$index]);
            $this->faqRows = array_values($this->faqRows);
        }
    }

    public function generateCopy(EbookCopywriter $copywriter): void
    {
        Gate::authorize('Admin');
        $this->validate(['title' => ['required', 'string', 'max:200'], 'freeChapterContent' => ['nullable', 'string', 'max:100000'], 'freeChapterPdf' => ['nullable', 'file', 'mimes:pdf', 'max:51200']]);
        $storedPdf = $this->existingFreeChapterPath ? Storage::disk('local')->path($this->existingFreeChapterPath) : null;
        if (trim($this->freeChapterContent) === '' && ! $this->freeChapterPdf && ! ($storedPdf && is_file($storedPdf))) {
            $this->addError('copywriter', 'Add the free chapter text or PDF before asking AI for suggestions.');

            return;
        }
        try {
            $draft = $copywriter->generate($this->title, $this->freeChapterContent, $this->freeChapterPdf?->getRealPath() ?: $storedPdf);
            $this->description = $draft['description'];
            $this->audience = $draft['audience'];
            $this->contents = $draft['contents'];
            $this->faqRows = $draft['faq'] ?: [['question' => '', 'answer' => '']];
            $this->metaTitle = $draft['meta_title'];
            $this->metaDescription = $draft['meta_description'];
            $this->primaryKeyword = $draft['primary_keyword'];
            session()->flash('copywriter_success', 'AI suggestions are ready. Review and edit them before saving or publishing.');
        } catch (GroqApiException $e) {
            report($e);
            $this->addError('copywriter', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->addError('copywriter', 'Could not generate suggestions. Check the AI configuration and try again.');
        }
    }

    public function render()
    {
        Gate::authorize('Admin');

        return view('livewire.admin.ebooks-manager', [
            'ebooks' => Ebook::query()->withCount('orders')->orderByDesc('updated_at')->get(),
        ]);
    }
}
