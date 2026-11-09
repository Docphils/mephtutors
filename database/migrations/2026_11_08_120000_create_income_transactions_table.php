<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 40)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('description', 255);
            $table->string('payer_name', 160)->nullable();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('NGN');
            $table->string('method', 30);
            $table->string('reference', 120)->nullable();
            $table->string('gateway_reference', 120)->nullable()->unique();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['received_at', 'method']);
        });

        DB::table('ebook_orders')
            ->where('status', 'paid')
            ->orderBy('id')
            ->chunk(500, function ($orders): void {
                foreach ($orders as $order) {
                    DB::table('income_transactions')->insertOrIgnore([
                        'source_type' => 'ebook_order',
                        'source_id' => $order->id,
                        'description' => 'eBook order '.$order->reference,
                        'payer_name' => $order->buyer_name,
                        'amount' => $order->amount_kobo / 100,
                        'currency' => $order->currency,
                        'method' => $order->paystack_reference ? 'paystack' : 'unrecorded',
                        'reference' => $order->paystack_reference,
                        'gateway_reference' => $order->paystack_reference,
                        'received_at' => $order->paid_at,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                    ]);
                }
            });

        DB::table('bookings')
            ->where('client_payment_status', 'Paid')
            ->orderBy('id')
            ->chunk(500, function ($bookings): void {
                foreach ($bookings as $booking) {
                    DB::table('income_transactions')->insertOrIgnore([
                        'source_type' => 'booking',
                        'source_id' => $booking->id,
                        'description' => 'Booking payment #'.$booking->id,
                        'amount' => $booking->amount,
                        'currency' => 'NGN',
                        'method' => 'unrecorded',
                        'reference' => $booking->payment_reference,
                        'received_at' => null,
                        'created_at' => $booking->created_at,
                        'updated_at' => $booking->updated_at,
                    ]);
                }
            });

        DB::table('crms')
            ->where('payment_status', 'paid')
            ->whereNotNull('quote_amount')
            ->where('quote_amount', '>', 0)
            ->orderBy('id')
            ->chunk(500, function ($records): void {
                foreach ($records as $record) {
                    DB::table('income_transactions')->insertOrIgnore([
                        'source_type' => 'crm',
                        'source_id' => $record->id,
                        'description' => 'Institution payment #'.$record->id,
                        'amount' => $record->quote_amount,
                        'currency' => 'NGN',
                        'method' => 'unrecorded',
                        'reference' => $record->payment_reference,
                        'received_at' => $record->paid_at,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at,
                    ]);
                }
            });

        DB::table('programme_enquiries')
            ->where('payment_status', 'paid')
            ->whereNotNull('price_quote')
            ->where('price_quote', '>', 0)
            ->orderBy('id')
            ->chunk(500, function ($records): void {
                foreach ($records as $record) {
                    DB::table('income_transactions')->insertOrIgnore([
                        'source_type' => 'programme_enquiry',
                        'source_id' => $record->id,
                        'description' => 'Programme payment #'.$record->id,
                        'amount' => $record->price_quote,
                        'currency' => 'NGN',
                        'method' => 'unrecorded',
                        'reference' => $record->payment_reference,
                        'received_at' => null,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_transactions');
    }
};
