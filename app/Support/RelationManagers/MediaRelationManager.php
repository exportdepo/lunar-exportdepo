<?php

namespace App\Support\RelationManagers;

use App\Support\Media\ReplaceMediaFile;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Lunar\Admin\Events\ModelMediaUpdated;
use Lunar\Admin\Support\RelationManagers\MediaRelationManager as LunarMediaRelationManager;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Lunar medya ilişki yöneticisinin proje override'ı.
 *
 * Farklar:
 *  - Düzenleme modalında da dosya alanı görünür; yeni dosya seçilirse mevcut
 *    görsel bu dosyayla değiştirilir (dosya adı, sırası ve özel özellikleri
 *    korunur). Dosya seçilmezse mevcut davranış aynen sürer.
 *  - Görsel kolonu, dönüşüm (conversion) üretilmemişse orijinal dosyaya düşer;
 *    böylece kuyruk/conversion sorunlarında tablo boş görünmez.
 */
class MediaRelationManager extends LunarMediaRelationManager
{
    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('custom_properties.name')
                ->label(__('lunarpanel::relationmanagers.medias.form.name.label'))
                ->maxLength(255),
            Toggle::make('custom_properties.primary')
                ->label(__('lunarpanel::relationmanagers.medias.form.primary.label'))
                ->inline(false),
            FileUpload::make('media')
                ->label(__('lunarpanel::relationmanagers.medias.form.media.label'))
                ->columnSpan(2)
                ->storeFiles(false)
                ->imageEditor()
                // Yeni medya eklerken zorunlu, düzenleme sırasında isteğe bağlı.
                ->required(fn (string $operation): bool => $operation === 'create')
                ->imageEditorAspectRatios([
                    null,
                    '16:9',
                    '4:3',
                    '1:1',
                ]),
        ]);
    }

    public function getDefaultTable(Table $table): Table
    {
        $table = parent::getDefaultTable($table);

        // Conversion üretilmemişse orijinal görsele düş (boş kolon yerine görsel).
        $table->columns(array_map(function ($column) {
            if ($column instanceof ImageColumn && $column->getName() === 'image') {
                $column->state(fn (Media $record): string => $record->getAvailableUrl(['small']));
            }

            return $column;
        }, $table->getColumns()));

        // Düzenleme aksiyonu: yeni dosya seçildiyse mevcut görseli değiştir.
        $table->recordActions(array_map(function ($action) {
            if (! $action instanceof EditAction) {
                return $action;
            }

            return $action->after(function (array $data, Media $record) {
                if ($this->replaceMediaFile($data, $record)) {
                    ModelMediaUpdated::dispatch($this->getOwnerRecord());
                }
            });
        }, $table->getRecordActions()));

        return $table;
    }

    /**
     * Düzenleme formunda yeni bir dosya seçildiyse mevcut medyayı değiştirir.
     *
     * @param  array<string, mixed>  $data
     */
    protected function replaceMediaFile(array $data, Media $record): bool
    {
        $upload = $data['media'] ?? null;

        if (blank($upload)) {
            return false;
        }

        app(ReplaceMediaFile::class)->handle(
            $record,
            $upload,
            $data['custom_properties'] ?? [],
        );

        return true;
    }
}
