<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Giriş yapmış kullanıcıları misafir rotalarından (/giris, /kayit, ...) uzaklaştırır.
 *
 * Livewire v3 page component'lerinde mount() içinden redirect dönmek
 * MissingLayoutException (500) ürettiği için bu kontrol middleware'a taşındı.
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect('/hesabim');
            }
        }

        return $next($request);
    }
}
