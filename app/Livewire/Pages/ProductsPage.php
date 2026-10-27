<?php

namespace App\Livewire\Pages;

use App\Models\CompanyProduct;
use App\Models\SiteSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class ProductsPage extends Component
{
    public function render()
    {
        $heading = (string) SiteSetting::getValue('products_page_heading', 'Products and platforms built by MephEd');
        $intro = (string) SiteSetting::getValue(
            'products_page_intro',
            'MephEd builds practical products across education, software, and digital publishing. Explore the platforms available today and the product lines in development.'
        );
        $metaDescription = (string) SiteSetting::getValue(
            'products_page_meta_description',
            'Explore software, education, and digital publishing products built by MephEd.'
        );

        return view('livewire.pages.products-page', [
            'products' => CompanyProduct::query()->published()
                ->orderByDesc('is_featured')->orderBy('display_order')->orderBy('name')->get(),
            'heading' => $heading,
            'intro' => $intro,
        ])->title(SiteSetting::getValue('products_page_meta_title', 'MephEd Products | Software, Education and Publishing'))
            ->layoutData([
                'metaDescription' => $metaDescription,
                'canonicalUrl' => route('products.index'),
                'ogType' => 'website',
                'ogImage' => asset('images/MephEd.png'),
            ]);
    }
}
