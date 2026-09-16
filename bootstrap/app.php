<?php

use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Livewire page component'lerinde mount'tan redirect dönmek
        // MissingLayoutException ürettiği için misafir sayfa yönlendirmesi
        // klasik middleware'a taşındı (bkz. LoginPage/RegisterPage).
        $middleware->alias([
            'guest' => RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Çıkış isteğinde CSRF token bayatsa (oturum bitmiş / sekme eski)
        // 419 yerine güvenli şekilde çıkarıp ana sayfaya yönlendir.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->is('cikis') || $request->routeIs('logout')) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/');
            }
        });
    })->create();
