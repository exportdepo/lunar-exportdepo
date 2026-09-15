<?php

namespace App\Providers;

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
            \Lunar\Admin\Filament\Pages\Dashboard::class => PanelDashboardExtension::class,
        ]);

        return parent::register();
    }

    protected function defaultPanel(): \Filament\Panel
    {
        return parent::defaultPanel()
            ->path('exportdepo')
            ->brandName('ExportDepo');
    }

    public static function getWidgets(): array
    {
        return [
            \Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderStatsOverview::class,
            \Lunar\Admin\Filament\Widgets\Dashboard\Orders\LatestOrdersTable::class,
            \Lunar\Admin\Filament\Widgets\Dashboard\Orders\PopularProductsTable::class,
        ];
    }

    public static function getResources(): array
    {
        $overrides = [
            \Lunar\Admin\Filament\Resources\CollectionGroupResource::class => \App\Filament\Resources\CollectionGroupResource::class,
            \Lunar\Admin\Filament\Resources\TaxClassResource::class => \App\Filament\Resources\TaxClassResource::class,
            \Lunar\Admin\Filament\Resources\ProductTypeResource::class => \App\Filament\Resources\ProductTypeResource::class,
            \Lunar\Admin\Filament\Resources\LanguageResource::class => \App\Filament\Resources\LanguageResource::class,
        ];

        return array_map(
            fn (string $resource): string => $overrides[$resource] ?? $resource,
            parent::getResources(),
        );
    }
}
