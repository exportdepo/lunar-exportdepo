<?php

// Intelephense (statik analiz) için IDE yardım dosyası.
//
// Laravel'in `auth()` helper'ı çalışma zamanında `AuthManager` döndürür;
// `user()`/`id()`/`check()` çağrıları `Macroable`/`__call` üzerinden
// çözüldüğü için PHP'de sorun yoktur. Ancak Intelephense yerleşik
// stub'larında Laravel helper'ları bulunmadığından "Undefined method
// 'user'" / "Undefined method 'id'" hataları üretir.
//
// Dönüş tipi kasti olarak intersection'dır: `user()`, `id()`, `check()`
// Guard'dan; `->name`, `->email` gibi nitelikler App\Models\User'dan
// çözülür (Laravel'in gerçek Guard<User> generik davranışıyla uyumlu).
//
// Bu dosya **çalışma zamanında asla yüklenmez**: `composer.json` autoload
// bölümünde yer almaz, `require`/`include` ile çağrılmaz. Sadece editörün
// statik analizi içindir (`.vscode/settings.json` →
// `intelephense.environment.stubFiles`).

namespace {
    /**
     * Auth guard'ı ya da varsayılan guard'ın kendisini döndürür.
     *
     * @param  string|null  $driver
     * @return \Illuminate\Contracts\Auth\Guard&\App\Models\User
     */
    function auth($driver = null) {}

    /**
     * Değerlendirilmiş view çıktısını döndürür.
     *
     * Laravel'in gerçek `view()` helper'ı dönüş tipi olarak
     * `Factory|View` union'ı bildirir; `View` burada **contract**'tir ve
     * `layoutData`/`layout`/`section` gibi `SupportPageComponents`
     * makroları yalnızca concrete `Illuminate\View\View` üzerinde
     * tanımlıdır. Intelephense union üzerinden method aradığında
     * "Undefined method 'layoutData'" hatası üretir. Dönüş tipi kasten
     * concrete sınıfa sabitlenir; runtime'da zaten her zaman bu sınıf
     * döner (`ViewFactory::make()`).
     *
     * @param  string|\Illuminate\Contracts\View\View|null  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\View\View
     */
    function view($view = null, $data = [], $mergeData = []) {}
}