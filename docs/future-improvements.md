# Gelecek İyileştirmeler / Değerlendirme Listesi

Aşağıdaki özellikler lunarphp/livewire-starter-kit karşılaştırması sonucu saptanmıştır.
Şu an **zorunlu değildir**, proje mevcut halinde çalışmaya devam eder.

Bu dosya yalnızca ileriye dönük bir referans / backlog kaydıdır. Kod içinde herhangi bir değişiklik gerektirmez.

## Kimlik Doğrulama

| # | Özellik | Açıklama | Öncelik | Zorunluluk |
|---|---------|----------|---------|------------|
| 1 | E-posta doğrulama | `MustVerifyEmail`, verification rotaları ve e-posta gönderimi | Orta | Opsiyonel |
| 2 | 2FA (iki faktörlü giriş) | Girişe ek kod doğrulaması | Düşük | Opsiyonel |
| 3 | Sanctum / API token auth | Mobil veya dış erişim için API kimlik doğrulama | Düşük | Opsiyonel |

## Altyapı & DevOps

| # | Özellik | Açıklama | Öncelik | Zorunluluk |
|---|---------|----------|---------|------------|
| 4 | Docker | `docker-compose.yml`, `Dockerfile` | Düşük | Opsiyonel |
| 5 | Redis | Önbellek ve kuyruk altyapısı | Düşük | Opsiyonel |
| 6 | Meilisearch | Ürün araması için arama motoru | Düşük | Opsiyonel |

## Frontend & Bileşenler

| # | Özellik | Açıklama | Öncelik | Zorunluluk |
|---|---------|----------|---------|------------|
| 7 | Livewire Volt | Fonksiyonel / SFC bileşen geçişi | Düşük | Opsiyonel |
| 8 | Alpine.js | İnteraktif arayüz katmanı | Düşük | Opsiyonel |

## Diğer

| # | Özellik | Açıklama | Öncelik | Zorunluluk |
|---|---------|----------|---------|------------|
| 9 | Ekipler / Tenant | Multi-tenancy veya takım yapısı | Çok Düşük | Opsiyonel |

---

### Notlar

- Bu liste GitHub Issues olarak da açılabilir (her bir madde ayrı bir issue olarak).
- Kod içine `// TODO:` yorumları eklenebilir, ancak merkezi bir dosya daha kolay takip edilir.
- Laravel Boost kurallarına uygun olarak, bu salt okunur bir belgeleme dosyasıdır ve projeyi çalıştırmak için gerekli değildir.