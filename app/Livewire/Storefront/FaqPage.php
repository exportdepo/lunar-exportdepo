<?php

namespace App\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class FaqPage extends Component
{
    public function render()
    {
        return view('livewire.storefront.faq-page')->layoutData([
            'title' => 'Sıkça Sorulan Sorular',
            'description' => 'ExportDepo hakkında sıkça sorulan sorular: sipariş, kargo, ödeme, iade ve hesap işlemleri.',
        ]);
    }
}
