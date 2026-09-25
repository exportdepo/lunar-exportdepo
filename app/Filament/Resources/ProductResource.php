<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\ManageProductMedia;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Admin\Filament\Resources\ProductResource as LunarProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia as LunarManageProductMedia;
use Lunar\Models\Tag;

/**
 * Lunar ProductResource override'ı: panel ürün listesine "Etiketler"
 * kolonu ve etiket filtresi ekler; medya sayfasında düzenleme sırasında
 * görsel değiştirmeye izin veren kendi sayfamızı kullanır.
 */
class ProductResource extends LunarProductResource
{
    /**
     * Medya sayfasını kendi sayfamızla değiştirir (edit modunda dosya değiştirme).
     */
    public static function getDefaultPages(): array
    {
        $pages = parent::getDefaultPages();

        $pages['media'] = ManageProductMedia::route('/{record}/media');

        return $pages;
    }

    /**
     * Alt navigasyondaki medya sekmesi kendi sayfamıza işaret etsin.
     */
    public static function getDefaultSubNavigation(): array
    {
        return array_map(
            fn (string $page): string => $page === LunarManageProductMedia::class ? ManageProductMedia::class : $page,
            parent::getDefaultSubNavigation(),
        );
    }

    public static function getLunarTableColumns(): array
    {
        return LunarProductResource::getTableColumns();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return parent::getDefaultTable($table)
            ->columns(static::getTableColumns())
            ->filters([
                SelectFilter::make('tags')
                    ->label(__('lunarpanel::product.form.tags.label'))
                    ->relationship('tags', 'value')
                    ->multiple()
                    ->preload(),
                ...$table->getFilters(),
            ]);
    }

    public static function getTagsTableColumn(): TextColumn
    {
        return TextColumn::make('tags.value')
            ->label(__('lunarpanel::product.form.tags.label'))
            ->badge()
            ->separator(', ')
            ->toggleable();
    }

    public static function getTableColumns(): array
    {
        return [
            ...static::getLunarTableColumns(),
            static::getTagsTableColumn(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('tags');
    }

    public static function tagFilterOptions(): array
    {
        return Tag::query()->orderBy('value')->pluck('value', 'id')->all();
    }
}
