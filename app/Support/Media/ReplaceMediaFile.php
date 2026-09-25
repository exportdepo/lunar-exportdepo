<?php

namespace App\Support\Media;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Mevcut bir medya kaydını yeni bir dosyayla değiştirir.
 *
 * Spatie Media Library dosyayı yerinde güncellemeyi desteklemediği için yeni
 * bir medya kaydı oluşturulur; sıra (order_column), koleksiyon ve özel
 * özellikler (ör. ad, primary işareti) korunur, eski kayıt ve dosyası silinir.
 */
class ReplaceMediaFile
{
    /**
     * @param  array<string, mixed>  $customProperties  Yeni kayda yazılacak ek özellikler
     */
    public function handle(Media $media, UploadedFile|string $file, array $customProperties = []): Media
    {
        $owner = $media->model;

        $adder = $file instanceof UploadedFile
            ? $owner->addMediaFromString($file->get())->usingFileName($file->getClientOriginalName())
            : $owner->addMedia($file)->usingFileName(basename($file));

        $newMedia = $adder
            ->withCustomProperties(array_merge(
                $media->custom_properties ?? [],
                $customProperties,
            ))
            ->preservingOriginal()
            ->toMediaCollection($media->collection_name);

        // Galeri sırası korunur; eski kayıt (ve dosyası) silinir.
        $newMedia->forceFill(['order_column' => $media->order_column])->save();
        $media->delete();

        return $newMedia->refresh();
    }
}
