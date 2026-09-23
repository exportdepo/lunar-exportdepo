<?php

namespace App\Providers;

use App\Filament\Resources\CollectionGroupResource;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\LanguageResource;
use App\Filament\Resources\ProductOptionResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductReviewResource;
use App\Filament\Resources\ProductTypeResource;
use App\Filament\Resources\TaxClassResource;
use Filament\Panel;
use Lunar\Admin\Filament\Pages\Dashboard;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\LatestOrdersTable;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderStatsOverview;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\PopularProductsTable;
use Lunar\Admin\LunarPanelManager as BaseLunarPanelManager;

/**
 * Subclass that replaces Lunar's default dashboard widgets with
 * a minimal, crash-free set. The original widgets use Apex Charts
 * views that hit a "rename: Erişim engellendi" error on Windows
 * while the compiled view cache is being written.
 */
class CleanLunarPanelManager extends BaseLunarPanelManager
{
    public function register(): BaseLunarPanelManager
    {
        $this->extensions([
            Dashboard::class => PanelDashboardExtension::class,
        ]);

        return parent::register();
    }

    protected function defaultPanel(): Panel
    {
        return parent::defaultPanel()
            ->path('exportdepo')
            ->brandName('ExportDepo');
    }

    public static function getWidgets(): array
    {
        return [
            OrderStatsOverview::class,
            LatestOrdersTable::class,
            PopularProductsTable::class,
        ];
    }

    public static function getResources(): array
    {
        $overrides = [
            \Lunar\Admin\Filament\Resources\CollectionGroupResource::class => CollectionGroupResource::class,
            \Lunar\Admin\Filament\Resources\TaxClassResource::class => TaxClassResource::class,
            \Lunar\Admin\Filament\Resources\ProductTypeResource::class => ProductTypeResource::class,
            \Lunar\Admin\Filament\Resources\LanguageResource::class => LanguageResource::class,
            // Silme aksiyonları FK kısıtları nedeniyle başarısız oluyordu;
            // silme işlemini DeleteCustomers servisine devreder.
            \Lunar\Admin\Filament\Resources\CustomerResource::class => CustomerResource::class,
            // Seçenek değerlerine renk kodu (swatch) yönetimi ekler.
            \Lunar\Admin\Filament\Resources\ProductOptionResource::class => ProductOptionResource::class,
            // Ürün listesine "Etiketler" kolonu + etiket filtresi ekler.
            \Lunar\Admin\Filament\Resources\ProductResource::class => ProductResource::class,
        ];

        $mapped = array_map(
            fn (string $resource): string => $overrides[$resource] ?? $resource,
            parent::getResources(),
        );

        // Uygulamaya özel (Lunar olmayan) kaynaklar panel listesine eklenir.
        return array_merge($mapped, [
            ProductReviewResource::class,
        ]);
    }
}
