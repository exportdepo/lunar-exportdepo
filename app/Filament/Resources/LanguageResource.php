<?php

namespace App\Filament\Resources;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\LanguageResource as LunarLanguageResource;

class LanguageResource extends LunarLanguageResource
{
    /**
     * Dil kodu doğrulaması: yalnızca geçerli ISO biçimleri kabul edilir
     * (tr, en, tr-TR...). Faker/tanımsız kodlarla yanlış dil kaydı
     * oluşturulmasını engeller.
     */
    protected static function getCodeFormComponent(): Component
    {
        return TextInput::make('code')
            ->label(__('lunarpanel::language.form.code.label'))
            ->required()
            ->minLength(2)
            ->maxLength(5)
            ->regex('/^[a-z]{2}(-[A-Z]{2})?$/')
            ->unique(table: 'lunar_languages', column: 'code', ignoreRecord: true)
            ->helperText('Örn: tr, en, en-GB');
    }

    /**
     * Dil listesine toplu silme aksiyonu ekler.
     * Default dil ve URL'leri olan diller korunur.
     */
    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Seçilenleri sil')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Seçili diller silinecek. Varsayılan dil ve bu dillere bağlı URL\'ler korunur.')
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records, \Filament\Actions\DeleteBulkAction $action) {
                            $blocked = $records->filter(
                                fn ($record) => $record->default
                                    || \Lunar\Models\Url::where('language_id', $record->id)->exists()
                            );

                            if ($blocked->isNotEmpty()) {
                                $action->halt(
                                    'Şu diller silinemedi (varsayılan dil veya URL bağlantısı var): '
                                    .$blocked->pluck('name')->implode(', ')
                                );
                            }
                        })
                        ->successNotificationTitle('Diller silindi.')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
