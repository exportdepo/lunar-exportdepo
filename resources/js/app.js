import './bootstrap';

/**
 * Alpine.js entegrasyonu
 * ---------------------------------------------------------------------------
 * Livewire 3, Alpine.js'i kendi paketi içinde taşır ve `window.Alpine` olarak
 * sunar; zaten bir Alpine örneği varsa "Detected multiple instances of Alpine
 * running" uyarısı verip kendi kopyasıyla üzerine yazar. Bu yüzden ikinci bir
 * Alpine kopyası (ör. `npm install alpinejs` + `Alpine.start()`) başlatmak
 * yerine Livewire'ın Alpine örneğine `alpine:init` olayı üzerinden kayıt
 * oluyoruz. Bu, Livewire tabanlı paketlerin (Filament vb.) kullandığı yaklaşımdır.
 *
 * Not: Livewire'ın `@livewireScripts` direktifi Alpine'ı da her sayfada yükler;
 * bu yüzden saf Blade sayfalarında da Alpine kullanılabilir.
 */
document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    if (!Alpine) {
        return;
    }

    /**
     * Karanlık tema tercihi.
     * Doğruluk kaynağı `<html class="dark">` sınıfıdır; seçim localStorage'da
     * kalıcılaştırılır. `components/layouts/app.blade.php` içindeki inline
     * script, sayfa boyanmadan önce sınıfı uygular (FOUC engeli).
     */
    Alpine.store('theme', {
        dark: document.documentElement.classList.contains('dark'),

        toggle() {
            this.dark = !this.dark;

            document.documentElement.classList.toggle('dark', this.dark);

            try {
                localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            } catch (error) {
                // localStorage kullanılamıyorsa (gizli mod vb.) sessizce devam et.
            }
        },
    });
});
