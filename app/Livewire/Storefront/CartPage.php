<?php

namespace App\Livewire\Storefront;

use App\Support\VariantOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

#[Layout('layouts.storefront')]
class CartPage extends Component
{
    public array $lines = [];

    public int $total = 0;

    public int $subTotal = 0;

    public int $taxTotal = 0;

    public int $shippingTotal = 0;

    /**
     * Adet güncellemesi reddedilen satırın kimliği.
     *
     * Hata torbası `cart` anahtarı altında global tutulur. Hata mesajının
     * hangi adet girişine bağlanacağını bilmek için reddedilen satır burada
     * ayrıca saklanır; aksi halde tek bir satırdaki hata sepeteki tüm adet
     * girişlerini hatalı olarak işaretlerdi (WCAG 3.3.1).
     *
     * Değer yalnızca sunucu tarafından belirlenir. `#[Locked]` özniteliği
     * istemcinin bu değeri değiştirmesini engeller; aksi halde sunucuda olmayan
     * bir hata mesajına bağlanan `aria-invalid` işareti oluşabilirdi
     * (WCAG 4.1.2).
     */
    #[Locked]
    public ?int $rejectedLineId = null;

    public function mount(): void
    {
        $this->refreshCart();
    }

    /**
     * Sepet satırı adedini günceller.
     *
     * PDP ile aynı stok kuralı burada da uygulanır: stok takibi olan varyantta
     * adet stoğu aşamaz. (Lunar'ın `purchasable = always` kuralı stok sınırı
     * uygulamadığı için bu kontrol uygulama tarafında yapılır.) Lunar
     * doğrulayıcılarının ürettiği `CartException` da 500'e çevrilmek yerine
     * kullanıcıya Türkçe uyarı olarak gösterilir; stepper sunucudaki gerçek
     * adete geri döndürülür.
     *
     * Adet değeri tarayıcıdan string olarak gelir; boş alan (`''`) geçerli
     * bir tamsayı olmadığı için normalize edilir (bkz. `normaliseQuantity`).
     */
    public function updateQuantity(int $lineId, int|string|null $quantity): void
    {
        $quantity = $this->normaliseQuantity($quantity);

        if ($quantity < 1) {
            $this->removeLine($lineId);

            return;
        }

        $cart = CartSession::current();
        if (! $cart) {
            $this->resetCartState();

            return;
        }

        $line = $cart->lines->firstWhere('id', $lineId);

        if (! $line) {
            // Satır başka bir sekmede kaldırılmış olabilir.
            $this->addError('cart', 'Bu ürün artık sepetinizde bulunmuyor.');
            $this->rejectedLineId = null;
            $this->refreshCart();
            $this->dispatch('cart-updated');

            return;
        }

        $stock = $line->purchasable instanceof ProductVariant ? $line->purchasable->stock : null;
        $rejected = $stock !== null && $quantity > $stock;

        if ($rejected) {
            $this->addError('cart', $this->stockMessage($stock));
            $this->rejectedLineId = $lineId;
        } else {
            try {
                $cart->updateLine($lineId, $quantity);
                $this->resetErrorBag('cart');
                $this->rejectedLineId = null;
            } catch (CartException $exception) {
                $rejected = true;
                $this->addError('cart', $this->cartErrorMessage($exception));
                $this->rejectedLineId = $lineId;
            }
        }

        $this->refreshCart();

        if ($rejected) {
            // Alpine stepper'ı sunucudaki adete eşitler (adet reddedildiğinde
            // istemci tarafında kalan hatalı değeri temizler).
            $this->dispatch('cart-quantity-rejected', lineId: $lineId, quantity: $this->lineQuantity($lineId));
        }

        $this->dispatch('cart-updated');
    }

