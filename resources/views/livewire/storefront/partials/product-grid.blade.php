{{--
    Ortak ürün ızgarası (ana sayfa, arama sonuçları, PLP).

    Beklenen veriler:
    - $gridProducts : ürün dizileri (id, name, description, price, stock, image_url, url)
    - $wishlistIds  : sayfanın istek listesindeki ürün idleri (Livewire public property)
    - $gridClasses  : opsiyonel sütun yoğunluğu (varsayılan: 1/2/3/4 sütun).
                      Kart alt satırındaki "Sepete Ekle" ikon-only olduğu için
                      sayfalar yoğun kırılımlar kullanabilir: ana sayfa + istek
                      listesi "grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3
                      xl:grid-cols-4 2xl:grid-cols-5", PLP (filtre sütunu içerik
                      alanını daralttığından) "grid grid-cols-1 gap-6 sm:grid-cols-2
                      xl:grid-cols-4"
--}}
@props([
    'gridClasses' => 'grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
])

<div class="{{ $gridClasses }}">
    @foreach ($gridProducts as $product)
        <x-product-card :product="$product"
                        :show-wishlist="true"
                        :in-wishlist="in_array($product['id'], $wishlistIds, true)" />
    @endforeach
</div>
