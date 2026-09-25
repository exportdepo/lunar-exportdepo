<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductResource\Pages\ManageProductMedia;
use App\Support\Media\ReplaceMediaFile;
use App\Support\RelationManagers\MediaRelationManager;
use Database\Seeders\DemoDataSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Tests\TestCase;

/**
 * Panelde ürün medyasının görünürlüğünü ve düzenlenebilirliğini doğrular.
 *
 * Not: Panel görsel kolonu Lunar'da 'small' conversion'ı üretilmemişse boş
 * kalır; bu yüzden conversion üretimi burada regresyon olarak kilitlenir.
 */
class MediaPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('lunar'));
    }

    public function test_product_media_page_renders_for_staff(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('lunarbook-pro-14');

        $this->actingAs($this->staff(), 'staff')
            ->get('/exportdepo/products/'.$product->id.'/media')
            ->assertOk()
            ->assertSee('lunarbook-pro-14.jpg');
    }

    public function test_seeded_media_have_generated_thumbnails_for_the_panel(): void
    {
        $this->seed(DemoDataSeeder::class);

        $media = Product::whereHas('urls', fn ($query) => $query->where('slug', 'lunarbook-pro-14'))
            ->first()
            ->media()
            ->orderBy('order_column')
            ->get();

        $this->assertCount(3, $media);

        foreach ($media as $item) {
            $this->assertTrue(
                $item->hasGeneratedConversion('small'),
                "{$item->file_name} için 'small' conversion üretilmemiş; panel görseli boş kalır."
            );

            $this->assertFileExists($item->getPath('small'));
            $this->assertStringContainsString('/conversions/', $item->getAvailableUrl(['small']));
        }
    }

    public function test_media_relation_manager_lists_records_and_offers_replacement_field(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('lunarbook-pro-14');
        $media = $product->media()->orderBy('order_column')->get();

        Livewire::test(MediaRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => ManageProductMedia::class,
            'mediaCollection' => 'images',
        ])
            ->assertOk()
            ->assertCanSeeTableRecords($media)
            // Görsel kolonu conversion URL'i ile dolu gelmeli (boş kalmamalı).
            ->assertTableColumnStateSet('image', $media->first()->getAvailableUrl(['small']), $media->first())
            // Düzenleme modalında yeni dosya seçilebilmeli.
            ->mountTableAction('edit', $media->first())
            ->assertFormFieldExists('media');
    }

    public function test_replace_media_file_swaps_the_file_and_keeps_metadata(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('lunarbook-pro-14');
        $original = $product->media()->orderBy('order_column')->first();

        $newMedia = app(ReplaceMediaFile::class)->handle(
            $original,
            UploadedFile::fake()->image('yeni-gorsel.jpg', 1200, 1200),
        );

        // Eski kayıt silinmiş, yeni kayıt aynı sırayı ve primary işaretini korumuş olmalı.
        $this->assertDatabaseMissing('media', ['id' => $original->id]);
        $this->assertSame($original->order_column, $newMedia->order_column);
        $this->assertSame($original->collection_name, $newMedia->collection_name);
        $this->assertTrue((bool) ($newMedia->custom_properties['primary'] ?? false));
        $this->assertSame('yeni-gorsel.jpg', $newMedia->file_name);

        // Yeni dosya için de panel thumbnail'ı (small) üretilmiş olmalı.
        $this->assertTrue($newMedia->hasGeneratedConversion('small'));

        // Galeri sırası değişmemeli.
        $this->assertSame(
            ['yeni-gorsel.jpg', 'lunarbook-pro-14-gorsel-2.jpg', 'lunarbook-pro-14-gorsel-3.jpg'],
            $product->refresh()->media()->orderBy('order_column')->pluck('file_name')->all()
        );
    }

    public function test_media_table_action_survives_a_real_livewire_update_request(): void
    {
        // Filament, tablo eylemlerini (Düzenle / Oluştur) ilişki yöneticisinin
        // Livewire bileşeni üzerinden çalıştırır. Bileşen panel kurulurken
        // Resource::getRelations() listesinden kaydedilir; Lunar'ın
        // ProductResource'ı bu metodu override etmediği için kayıt yapılmaz ve
        // livewire/update isteğinde ComponentNotFoundException ->
        // LivewireReleaseTokenMismatchException -> 419 döner.
        //
        // Bu test kasıtlı olarak Livewire::test() YERİNE gerçek HTTP
        // /livewire/update isteği atar; test kütüphanesi bileşen kaydını
        // atlattığı için 419 yalnızca bu yolla yakalanabiliyor.
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('lunarbook-pro-14');
        $media = $product->media()->orderBy('order_column')->first();

        $this->actingAs($this->staff(), 'staff');

        $html = $this->get('/exportdepo/products/'.$product->id.'/media')
            ->assertOk()
            ->getContent();

        // İlişki yöneticisi bileşeninin snapshot'ını çıkar.
        $snapshot = null;
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);

        foreach ($matches[1] as $raw) {
            $decoded = json_decode(html_entity_decode($raw, ENT_QUOTES), true);

            if (is_array($decoded) && str_contains((string) ($decoded['memo']['name'] ?? ''), 'media-relation-manager')) {
                $snapshot = $decoded;
            }
        }

        $this->assertNotNull($snapshot, 'Medya ilişki yöneticisi bileşeni sayfada bulunamadı.');

        // Filament v4, tablo eylem wire:key'ini md5(recordKey) olarak üretir.
        preg_match('/actions\.edit\.([0-9a-f]{32})/', $html, $keyMatch);
        $this->assertNotEmpty($keyMatch[1] ?? null, 'Medya tablosunda düzenleme eylemi bulunamadı.');

        $response = $this->post('/livewire/update', [
            '_token' => csrf_token(),
            'components' => [[
                'snapshot' => json_encode($snapshot),
                'updates' => [],
                'calls' => [[
                    'path' => '',
                    'method' => 'mountTableAction',
                    'params' => ['edit', $keyMatch[1]],
                ]],
            ]],
        ], [
            'X-Livewire' => 'true',
            'Referer' => '/exportdepo/products/'.$product->id.'/media',
        ]);

        $this->assertNotSame(
            419,
            $response->getStatusCode(),
            'Medya düzenleme modalı 419 (Page Expired) döndü; ilişki yöneticisi bileşeni Livewire\'a kayıtlı değil.'
        );

        $response->assertOk();
    }

    private function staff(): Staff
    {
        return Staff::factory()->create(['admin' => true]);
    }

    private function productBySlug(string $slug): Product
    {
        return Product::whereHas('urls', fn ($query) => $query->where('slug', $slug))->firstOrFail();
    }
}
