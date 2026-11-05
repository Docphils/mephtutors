<?php

namespace App\Console\Commands;

use App\Services\EbookReconciliationService;
use Illuminate\Console\Command;

class ReconcileEbookPayments extends Command
{
    protected $signature = 'ebooks:reconcile-payments';

    protected $description = 'Reconcile pending eBook payments and retry overdue fulfillment';

    public function handle(EbookReconciliationService $reconciliation): int
    {
        $result = $reconciliation->run();
        $this->info("Checked {$result['checked']} pending order(s); recovered {$result['recovered']}.");

        return self::SUCCESS;
    }
}
