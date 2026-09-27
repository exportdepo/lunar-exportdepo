{{--
    ExportDepo "İade ve Değişim" kurumsal sayfası.

    Statik içeriklidir: iade koşulları, adım adım iade süreci ve kapsam
    dışı ürünler. Ürün detay sayfasındaki "İptal ve İade Hakkı" bölümüyle
    aynı kuralları açıklar.
--}}
<div>
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="anim-fade-up mb-10">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">İade ve Değişim</h1>
            <p class="mt-3 text-base text-slate-500 dark:text-slate-400">Memnun kalmadığınız ürünleri 14 gün içinde koşulsuz iade edebilirsiniz.</p>
        </div>

        {{-- Özet rozetleri --}}
        <div class="anim-fade-up mb-12 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="text-2xl" aria-hidden="true">🗓️</span>
                <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">14 gün</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">teslimat tarihinden itibaren</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="text-2xl" aria-hidden="true">🚚</span>
                <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">Ücretsiz kargo</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">iade sürecinde</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="text-2xl" aria-hidden="true">💳</span>
                <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">Hızlı iade</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">ürün depoya ulaşınca ödemeye yansır</p>
            </div>
        </div>

        <div class="space-y-8 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">İade Süreci</h2>
                <ol class="list-decimal space-y-2 pl-5">
                    <li><strong class="text-slate-900 dark:text-white">Talep oluşturun:</strong> Siparişinizle birlikte gelen onay e-postasındaki referans koduyla sitedeki <em>Sipariş Takibi</em> formunu ya da <a href="{{ url('/iletisim') }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">iletişim formumuzu</a> kullanarak iade talebinizi iletin.</li>
                    <li><strong class="text-slate-900 dark:text-white">Paketleyin:</strong> Ürünü, faturası ve tüm aksesuarlarıyla orijinal kutusunda hasar görmeyecek şekilde paketleyin.</li>
                    <li><strong class="text-slate-900 dark:text-white">Kargoya verin:</strong> Paylaştığımız iade kodu ile anlaşmalı kargo firmasına paketi teslim edin; iade kargo ücreti tarafımızca karşılanır.</li>
                    <li><strong class="text-slate-900 dark:text-white">Geri ödeme:</strong> Ürün depomuza ulaştıktan ve kontrol edildikten sonra ödemeniz 1-3 iş günü içinde aynı ödeme yöntemine iade edilir.</li>
                </ol>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">Değişim</h2>
                <p>Aynı ürünün farklı beden/renk varyantını istiyorsanız iade sürecinin ardından yeni siparişinizi dilediğiniz varyantla oluşturabilirsiniz; stok durumuna göre değişim taleplerini iletişim formu üzerinden de hızlandırabiliriz.</p>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">İade Koşulları</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li>Ürünün kullanılmamış, ambalajı açılmamış ve satılabilir durumda olması gerekir (deneme dışı kullanım izi olmamalıdır).</li>
                    <li>İade talebiniz teslimat tarihinden itibaren 14 gün içinde yapılmalıdır.</li>
                    <li>İade kargosunun dışında ürün eksiksiz ve hasarsız ulaşmalıdır; aksi halde iade kabul edilmeyebilir.</li>
                </ul>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">Kapsam Dışı Ürünler</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li>Kişisel bakım ve hijyen ürünleri (ambalajı açılmışsa)</li>
                    <li>Özel siparişle üretilmiş, kişiye özel baskı/kişiselleştirme yapılmış ürünler</li>
                    <li>Dijital içerikler ve hediye çekleri</li>
                </ul>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">Hasarlı veya Eksik Teslimat</h2>
                <p>Ürün kargodan hasarlı veya eksik ulaştıysa teslimatı kabul etmeden kargo görevlisine tutanak tutturun ve 24 saat içinde bize bildirin; ürünü ücretsiz olarak değiştirir veya bedelini iade ederiz.</p>
            </div>
        </div>

        <div class="anim-fade-up mt-10 flex flex-col items-start justify-between gap-4 rounded-2xl bg-linear-to-r from-indigo-600 to-purple-600 p-8 text-white sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-bold">İade talebiniz mi var?</h2>
                <p class="mt-1 text-sm text-indigo-100">Referans kodunuzla sipariş takibinden veya iletişim formundan bize ulaşın.</p>
            </div>
            <x-button variant="inverse" href="{{ url('/iletisim') }}">Bize Ulaşın</x-button>
        </div>
    </section>
</div>
