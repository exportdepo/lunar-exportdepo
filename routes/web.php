<?php

use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\CollectionPage;
use App\Livewire\Storefront\ProductPage;
use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\CheckoutPage;
use App\Livewire\Storefront\OrderStatusPage;
use App\Livewire\Storefront\Auth\LoginPage;
use App\Livewire\Storefront\Auth\RegisterPage;
use App\Livewire\Storefront\Auth\ForgotPasswordPage;
use App\Livewire\Storefront\Auth\ResetPasswordPage;
use App\Livewire\Storefront\Auth\AccountPage;
use App\Livewire\Storefront\Auth\AccountOrdersPage;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Storefront ana sayfa
Route::get('/', HomePage::class)->name('home');
Route::get('/koleksiyon/{slug}', CollectionPage::class)->name('collection.show');
Route::get('/urun/{slug}', ProductPage::class)->name('product.show');
Route::get('/sepet', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/siparis/{reference}', OrderStatusPage::class)
    ->name('order.show')
    ->where('reference', '[A-Za-z0-9]+');

// Misafir auth rotaları (Livewire bileşenleri kendi içinde auth kontrolü yapar)
Route::get('/giris', LoginPage::class)->name('login');
Route::get('/kayit', RegisterPage::class)->name('register');
Route::get('/sifremi-unuttum', ForgotPasswordPage::class)->name('password.request');
Route::get('/sifre-sifirlama/{token}', ResetPasswordPage::class)->name('password.reset');

// Giriş gerektiren rotalar
Route::middleware('auth')->group(function () {
    Route::get('/hesabim', AccountPage::class)->name('account');
    Route::get('/hesabim/siparislerim', AccountOrdersPage::class)->name('account.orders');
    Route::post('/cikis', function (Request $request) {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

Route::post('/stripe/webhook', StripeWebhookController::class)
	->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
	->name('stripe.webhook');
