<?php

namespace App\Filament\Resources\ProductOptionResource\RelationManagers;

use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ProductOptionResource\RelationManagers\ValuesRelationManager as LunarValuesRelationManager;

/**
 * Ürün seçeneği değerlerinin (Renk: Siyah, Beden: M ...) yönetildiği tablo.
 *
 * Lunar'ın varsayılan tablosuna renk kodu (hex) alanı ekler; böylece vitrindeki
 * renk swatch'ları panelden yönetilebilir. Hex değeri değerin `meta.hex`
 * alanında saklanır ve vitrin bu alanı okuyarak yuvarlak renk örnekleri çizer.
 */
class ValuesRelationManager extends LunarValuesRelationManager
{
    public function getDefaultForm(Schema $schema): Schema
    {
        $schema = parent::getDefaultForm($schema);

        return $schema->components([
            ...$schema->getComponents(),
            ColorPicker::make('meta.hex')
                ->label('Renk kodu')
                ->helperText('Yalnızca renk seçenekleri için doldurulur (vitrin swatch rengi).')
                ->format('hex'),
        ]);
    }

    public function getDefaultTable(Table $table): Table
    {
        $table = parent::getDefaultTable($table);

        return $table->columns([
            ...$table->getColumns(),
            ColorColumn::make('meta.hex')
                ->label('Renk kodu'),
            TextColumn::make('variants_count')
                ->label('Varyant')
                ->counts('variants')
                ->badge()
                ->color('gray'),
        ]);
    }
}
