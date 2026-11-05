<?php

namespace Database\Factories;

use App\Enums\EbookStatus;
use App\Models\Ebook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Ebook> */
class EbookFactory extends Factory
{
    protected $model = Ebook::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'title' => $title,
            'subtitle' => fake()->optional()->sentence(),
            'author_name' => fake()->name(),
            'publisher_name' => 'MephEd',
            'price_kobo' => fake()->numberBetween(100000, 5000000),
            'currency' => 'NGN',
            'status' => EbookStatus::Draft,
            'faq' => [],
        ];
    }
}
