<?php

namespace App\Livewire\Storefront;

use App\Support\WishlistManager;
use Livewire\Attributes\On;
use Livewire\Component;

class WishlistIndicator extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('wishlist-updated')]
    public function refreshCount(): void
    {
        $this->count = WishlistManager::count();
    }

    public function render()
    {
        return view('livewire.storefront.wishlist-indicator');
    }
}
