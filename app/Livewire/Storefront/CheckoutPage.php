<?php

namespace App\Livewire\Storefront;

use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\OrderConfirmationService;
use App\Services\OrderStockService;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Country;
use Lunar\Models\Order;
use Lunar\Models\TaxClass;

#[Layout('layouts.storefront')]
class CheckoutPage extends Component
{
    public array $summary = [];

    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $phone = '';
    public string $lineOne = '';
    public string $city = '';
    public string $state = '';
    public string $postcode = '';
    public string $paymentMethod = 'offline';
    public ?string $orderReference = null;
    public ?int $countryId = null;
    public bool $stripeEnabled = false;

    public function mount(): void
    {
        $this->countryId = Country::where('iso2', 'TR')->value('id');
        $this->stripeEnabled = filled(config('lunar.payments.types.stripe.secret'))
            && filled(config('services.stripe.webhook_secret'));
        $this->refreshSummary();
    }

    public function placeOrder()
    {
        $this->validate([
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'lineOne' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['required', 'string', 'max:20'],
            'paymentMethod' => ['required', 'in:offline,stripe'],
        ]);

        $cart = CartSession::current();
        if (! $cart || $cart->lines->isEmpty()) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'Sipariş oluşturmak için sepetinizde ürün olmalı.',
            ]);
        }

        if (! $this->countryId) {
            throw ValidationException::withMessages([
                'lineOne' => 'Teslimat ülkesi yapılandırılmamış.',
            ]);
        }

        if ($this->paymentMethod === 'stripe' && ! $this->stripeEnabled) {
            throw ValidationException::withMessages([
                'paymentMethod' => 'Stripe ödeme şu anda yapılandırılmamış.',
            ]);
        }

        $address = [
            'country_id' => $this->countryId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'contact_email' => $this->email,
            'contact_phone' => $this->phone,
            'line_one' => $this->lineOne,
            'city' => $this->city,
            'state' => $this->state,
            'postcode' => $this->postcode,
        ];

        $cart->addAddress($address, 'shipping');
        $cart->addAddress($address, 'billing');
        $this->applyFreeShipping($cart);
        $cart->calculate(force: true);

        $paymentDriver = $this->paymentMethod === 'stripe'
            ? 'stripe'
            : config('lunar.payments.default', 'cash-in-hand');

        $response = Payments::driver($paymentDriver)
            ->cart($cart)
            ->withData(['meta' => ['payment_method' => $this->paymentMethod]])
            ->authorize();

        if (! $response?->success) {
            throw ValidationException::withMessages([
                'paymentMethod' => $response?->message ?: 'Ödeme başlatılamadı. Lütfen tekrar deneyin.',
            ]);
        }

        if ($this->paymentMethod === 'stripe') {
            return redirect()->away($response->message);
        }

        $order = Order::findOrFail($response->orderId);

        $this->linkOrderToUser($order);
        app(OrderStockService::class)->decrementFor($order);

        $this->orderReference = $order->reference;
        $this->summary = [];
        CartSession::forget();
        $this->dispatch('cart-updated');

        app(OrderConfirmationService::class)->send($order);
    }

    protected function linkOrderToUser(Order $order): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $customer = $user->customers->first();
        if (! $customer) {
            return;
        }

        $order->update(['customer_id' => $customer->id]);
    }

    protected function refreshSummary(): void
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            $this->summary = [];
            return;
        }

        $this->summary = [
            'count' => $cart->lines->sum('quantity'),
            'total' => $cart->total?->value ?? 0,
        ];
    }

    protected function applyFreeShipping($cart): void
    {
        $shippingOption = new ShippingOption(
            name: 'Ücretsiz kargo',
            description: 'Türkiye içi standart teslimat',
            identifier: 'free-shipping',
            price: new Price(0, $cart->currency),
            taxClass: TaxClass::where('default', true)->firstOrFail(),
        );

        ShippingManifest::addOption($shippingOption);
        $cart->setShippingOption($shippingOption);
    }

    public function render()
    {
        return view('livewire.storefront.checkout-page');
    }
}
