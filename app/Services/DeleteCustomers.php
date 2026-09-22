<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Müşterileri, FK kısıtlı tüm bağlantılarıyla birlikte siler.
 *
 * Paneldeki varsayılan silme akışı, sipariş/sepet/adres/pivot FK'ları
 * (`on delete no action`) nedeniyle müşteri silinmeye çalışıldığında
 * kısıt hatasıyla düşüyordu. Bu servis silme öncesi tüm bağları temizler.
 *
 * Sipariş geçmişi (lunar_orders / lunar_order_lines) korunur; yalnızca
 * sipariş üzerindeki müşteri referansı (customer_id) null'a çekilir.
 */
class DeleteCustomers
{
    /**
     * @param  iterable<int, Model>  $customers
     */
    public function handle(iterable $customers): void
    {
        DB::transaction(function () use ($customers): void {
            foreach ($customers as $customer) {
                $this->delete($customer);
            }
        });
    }

    protected function delete(Model $customer): void
    {
        $customerId = $customer->getKey();

        // Pivot bağlantıları (FK kısıtlı)
        DB::table('lunar_customer_user')
            ->where('customer_id', $customerId)
            ->delete();

        DB::table('lunar_customer_customer_group')
            ->where('customer_id', $customerId)
            ->delete();

        DB::table('lunar_customer_discount')
            ->where('customer_id', $customerId)
            ->delete();

        // Adresler (FK: lunar_addresses.customer_id, no action)
        DB::table('lunar_addresses')
            ->where('customer_id', $customerId)
            ->delete();

        // Sipariş geçmişi korunur; müşteri referansı null'a çekilir.
        DB::table('lunar_orders')
            ->where('customer_id', $customerId)
            ->update(['customer_id' => null]);

        // Sepetler silinmez (siparişe bağlı olabilir); referans null'a çekilir.
        DB::table('lunar_carts')
            ->where('customer_id', $customerId)
            ->update(['customer_id' => null]);

        // Müşteri nitelik bağları (morph alias: 'customer')
        DB::table('lunar_attributables')
            ->where('attributable_type', 'customer')
            ->where('attributable_id', $customerId)
            ->delete();

        // Müşterinin kendisi
        DB::table('lunar_customers')
            ->where('id', $customerId)
            ->delete();
    }
}