    public function removeLine(int $lineId): void
    {
        $cart = CartSession::current();
        if (! $cart) {
            $this->resetCartState();

            return;
        }

        try {
            $cart->remove($lineId);
            $this->resetErrorBag('cart');
            $this->rejectedLineId = null;
        } catch (CartException) {
            $this->addError('cart', 'Ürün sepetten kaldırılamadı.');
            // Hata kaldırma işlemine ait; adet girişini hatalı işaretlemek
            // yanlış olur (WCAG 3.3.1).
            $this->rejectedLineId = null;
        }

        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    /**
     * Adet değerini tamsayıya normalize eder.
     *
     * Tarayıcı, adet alanı boşaltıldığında boş string gönderir; bu değer
     * geçerli bir tamsayı olmadığı için aksiyona olduğu gibi geçirilirse
     * `TypeError` oluşur. Boş ya da sayısal olmayan değerler `0` kabul
     * edilir; `0` da `1`'in altında olduğu için satırın kaldırılmasıyla
     * sonuçlanır (aynı davranış alana `0` yazılmasıyla).
     */
    protected function normaliseQuantity(int|string|null $quantity): int
    {
        if (is_int($quantity)) {
            return $quantity;
        }

        if ($quantity === null || ! is_numeric(trim($quantity))) {
            return 0;
        }

        return (int) trim($quantity);
    }

    /**
     * Sepet oturumdan kaybolduğunda (ör. başka bir sekmede temizlendiğinde)
     * satır listesini ve hata durumunu sıfırlar.
     *
     * Hata durumu temizlenmezse `rejectedLineId` bir sonraki istekte de
     * korunur ve görünmeyen bir hata mesajına bağlanan `aria-invalid`
     * işareti kalır (WCAG 4.1.2).
     */
    protected function resetCartState(): void
    {
        $this->resetErrorBag('cart');
        $this->rejectedLineId = null;
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    /** Stok üstü adet için kullanıcıya gösterilecek mesaj. */
    protected function stockMessage(int $stock): string
    {
        return $stock > 0
            ? "Stokta en fazla {$stock} adet bulunuyor."
            : 'Bu ürün şu anda stokta yok.';
    }

    /**
     * Lunar doğrulayıcı mesajları İngilizce üretilir (ör. `CartLineStock`);
     * vitrinde gösterilecek Türkçe metni burada oluşturuyoruz.
     */
    protected function cartErrorMessage(CartException $exception): string
    {
        return $exception->errors()->has('purchasable')
            ? 'Bu ürün şu anda satın alınamıyor.'
            : 'Seçilen adet stok veya satış kurallarına uygun değil.';
    }

    /** Sepetteki satırın sunucudaki güncel adedi (satır yoksa 1). */
    protected function lineQuantity(int $lineId): int
    {
        return (int) (collect($this->lines)->firstWhere('id', $lineId)['quantity'] ?? 1);
    }

    protected function refreshCart(): void
    {
        $cart = CartSession::current();

        if (! $cart) {
            $this->lines = [];
            $this->total = 0;
            $this->subTotal = 0;
            $this->taxTotal = 0;
            $this->shippingTotal = 0;

            return;
        }

        $cart->refresh();
        $cart->recalculate();

        $this->lines = $cart->lines->load(['purchasable.values.option', 'purchasable.product.media'])->map(function ($line): array {
            $variant = $line->purchasable;
            $product = $variant?->product;
            $image = $product?->media->first();

            return [
                'id' => $line->id,
                'name' => (string) ($product?->translateAttribute('name') ?? 'Ürün'),
                'options' => VariantOptions::label($variant),
                'image_url' => $image?->getUrl(),
                'quantity' => (int) $line->quantity,
                'unit_price' => $line->unitPriceInclTax?->value ?? ($line->unitPrice?->value ?? 0),
                'total' => (int) (($line->unitPriceInclTax?->value ?? ($line->unitPrice?->value ?? 0)) * $line->quantity),
            ];
        })->all();

        $this->subTotal = $cart->subTotal?->value ?? 0;
        $this->taxTotal = $cart->taxTotal?->value ?? 0;
        $this->shippingTotal = $cart->shippingTotal?->value ?? 0;
        $this->total = $cart->total?->value ?? 0;
    }

    public function render()
    {
        return view('livewire.storefront.cart-page')->layoutData([
            'title' => 'Sepetim',
        ]);
    }
}
