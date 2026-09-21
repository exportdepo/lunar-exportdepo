<?php

namespace App\Livewire\Storefront;

use App\Support\WishlistManager;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\Channel;
use Lunar\Models\ProductVariant;

#[Layout('layouts.storefront')]
class WishlistPage extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $isAuthenticated = false;

    public ?int $addedProductId = null;

    public function mount(): void
    {
        $this->isAuthenticated = auth()->check();
        $this->loadItems();
    }

    #[On('wishlist-updated')]
    public function loadItems(): void
    {
        $this->items = WishlistManager::products();
    }

    public function remove(int $productId): void
    {
        WishlistManager::remove($productId);

        if ($this->addedProductId === $productId) {
            $this->addedProductId = null;
        }

        $this->loadItems();
        $this->dispatch('wishlist-updated');
    }

    public function addToCart(int $productId): void
    {
        $item = collect($this->items)->firstWhere('id', $productId);

        if (! $item || ! $item['variant_id']) {
            return;
        }

        $variant = ProductVariant::query()->find($item['variant_id']);

        if (! $variant) {
            return;
        }

        $channel = $variant->product->channels()
            ->where('handle', 'web')
            ->first();

        if ($channel instanceof Channel) {
            CartSession::setChannel($channel);
        }

        CartSession::manager()->add($variant, 1);

        $this->addedProductId = $productId;
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.storefront.wishlist-page')->layoutData([
            'title' => 'İstek Listem',
        ]);
    }
}
