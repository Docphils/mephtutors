<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('crms', function (Blueprint $table) {
            $table->decimal('quote_amount', 12, 2)->nullable()->after('requirements');
            $table->text('quote_notes')->nullable()->after('quote_amount');
            $table->longText('contract_terms')->nullable()->after('quote_notes');

            $table->enum('payment_status', ['pending', 'part_paid', 'paid', 'waived'])
                ->default('pending')
                ->after('status');
            $table->string('payment_reference')->nullable()->after('payment_status');

            $table->timestamp('proposal_sent_at')->nullable()->after('contacted_at');
            $table->timestamp('approved_at')->nullable()->after('proposal_sent_at');
            $table->timestamp('paid_at')->nullable()->after('approved_at');
            $table->timestamp('deployed_at')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crms', function (Blueprint $table) {
            $table->dropColumn([
                'quote_amount',
                'quote_notes',
                'contract_terms',
                'payment_status',
                'payment_reference',
                'proposal_sent_at',
                'approved_at',
                'paid_at',
                'deployed_at',
            ]);
        });
    }
};
