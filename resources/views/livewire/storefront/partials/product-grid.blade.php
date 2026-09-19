{{--
    Ortak ürün ızgarası (ana sayfa bölümleri ve arama sonuçları).

    Beklenen veriler:
    - $gridProducts : ürün dizileri (id, name, description, price, stock, image_url, url)
    - $wishlistIds  : sayfanın istek listesindeki ürün id'leri (Livewire public property)
--}}
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @foreach ($gridProducts as $product)
        <x-product-card :product="$product"
                        :show-wishlist="true"
                        :in-wishlist="in_array($product['id'], $wishlistIds, true)" />
    @endforeach
</div>
