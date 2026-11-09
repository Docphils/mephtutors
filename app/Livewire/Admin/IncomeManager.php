<?php

namespace App\Livewire\Admin;

use App\Models\IncomeTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Income - MephEd Admin')]
class IncomeManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $methodFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showForm = false;

    public string $amount = '';

    public string $description = '';

    public string $payerName = '';

    public string $manualMethod = 'bank_transfer';

    public string $reference = '';

    public string $receivedAt = '';

    public string $notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMethodFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function openForm(): void
    {
        $this->resetValidation();
        $this->reset(['amount', 'description', 'payerName', 'reference', 'notes']);
        $this->manualMethod = 'bank_transfer';
        $this->receivedAt = now()->format('Y-m-d\TH:i');
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    public function store(): void
    {
        $validated = $this->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:999999999999.99'],
            'description' => ['required', 'string', 'max:255', 'not_regex:/^\s*$/'],
            'payerName' => ['nullable', 'string', 'max:160'],
            'manualMethod' => ['required', 'in:cash,bank_transfer,other'],
            'reference' => ['nullable', 'string', 'max:120'],
            'receivedAt' => ['required', 'date', 'before_or_equal:now'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        IncomeTransaction::query()->create([
            'source_type' => 'manual',
            'description' => trim($validated['description']),
            'payer_name' => filled($validated['payerName']) ? trim($validated['payerName']) : null,
            'amount' => $validated['amount'],
            'currency' => 'NGN',
            'method' => $validated['manualMethod'],
            'reference' => filled($validated['reference']) ? trim($validated['reference']) : null,
            'received_at' => Carbon::parse($validated['receivedAt']),
            'notes' => filled($validated['notes']) ? trim($validated['notes']) : null,
            'recorded_by' => auth()->id(),
        ]);

        $this->showForm = false;
        $this->reset(['amount', 'description', 'payerName', 'reference', 'notes']);
        $this->resetPage();
        $this->dispatch('income-recorded');
        session()->flash('message', 'Income record added.');
    }

    public function render()
    {
        $query = IncomeTransaction::query()
            ->when($this->methodFilter !== '', fn (Builder $records): Builder => $records->where('method', $this->methodFilter))
            ->when($this->dateFrom !== '', fn (Builder $records): Builder => $records->whereDate('received_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $records): Builder => $records->whereDate('received_at', '<=', $this->dateTo))
            ->when(trim($this->search) !== '', function (Builder $records): void {
                $search = mb_substr(trim($this->search), 0, 100);
                $records->where(function (Builder $matches) use ($search): void {
                    $matches->where('description', 'like', '%'.$search.'%')
                        ->orWhere('payer_name', 'like', '%'.$search.'%')
                        ->orWhere('reference', 'like', '%'.$search.'%');
                });
            });

        $allRecords = IncomeTransaction::query();

        return view('livewire.admin.income-manager', [
            'transactions' => $query->latest('received_at')->latest('id')->paginate(15),
            'allTimeNairaTotal' => $allRecords->where('currency', 'NGN')->sum('amount'),
            'monthNairaTotal' => IncomeTransaction::query()
                ->where('currency', 'NGN')
                ->whereBetween('received_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
            'unrecordedCount' => IncomeTransaction::query()->where('method', 'unrecorded')->count(),
            'recordCount' => IncomeTransaction::query()->count(),
        ]);
    }
}
