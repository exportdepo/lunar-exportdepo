<?php

// Intelephense (statik analiz) için IDE yardım dosyası.
//
// Livewire, `Illuminate\View\View` üzerine runtime'da makrolar kaydeder
// (`SupportPageComponents::registerLayoutViewMacros()`): `layoutData`,
// `layout`, `section`, `title`, `slot`, `extends`, `response`. PHP çalışırken
// `Macroable::__call` üzerinden çözüldükleri için sorun yoktur; ancak
// Intelephense makroları göremediğinden "Undefined method 'layoutData'"
// hatası üretir.
//
// Bu dosya **çalışma zamanında asla yüklenmez**: `composer.json` autoload
// bölümünde yer almaz, `require`/`include` ile çağrılmaz. Sadece editörün
// statik analizi içindir (`_ide_helper.php` desenindeki gibi aynı FQCN'e
// ait `@method` etiketleri Intelephense tarafından birleştirilir; sınıf
// gövdesi boştur, dolayısıyla runtime'da yüklenmediği sürece "Cannot
// declare class" çakışması yaşanmaz).

namespace Illuminate\View {
    /**
     * @method $this layoutData(array $data = []) Sayfa bileşeninin layout verisini (title, description, ogImage) ayarlar.
     * @method $this layout(string $view, array $params = [])
     * @method $this section(string $section)
     * @method $this title(string $title)
     * @method $this slot(string $slot)
     * @method $this extends(string $view, array $params = [])
     * @method $this response(callable $callback)
     */
    class View {}
}
