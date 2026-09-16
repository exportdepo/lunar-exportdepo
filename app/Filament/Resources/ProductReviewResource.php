<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages\EditProductReview;
use App\Filament\Resources\ProductReviewResource\Pages\ListProductReviews;
use App\Models\ProductReview;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Lunar\Admin\Support\Resources\BaseResource;

class ProductReviewResource extends BaseResource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $permission = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 60;

    public static function getLabel(): string
    {
        return 'Ürün Yorumu';
    }

    public static function getPluralLabel(): string
    {
        return 'Ürün Yorumları';
    }

    public static function getNavigationLabel(): string
    {
        return 'Ürün Yorumları';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Katalog';
    }

    public static function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('product_id')
                    ->label('Ürün')
                    ->relationship('product', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->translateAttribute('name'))
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->label('Yorumcu adı')
                    ->required()
                    ->maxLength(100),
                Select::make('rating')
                    ->label('Puan')
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                    ->required(),
                TextInput::make('title')
                    ->label('Başlık')
                    ->maxLength(150),
                Textarea::make('body')
                    ->label('Yorum')
                    ->required()
                    ->columnSpan(2),
                Toggle::make('is_approved')
                    ->label('Onaylı')
                    ->helperText('Yalnızca onaylı yorumlar vitrinde görünür.'),
            ])->columns(2),
        ]);
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_title')
                    ->label('Ürün')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Yorumcu'),
                TextColumn::make('rating')
                    ->label('Puan')
                    ->badge()
                    ->color(fn ($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger')),
                TextColumn::make('title')
                    ->label('Başlık')
                    ->limit(30),
                TextColumn::make('body')
                    ->label('Yorum')
                    ->limit(50),
                IconColumn::make('is_approved')
                    ->label('Onay')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Onay durumu'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->label('Onayla')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->is_approved)
                    ->action(fn ($record) => $record->update(['is_approved' => true])),
                Action::make('reject')
                    ->label('Onayı kaldır')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_approved)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['is_approved' => false])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Seçilenleri onayla')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_approved' => true])),
                    DeleteBulkAction::make()
                        ->label('Seçilenleri sil'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListProductReviews::route('/'),
            'edit' => EditProductReview::route('/{record}/edit'),
        ];
    }
}
