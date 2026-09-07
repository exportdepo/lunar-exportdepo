<?php

namespace App\Livewire\Storefront\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Order;

#[Layout('layouts.storefront')]
class AccountPage extends Component
{
    public function mount()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function recentOrders(): array
    {
        $user = Auth::user();
        $customer = $user?->customers->first();

        if (! $customer) {
            return [];
        }

        return Order::where('customer_id', $customer->id)
            ->orderByDesc('placed_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'reference' => $order->reference,
                'status' => $order->status,
                'status_label' => $this->translateStatus($order->status),
                'total' => $order->total->value ?? 0,
                'placed_at' => $order->placed_at?->format('d.m.Y') ?? $order->created_at?->format('d.m.Y'),
            ])
            ->all();
    }

    public function totalOrdersCount(): int
    {
        $user = Auth::user();
        $customer = $user?->customers->first();
        return $customer ? Order::where('customer_id', $customer->id)->count() : 0;
    }

    protected function translateStatus(string $status): string
    {
        return match ($status) {
            'awaiting-payment' => 'Ödeme Bekleniyor',
            'payment-offline' => 'Manuel Ödeme',
            'payment-received' => 'Ödeme Alındı',
            'dispatched' => 'Kargoya Verildi',
            default => $status,
        };
    }

    public function render()
    {
        return view('livewire.storefront.auth.account-page');
    }
}
