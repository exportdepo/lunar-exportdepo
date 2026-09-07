<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Siparişiniz alındı</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f8fafc;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    {{-- Başlık --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 50%,#ec4899 100%);padding:32px;text-align:center;">
                            <div style="display:inline-block;width:48px;height:48px;line-height:48px;background-color:rgba(255,255,255,0.2);border-radius:12px;color:#ffffff;font-size:24px;font-weight:bold;">🌙</div>
                            <h1 style="margin:16px 0 0 0;color:#ffffff;font-size:24px;font-weight:800;">Lunar Shop</h1>
                        </td>
                    </tr>
                    {{-- Onay --}}
                    <tr>
                        <td style="padding:32px 32px 16px 32px;text-align:center;">
                            <div style="font-size:48px;line-height:1;">✓</div>
                            <h2 style="margin:12px 0 8px 0;font-size:24px;font-weight:800;color:#0f172a;">Siparişiniz alındı</h2>
                            <p style="margin:0;color:#475569;font-size:15px;line-height:24px;">Siparişiniz başarıyla oluşturuldu. En kısa sürede hazırlanıp size ulaşacaktır.</p>
                        </td>
                    </tr>

                    {{-- Referans kutusu --}}
                    <tr>
                        <td style="padding:0 32px 24px 32px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f1f5f9;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px;text-align:center;">
                                        <div style="color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:1px;font-weight:600;">Sipariş Numarası</div>
                                        <div style="color:#4f46e5;font-size:20px;font-weight:800;margin-top:4px;">{{ $order->reference }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Müşteri / Adres --}}
                    @php
                        $billing = $order->billingAddress;
                        $shipping = $order->shippingAddress ?? $billing;
                    @endphp
                    <tr>
                        <td style="padding:0 32px 24px 32px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td valign="top" width="50%" style="padding-right:8px;">
                                        <h3 style="margin:0 0 8px 0;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#64748b;font-weight:600;">Müşteri</h3>
                                        <p style="margin:0;color:#0f172a;font-size:14px;line-height:22px;">
                                            <strong>{{ $billing?->first_name }} {{ $billing?->last_name }}</strong><br>
                                            <a href="mailto:{{ $billing?->contact_email }}" style="color:#4f46e5;text-decoration:none;">{{ $billing?->contact_email }}</a><br>
                                            @if ($billing?->contact_phone)
                                                <span style="color:#475569;">{{ $billing?->contact_phone }}</span>
                                            @endif
                                        </p>
                                    </td>
                                    <td valign="top" width="50%" style="padding-left:8px;">
                                        <h3 style="margin:0 0 8px 0;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#64748b;font-weight:600;">Teslimat Adresi</h3>
                                        <p style="margin:0;color:#0f172a;font-size:14px;line-height:22px;">
                                            {{ $shipping?->line_one }}<br>
                                            {{ $shipping?->city }}{{ $shipping?->state ? ', ' . $shipping->state : '' }} {{ $shipping?->postcode }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Ürünler --}}
                    <tr>
                        <td style="padding:0 32px 16px 32px;">
                            <h3 style="margin:0 0 12px 0;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#64748b;font-weight:600;">Ürünler</h3>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse:collapse;">
                                @foreach ($order->productLines as $line)
                                    @php
                                        $product = $line->purchasable?->product;
                                        $name = $product?->translateAttribute('name') ?? 'Ürün';
                                        $lineTotal = ($line->total?->value ?? 0) / 100;
                                        $unitPrice = ($line->unitPrice?->value ?? 0) / 100;
                                    @endphp
                                    <tr>
                                        <td style="padding:12px 0;border-bottom:1px solid #e2e8f0;">
                                            <div style="color:#0f172a;font-size:14px;font-weight:600;">{{ $name }}</div>
                                            <div style="color:#64748b;font-size:12px;margin-top:2px;">{{ $line->quantity }} adet × {{ number_format($unitPrice, 2, ',', '.') }} ₺</div>
                                        </td>
                                        <td align="right" valign="top" style="padding:12px 0;border-bottom:1px solid #e2e8f0;color:#0f172a;font-size:14px;font-weight:700;white-space:nowrap;">
                                            {{ number_format($lineTotal, 2, ',', '.') }} ₺
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Toplam --}}
                    @php
                        $subTotal = ($order->sub_total?->value ?? 0) / 100;
                        $shippingTotal = ($order->shipping_total?->value ?? 0) / 100;
                        $taxTotal = ($order->tax_total?->value ?? 0) / 100;
                        $discountTotal = ($order->discount_total?->value ?? 0) / 100;
                        $total = ($order->total?->value ?? 0) / 100;
                    @endphp
                    <tr>
                        <td style="padding:0 32px 24px 32px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding:6px 0;color:#475569;font-size:14px;">Ara toplam</td>
                                    <td align="right" style="padding:6px 0;color:#0f172a;font-size:14px;">{{ number_format($subTotal, 2, ',', '.') }} ₺</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;color:#475569;font-size:14px;">Kargo</td>
                                    <td align="right" style="padding:6px 0;color:#0f172a;font-size:14px;">
                                        @if ($shippingTotal > 0)
                                            {{ number_format($shippingTotal, 2, ',', '.') }} ₺
                                        @else
                                            <span style="color:#16a34a;font-weight:600;">Ücretsiz</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($taxTotal > 0)
                                    <tr>
                                        <td style="padding:6px 0;color:#475569;font-size:14px;">Vergi</td>
                                        <td align="right" style="padding:6px 0;color:#0f172a;font-size:14px;">{{ number_format($taxTotal, 2, ',', '.') }} ₺</td>
                                    </tr>
                                @endif
                                @if ($discountTotal > 0)
                                    <tr>
                                        <td style="padding:6px 0;color:#475569;font-size:14px;">İndirim</td>
                                        <td align="right" style="padding:6px 0;color:#16a34a;font-size:14px;">-{{ number_format($discountTotal, 2, ',', '.') }} ₺</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:12px 0 0 0;border-top:1px solid #e2e8f0;color:#0f172a;font-size:16px;font-weight:700;">Toplam</td>
                                    <td align="right" style="padding:12px 0 0 0;border-top:1px solid #e2e8f0;color:#0f172a;font-size:20px;font-weight:800;">{{ number_format($total, 2, ',', '.') }} ₺</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CTA: Sipariş Takip --}}
                    <tr>
                        <td align="center" style="padding:0 32px 24px 32px;">
                            <a href="{{ url('/siparis/' . $order->reference) }}" style="display:inline-block;background-color:#4f46e5;color:#ffffff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;">Siparişinizi Takip Edin</a>
                        </td>
                    </tr>

                    {{-- Bilgi notu --}}
                    <tr>
                        <td style="padding:0 32px 32px 32px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#fef3c7;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px;color:#78350f;font-size:13px;line-height:20px;">
                                        💡 <strong>Ödeme yöntemi:</strong> Manuel ödeme — siparişiniz mağaza tarafından ayrıca teyit edilecektir.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#0f172a;padding:24px 32px;text-align:center;">
                            <p style="margin:0;color:#94a3b8;font-size:12px;line-height:20px;">
                                © {{ date('Y') }} Lunar Shop. Tüm hakları saklıdır.<br>
                                <span style="color:#64748b;">🇹🇷 Türkiye — TRY</span>
                            </p>
                        </td>
                    </tr>
            </td>
        </tr>
    </table>
</body>
</html>
