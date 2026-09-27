<?php

namespace App\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class AboutPage extends Component
{
    public function render()
    {
        return view('livewire.storefront.about-page')->layoutData([
            'title' => 'Hakkımızda',
            'description' => 'ExportDepo hakkında: misyonumuz, vizyonumuz ve Türkiye\'nin yeni nesil e-ticaret deneyimini sunan ekibimiz.',
        ]);
    }
}
