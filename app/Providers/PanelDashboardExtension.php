<?php

namespace App\Providers;

use Lunar\Admin\Support\Extending\BaseExtension;

class PanelDashboardExtension extends BaseExtension
{
    public function getChartWidgets(array $widgets): array
    {
        return [];
    }
}
