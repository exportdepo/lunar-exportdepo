<?php

namespace App\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class ReturnsPage extends Component
{
    public function render()
    {
        return view('livewire.storefront.returns-page')->layoutData([
            'title' => 'İade ve Değişim',
            'description' => 'ExportDepo iade ve değişim koşulları: 14 gün içinde kolay iade, süreç adımları ve kapsam dışı ürünler.',
        ]);
    }
}
