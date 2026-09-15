<?php

namespace Tests\Concerns;

use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\TaxZone;

trait SeedsLunarDefaults
{
    protected function seedLunarDefaults(): void
    {
        if (! Currency::query()->where('default', true)->exists()) {
            Currency::factory()->create([
                'code' => 'TRY',
                'default' => true,
                'enabled' => true,
            ]);
        }

        if (! Channel::query()->where('default', true)->exists()) {
            Channel::factory()->create([
                'handle' => 'web',
                'default' => true,
            ]);
        }

        if (! Language::query()->where('default', true)->exists()) {
            Language::factory()->create([
                'code' => 'tr',
                'default' => true,
            ]);
        }

        if (! CustomerGroup::query()->where('default', true)->exists()) {
            CustomerGroup::factory()->create([
                'handle' => 'default',
                'default' => true,
            ]);
        }

        if (! TaxZone::query()->where('default', true)->exists()) {
            TaxZone::factory()->create();
        }
    }
}
