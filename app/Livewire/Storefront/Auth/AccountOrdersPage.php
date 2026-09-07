<?php

namespace App\Livewire\Storefront\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Order;

#[Layout('layouts.storefront')]
class AccountOrdersPage extends Component
{
    public function mount()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function orders(): array
    {
        $user = Auth::user();
        $customer = $user?->customers->first();

        if (! $customer) {
            return [];
        }

        return Order::where('customer_id', $customer->id)
            ->orderByDesc('placed_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $order) => [
                'reference' => $order->reference,
                'status' => $order->status,
                'status_label' => $this->translateStatus($order->status),
                'status_color' => $this->statusColor($order->status),
                'total' => $order->total->value ?? 0,
                'placed_at' => $order->placed_at?->format('d.m.Y H:i') ?? $order->created_at?->format('d.m.Y H:i'),
            ])
            ->all();
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

    protected function statusColor(string $status): string
    {
        return match ($status) {
            'awaiting-payment' => '#f59e0b',
            'payment-offline' => '#0ea5e9',
            'payment-received' => '#10b981',
            'dispatched' => '#8b5cf6',
            default => '#64748b',
        };
    }

    public function render()
    {
        return view('livewire.storefront.auth.account-orders-page');
    }
}
