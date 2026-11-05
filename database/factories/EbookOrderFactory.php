<?php

namespace Database\Factories;

use App\Enums\EbookOrderStatus;
use App\Models\Ebook;
use App\Models\EbookOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<EbookOrder> */
class EbookOrderFactory extends Factory
{
    protected $model = EbookOrder::class;

    public function definition(): array
    {
        return [
            'reference' => (string) Str::uuid(),
            'ebook_id' => Ebook::factory(),
            'buyer_name' => fake()->name(),
            'buyer_email' => fake()->unique()->safeEmail(),
            'amount_kobo' => 250000,
            'currency' => 'NGN',
            'status' => EbookOrderStatus::Pending,
        ];
    }
}
