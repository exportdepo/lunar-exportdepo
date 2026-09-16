<?php

namespace App\Providers;

use App\PaymentTypes\StripePayment;
use App\Support\WishlistManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\LunarPanelManager;
use Lunar\Facades\Payments;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Filament 4 + Lunar 1.x compatibility shim.
        //
        // Filament's routes are loaded by FilamentServiceProvider's
        // packageBooted() which calls Filament::getPanels() to discover
        // all registered panels. If the Lunar panel is not yet registered
        // with Filament at that point, the /lunar/* routes are never
        // created. We register the panel here in register() (which runs
        // for every provider before any boot() runs) so the panel is
        // guaranteed to exist before Filament boots.
        if (! class_exists(LunarPanelManager::class)) {
            return;
        }

        // Replace the default LunarPanelManager binding with our
        // CleanLunarPanelManager subclass so the dashboard widgets
        // list is overridden (Apex Charts views crash on Windows).
        $this->app->scoped('lunar-panel', function ($app) {
            return new CleanLunarPanelManager;
        });
        $this->app->alias('lunar-panel', LunarPanelManager::class);

        $this->app->afterResolving(LunarPanelManager::class, function (LunarPanelManager $manager) {
            $this->safeRegister($manager);
        });

        // Force resolve and register now, while we are still in the
        // register phase and before any boot() that loads routes.
        if ($this->app->resolved(LunarPanelManager::class)) {
            $this->safeRegister($this->app->make(LunarPanelManager::class));
        } else {
            try {
                $this->safeRegister($this->app->make(LunarPanelManager::class));
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Payments::extend('stripe', fn ($app) => $app->make(StripePayment::class));

        // Misafirken oturumda oluşturulan istek listesini, kullanıcı giriş
        // yaptığında hesabına taşı; çıkışta oturum listesini temizle.
        // Yalnızca vitrin (web) guard'ı için geçerlidir, personel panelini etkilemez.
        Event::listen(Login::class, function (Login $event): void {
            if ($event->guard === 'web' && $event->user) {
                WishlistManager::mergeSessionIntoUser((int) $event->user->getAuthIdentifier());
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->guard === 'web') {
                WishlistManager::clearSession();
            }
        });

        // Last-ditch attempt to register the panel after every provider
        // has booted, in case the early registration failed silently.
        if (class_exists(LunarPanelManager::class)) {
            $this->app->booted(function () {
                $this->safeRegister(app(LunarPanelManager::class));
            });
        }
    }

    private function safeRegister(LunarPanelManager $manager): void
    {
        try {
            $manager->register();
        } catch (\Throwable $e) {
            Log::warning('LunarPanelManager register failed: '.$e->getMessage());
        }
    }
}
