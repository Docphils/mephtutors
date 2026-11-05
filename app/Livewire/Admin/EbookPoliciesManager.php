<?php

namespace App\Livewire\Admin;

use App\Models\EbookPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('eBook Policies - MephEd Admin')]
class EbookPoliciesManager extends Component
{
    public array $policies = [];

    public bool $confirmPublication = false;

    public function mount(): void
    {
        Gate::authorize('Admin');
        $this->loadPolicies();
    }

    public function savePolicies(): void
    {
        Gate::authorize('Admin');

        $data = $this->validate([
            'policies' => ['required', 'array:refund,terms,privacy'],
            'policies.*.title' => ['required', 'string', 'max:180'],
            'policies.*.content' => ['required', 'string', 'max:30000'],
            'policies.*.is_published' => ['required', 'boolean'],
            'confirmPublication' => ['boolean'],
        ]);

        $publishingDraft = EbookPolicy::query()
            ->whereIn('slug', EbookPolicy::REQUIRED_SLUGS)
            ->get()
            ->contains(fn (EbookPolicy $policy): bool => ! $policy->is_published
                && (bool) $data['policies'][$policy->slug]['is_published']);

        if ($publishingDraft && ! $this->confirmPublication) {
            $this->addError('confirmPublication', 'Confirm that you have reviewed and approved the policy text before publishing it.');

            return;
        }

        DB::transaction(function () use ($data): void {
            foreach (EbookPolicy::REQUIRED_SLUGS as $slug) {
                $policy = EbookPolicy::query()->where('slug', $slug)->lockForUpdate()->firstOrFail();
                $isPublished = (bool) $data['policies'][$slug]['is_published'];
                $policy->fill([
                    'title' => $data['policies'][$slug]['title'],
                    'content' => $data['policies'][$slug]['content'],
                    'is_published' => $isPublished,
                    'published_at' => $isPublished ? ($policy->published_at ?? now()) : null,
                ])->save();
            }
        });

        $this->confirmPublication = false;
        $this->resetValidation();
        $this->loadPolicies();
        session()->flash('success', 'eBook policies saved.');
    }

    public function render()
    {
        Gate::authorize('Admin');

        return view('livewire.admin.ebook-policies-manager', [
            'publishingReady' => EbookPolicy::requiredPoliciesArePublished(),
        ]);
    }

    private function loadPolicies(): void
    {
        $this->policies = EbookPolicy::query()
            ->whereIn('slug', EbookPolicy::REQUIRED_SLUGS)
            ->get()
            ->keyBy('slug')
            ->map(fn (EbookPolicy $policy): array => [
                'title' => $policy->title,
                'content' => $policy->content,
                'is_published' => $policy->is_published,
            ])
            ->all();
    }
}
