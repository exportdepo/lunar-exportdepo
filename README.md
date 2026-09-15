# ExportDepo

**ExportDepo**, [Laravel](https://laravel.com) 12 ve [Lunar PHP](https://lunarphp.io) ^1.5 üzerinde çalışan, [Livewire](https://livewire.laravel.com) tabanlı tam yığın bir e-ticaret vitrinidir. Stripe ödemeleri ve Lunar yönetim panelini içerir.

![CI](https://github.com/exportdepo/lunar-exportdepo/actions/workflows/tests.yml/badge.svg)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)

> ℹ️ Repo private olduğu için CI badge'i yalnızca repo üyelerine görünür.

## Özellikler

- 🏪 **Storefront** — Livewire tam sayfa bileşenleriyle: ana sayfa, koleksiyon, ürün detayı, sepet, checkout, sipariş durumu
- 👤 **Hesap** — kayıt/giriş/şifre sıfırlama, "Hesabım" ve sipariş geçmişi sayfaları
- 💳 **Ödeme** — Stripe entegrasyonu (webhook desteğiyle); `STRIPE_*` boşsa manuel ödeme (cash-in-hand) fallback'i
- 🎛️ **Yönetim paneli** — Lunar Hub (`/lunar`) + özel Filament kaynakları: CollectionGroup, ProductType, TaxClass, Language
- 🇹🇷 **Türkiye odaklı** — TRY para birimi, Türkçe rotalar ve demo veriler

## Teknolojiler

| Katman | Teknoloji |
|---|---|
| Backend | Laravel 12, PHP 8.4 |
| E-ticaret çekirdeği | Lunar PHP ^1.5 (ürün, varyant, fiyat, sipariş, sepet) |
| Frontend | Livewire 3, Tailwind CSS 4 (Vite), Alpine.js |
| Yönetim paneli | Filament 4 (Lunar Hub) |
| Ödeme | Stripe PHP SDK ^21.3 |
| Veritabanı | SQLite (geliştirme/test) — MySQL/PostgreSQL uyumlu |
| Test | PHPUnit 11 — 46 test / 165 assertion |

## Gereksinimler

- PHP **8.4+** (`pdo_sqlite`, `mbstring`, `intl`, `bcmath`, `zip` uzantılarıyla)
- Composer
- Node.js 22+

## Kurulum

```bash
# 1. Bağımlılıklar
composer install
npm ci

# 2. Ortam
cp .env.example .env
php artisan key:generate

# 3. Veritabanı (varsayılan: SQLite)
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=DemoDataSeeder

# 4. Frontend
npm run build        # veya geliştirme için: npm run dev

# 5. Çalıştır
php artisan serve
# → http://127.0.0.1:8000
```

Demo seeder; elektronik ve giyim koleksiyonlarında 15+ Türkçe ürün, yönetim paneli için admin staff hesabı oluşturur. Admin şifresi `.env` içindeki `EXPORTDEPO_ADMIN_PASSWORD` ile belirlenir.

## Ortam Değişkenleri

| Değişken | Açıklama |
|---|---|
| `PAYMENTS_TYPE` | `cash-in-hand` (varsayılan, manuel ödeme) veya `stripe` |
| `STRIPE_SECRET` | Stripe gizli anahtarı — boşsa manuel ödeme fallback'i devreye girer |
| `STRIPE_WEBHOOK_SECRET` | Webhook imza doğrulama anahtarı |
| `EXPORTDEPO_ADMIN_PASSWORD` | Seeder'ın oluşturduğu admin staff şifresi |

Stripe webhook endpoint'i: `POST /stripe/webhook` (CSRF koruması muaf).

## Storefront Rotaları

| Rota | Açıklama |
|---|---|
| `/` | Ana sayfa |
| `/koleksiyon/{slug}` | Koleksiyon sayfası (arama/filtre dahil) |
| `/urun/{slug}` | Ürün detayı |
| `/sepet` | Sepet |
| `/checkout` | Checkout |
| `/siparis/{reference}` | Sipariş durumu takibi |
| `/giris`, `/kayit`, `/sifremi-unuttum`, `/sifre-sifirlama/{token}` | Kimlik doğrulama |
| `/hesabim`, `/hesabim/siparislerim` | Hesap (giriş gerektirir) |
| `/lunar` | Yönetim paneli (Lunar Hub / Filament) |

## Testler ve CI

```bash
php artisan test
```

Testler bellek içi SQLite (`:memory:`) üzerinde, `RefreshDatabase` ile koşar. Her push'ta GitHub Actions (`.github/workflows/tests.yml`) tüm test paketini PHP 8.4 ile otomatik çalıştırır.

## Proje Yapısı

```
app/
├── Filament/Resources/     # Özel Lunar admin kaynakları
├── Http/Controllers/       # StripeWebhookController
├── Livewire/Storefront/    # Vitrin sayfa bileşenleri
├── Services/               # Silme servisleri (ProductType, TaxClass, CollectionGroup)
└── Support/                # StorefrontPricing gibi yardımcılar
```

> 📌 Dahili notlar için: proje devir-teslim özetine `handoff.md`, veritabanı denetim raporuna `DB_AUDIT.md` dosyalarından ulaşabilirsiniz.

## Lisans

[MIT](https://opensource.org/licenses/MIT)
