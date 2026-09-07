<?php

use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\CollectionPage;
use App\Livewire\Storefront\ProductPage;
use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\CheckoutPage;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Storefront ana sayfa
Route::get('/', HomePage::class)->name('home');
Route::get('/koleksiyon/{slug}', CollectionPage::class)->name('collection.show');
Route::get('/urun/{slug}', ProductPage::class)->name('product.show');
Route::get('/sepet', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::post('/stripe/webhook', StripeWebhookController::class)
	->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
	->name('stripe.webhook');
