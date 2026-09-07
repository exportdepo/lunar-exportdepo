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
}
