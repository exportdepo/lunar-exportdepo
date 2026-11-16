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
     * Odaklanabilir öğe seçicisi.
     *
     * `disabled` öğeler ve `tabindex="-1"` taşıyanlar (odaklanamaz) listeden
     * çıkarılır; `type="hidden"` girdiler zaten odaklanamaz.
     */
    const focusableSelector = [
        'button:not([disabled])',
        '[href]:not([disabled])',
        'input:not([disabled]):not([type="hidden"])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(', ');

    /**
     * Görünür odaklanabilir öğeleri listeler.
     *
     * `getClientRects()` kullanılır (offsetParent `position: fixed` öğelerde
     * yanıltıcı biçimde `null` döner). `display: none` öğelerin rect listesi
     * boş olduğu için zaten elenir.
     */
    const getFocusableElements = (container) => {
        if (!container) {
            return [];
        }

        return Array.from(container.querySelectorAll(focusableSelector))
            .filter((element) => element.getClientRects().length > 0);
    };

    /**
     * Odak tuzağı (focus trap) oluşturur.
     *
     * WCAG 2.1.1 (Keyboard) ve 2.4.3 (Focus Order): modal katman açıkken
     * Tab/Shift+Tab o katmanın içinde döngüde kalmalıdır.
     *
     * Odaklanabilir öğeler her Tab tuşunda yeniden hesaplanır: Alpine
     * geçiş animasyonları ve Livewire'ın DOM morph işlemleri (ör. sepet
     * satırı silinince) ilk/son öğeyi değiştirebilir.
     */
    const createFocusTrap = (container) => {
        const handler = (event) => {
            if (event.key !== 'Tab') {
                return;
            }

            const focusable = getFocusableElements(container);

            if (!focusable.length) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            const active = document.activeElement;

            // Odak katmanın dışına kaçtıysa ya da katmanın kendisindeyse
            // (tabindex="-1" taşıyan panel) ilk/son odaklanabilir öğeye al.
            if (!container.contains(active) || !focusable.includes(active)) {
                event.preventDefault();
                (event.shiftKey ? last : first).focus();

                return;
            }

            if (event.shiftKey && active === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && active === last) {
                event.preventDefault();
                first.focus();
            }
        };

        container.addEventListener('keydown', handler);

        return {
            release() {
                container.removeEventListener('keydown', handler);
            },
        };
    };

    /**
     * Hangi katmanın odak tuzağının etkin olduğunu tutar.
     *
     * Birden fazla katman (arama modalı + mobil menü + sepet çekmecesi) aynı
     * anda açılabilir. Odak geri yüklemesini yalnızca en üstteki katman
     * yapmalıdır; aksi halde kapanan alt katman, üst katmanın odakını çalar.
     */
    let activeTrapOwner = null;

    /**
     * Odak geri yükleme yardımcısı (WCAG 2.4.3).
     *
     * @param {HTMLElement|null} target  Odakın döneceği tetikleyici öğe.
     * @param {string|null} fallbackSelector  Tetikleyici Livewire tarafından
     *                                        yeniden çizildiyse kullanılacak yedek seçici.
     */
    const restoreFocus = (target, fallbackSelector = null) => {
        const element = target?.isConnected
            ? target
            : (fallbackSelector ? document.querySelector(fallbackSelector) : null);

        element?.focus();
    };

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

    /**
     * Sepet çekmecesi (drawer) durumu.
     * Header'daki sepet düğmesi ile `components/cart-drawer.blade.php` kabuğu
     * arasında ortak durumdur. Global store seçilmesiyle durum, Livewire'ın
     * DOM morph işlemlerinden etkilenmez ve her iki bileşen aynı kaynağı okur.
     */
    Alpine.store('cartDrawer', {
        open: false,

        /**
         * Çekmeceyi açan düğme. Çekmece kapanınca odak buraya geri döner
         * (WCAG 2.4.3 — Focus Order).
         */
        trigger: null,

        show(trigger = null) {
            this.trigger = trigger ?? document.activeElement;
            this.open = true;
        },

        hide() {
            this.open = false;
        },

        toggle(trigger = null) {
            if (this.open) {
                this.hide();

                return;
            }

            this.show(trigger);
        },
    });

    /**
     * Header bileşeni durumu ve davranışları.
     * - mobileOpen: mobil hamburger menüsü
     * - searchOpen: arama modalı
     * - focus trap: açılan katmanlarda Tab odağı katman içinde tutulur ve
     *   kapanışta odak tetikleyici düğmeye geri verilir (WCAG 2.4.3).
     */
    Alpine.data('header', () => ({
        mobileOpen: false,
        searchOpen: false,

        _searchTrap: null,
        _mobileTrap: null,
        _searchRestoreTarget: null,
        _mobileRestoreTarget: null,
        _searchActive: false,
        _mobileActive: false,

        init() {
            this.$watch('searchOpen', (open) => {
                if (open) {
                    this.activateSearchModal();
                } else {
                    this.deactivateSearchModal();
                }
            });

            this.$watch('mobileOpen', (open) => {
                if (open) {
                    this.activateMobileMenu();
                } else {
                    this.deactivateMobileMenu();
                }
            });

            // Sepet çekmecesi açılırken header'daki diğer katmanlar kapanır:
            // aksi halde mobil menünün odak tuzağı açık kalır ve çekmece
            // kapandığında odak yanlış yere döner.
            this.$watch('$store.cartDrawer.open', (open) => {
                if (open) {
                    this.mobileOpen = false;
                    this.searchOpen = false;
                }
            });
        },

        /**
         * Arama modalı açılır: odak tuzağı kurulur ve odak arama alanına alınır.
         */
        activateSearchModal() {
            this._searchRestoreTarget = this.$refs.searchTrigger ?? document.activeElement;
            this._searchActive = true;

            this.$nextTick(() => {
                // Arada kapatıldıysa tuzak kurulmaz.
                if (!this._searchActive) {
                    return;
                }

                const modal = this.$refs.searchModal;

                if (!modal) {
                    return;
                }

                this._searchTrap = createFocusTrap(modal);
                activeTrapOwner = 'search';
                this.$refs.searchInput?.focus();
            });
        },

        /**
         * Arama modalı kapanır: tuzak serbest bırakılır, odak tetikleyiciye döner.
         */
        deactivateSearchModal() {
            this._searchActive = false;

            this._searchTrap?.release();
            this._searchTrap = null;

            const target = this._searchRestoreTarget;
            this._searchRestoreTarget = null;

            if (activeTrapOwner !== 'search') {
                return;
            }

            activeTrapOwner = null;
            restoreFocus(target, '#search-trigger-button');
        },

        /**
         * Mobil menü açılır: odak tuzağı kurulur ve odak menünün ilk
         * bağlantısına alınır.
         */
        activateMobileMenu() {
            this._mobileRestoreTarget = this.$refs.mobileTrigger ?? document.activeElement;
            this._mobileActive = true;

            this.$nextTick(() => {
                // Arada kapatıldıysa tuzak kurulmaz.
                if (!this._mobileActive) {
                    return;
                }

                const menu = this.$refs.mobileMenu;

                if (!menu) {
                    return;
                }

                this._mobileTrap = createFocusTrap(menu);
                activeTrapOwner = 'mobile';
                menu.querySelector('a, button')?.focus();
            });
        },

        /**
         * Mobil menü kapanır: tuzak serbest bırakılır, odak hamburger düğmesine döner.
         */
        deactivateMobileMenu() {
            this._mobileActive = false;

            this._mobileTrap?.release();
            this._mobileTrap = null;

            const target = this._mobileRestoreTarget;
            this._mobileRestoreTarget = null;

            if (activeTrapOwner !== 'mobile') {
                return;
            }

            activeTrapOwner = null;
            restoreFocus(target, '#mobile-menu-trigger');
        },
    }));

    /**
     * Sepet çekmecesi (drawer) bileşeni.
     * - focus trap: drawer açıldığında klavyeyi drawer içinde tutar
     * - kapanışta odak tetikleyici sepet düğmesine geri döner
     */
    Alpine.data('cartDrawer', () => ({
        _trap: null,
        _restoreTarget: null,
        _active: false,

        init() {
            this.$watch('$store.cartDrawer.open', (open) => {
                if (open) {
                    this.activate();
                } else {
                    this.deactivate();
                }
            });
        },

        /**
         * Çekmece açılır: odak tuzağı kurulur ve odak panele alınır.
         * Panel `tabindex="-1"` taşıdığı için ekran okuyucu diyalog adını
         * ("Sepetim") duyurabilir.
         */
        activate() {
            this._restoreTarget = this.$store.cartDrawer.trigger ?? document.activeElement;
            this._active = true;

            this.$nextTick(() => {
                // Arada kapatıldıysa tuzak kurulmaz.
                if (!this._active) {
                    return;
                }

                const panel = this.$refs.panel;

                if (!panel) {
                    return;
                }

                this._trap = createFocusTrap(panel);
                activeTrapOwner = 'cartDrawer';
                panel.focus();
            });
        },

        /**
         * Çekmece kapanır: tuzak serbest bırakılır, odak sepet düğmesine döner.
         */
        deactivate() {
            this._active = false;

            this._trap?.release();
            this._trap = null;

            const target = this._restoreTarget;
            this._restoreTarget = null;

            if (activeTrapOwner !== 'cartDrawer') {
                return;
            }

            activeTrapOwner = null;
            restoreFocus(target, '#cart-trigger-button');
            this.$store.cartDrawer.trigger = null;
        },
    }));
});
