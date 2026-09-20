<?php

namespace Tests\Feature;

use Lunar\FieldTypes\Text;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Tests\TestCase;

/**
 * Header arama kutusunun (?q=) ana sayfa davranışını doğrular.
 */
class HomePageSearchTest extends TestCase
{
    public function test_home_search_matches_turkish_names_case_and_diacritic_insensitively(): void
    {
        $language = Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        $this->createProduct($language->id, 'Bluetooth Kulaklık', 'kablosuz-kulaklik');
        $this->createProduct($language->id, 'Masa Lambası', 'masa-lambasi');

        // Doğru Türkçe yazım
        $this->get('/?q=kulaklık')
            ->assertOk()
            ->assertSee('Bluetooth Kulaklık')
            ->assertDontSee('Masa Lambası');

        // Büyük harf + noktasız I
        $this->get('/?q=KULAKLIK')
            ->assertOk()
            ->assertSee('Bluetooth Kulaklık')
            ->assertDontSee('Masa Lambası');

        // Türkçe karakter kullanmadan yazım (diakritik katlama: ı -> i, ş -> s)
        $this->get('/?q=lambasi')
            ->assertOk()
            ->assertSee('Masa Lambası')
            ->assertDontSee('Bluetooth Kulaklık');

        // Çok kelimeli arama: tüm kelimeler bulunmalı
        $this->get('/?q=bluetooth kulaklik')
            ->assertOk()
            ->assertSee('Bluetooth Kulaklık')
            ->assertDontSee('Masa Lambası');
    }

    public function test_home_search_hides_hero_and_shows_empty_state_when_nothing_matches(): void
    {
        $language = Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        $this->createProduct($language->id, 'Bluetooth Kulaklık', 'kablosuz-kulaklik');

        // Eşleşmeyen arama: hero gizlenir, sonuç alanı en üstte ve boş durum gösterilir
        $this->get('/?q=bulunmayan-terim')
            ->assertOk()
            ->assertDontSee("Hoş Geldiniz")
            ->assertSee('eşleşen ürün bulunamadı');

        // Aramasız hâlde hero görünür
        $this->get('/')
            ->assertOk()
            ->assertSee("Hoş Geldiniz");
    }

    private function createProduct(int $languageId, string $name, string $slug): Product
    {
        $product = Product::factory()->create(['status' => 'published']);
        $product->update([
            'attribute_data' => collect([
                'name' => new Text($name),
            ]),
        ]);
        $product->urls()->create([
            'language_id' => $languageId,
            'slug' => $slug,
            'default' => true,
        ]);

        return $product;
    }
}
