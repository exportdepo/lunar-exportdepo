<?php

namespace App\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class PrivacyPage extends Component
{
    public function render()
    {
        return view('livewire.storefront.privacy-page')->layoutData([
            'title' => 'Gizlilik Politikası ve KVKK',
            'description' => 'ExportDepo gizlilik politikası: kişisel verilerinizin 6698 sayılı KVKK kapsamında işlenmesi ve saklanması.',
        ]);
    }
}
