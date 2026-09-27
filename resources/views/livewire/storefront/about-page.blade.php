{{--
    ExportDepo "Hakkımızda" kurumsal sayfası.

    Statik içeriklidir: marka hikayesi, misyon/vizyon kartları ve değer
    rozetleri. Sayfa başlığı/meta verisi AboutPage::render() içindeki
    layoutData() ile set edilir.
--}}
<div>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="anim-fade-up mb-10 max-w-3xl">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Hakkımızda</h1>
            <p class="mt-3 text-base text-slate-500 dark:text-slate-400">Türkiye'nin yeni nesil e-ticaret deneyimini sunmak için yola çıktık.</p>
        </div>

        {{-- Hikaye --}}
        <div class="anim-fade-up mb-12 grid grid-cols-1 gap-10 lg:grid-cols-2">
            <div class="space-y-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                <p>ExportDepo, alışverişi sade, güvenli ve keyifli hale getirmek amacıyla kuruldu. Elektronikten giyime, ev &amp; yaşama uzanan geniş ürün yelpazemizle her ihtiyaca tek adresten çözüm sunuyoruz.</p>
                <p>Müşterilerimizin zamanını önemsiyoruz: hızlı yüklenen sayfalar, kolay ürün keşfi ve sorunsuz ödeme akışıyla alışverişi bir güce değil, birkaç tıklamaya indiriyoruz.</p>
                <p>Satın aldığınız her ürün özenle paketlenir; her siparişin arkasında gerçek bir ekip, her sorunuzda gerçek bir destek bulunur.</p>
            </div>
            <div class="flex flex-col justify-center gap-4 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="text-5xl" aria-hidden="true">🌙</span>
                <p class="text-lg font-bold text-slate-900 dark:text-white">"İyi alışveriş, iyi hizmetle başlar."</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">— ExportDepo ekibi</p>
            </div>
        </div>

        {{-- Misyon / Vizyon --}}
        <div class="mb-12 grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="anim-fade-up rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400" aria-hidden="true">🎯</span>
                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Misyonumuz</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">Herkes için erişilebilir, şeffaf ve güvenilir bir online alışveriş deneyimi sunmak; doğru ürünü, doğru fiyata, en kısa sürede kapınıza ulaştırmak.</p>
            </div>
            <div class="anim-fade-up rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400" aria-hidden="true">🔭</span>
                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Vizyonumuz</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">Türkiye'nin en çok güvenilen e-ticaret platformlarından biri olmak; ürün çeşitliliği ve müşteri memnuniyetinde sektör standardını belirlemek.</p>
            </div>
        </div>

        {{-- Değerler --}}
        <h2 class="mb-6 text-2xl font-bold text-slate-900 dark:text-white">Değerlerimiz</h2>
        <div class="stagger-in mb-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="text-2xl" aria-hidden="true">🤝</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Güven</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Söz verdiğimizde tutarız; verileriniz ve ödemeleriniz güvendedir.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="text-2xl" aria-hidden="true">✨</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Kalite</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Vitrine çıkardığımız her ürünü titizlikle seçeriz.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="text-2xl" aria-hidden="true">⚡</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Hız</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Siparişiniz en kısa sürede yola çıkar, sorularınız ertelenmez.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <span class="text-2xl" aria-hidden="true">🔍</span>
                <h3 class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Şeffaflık</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Fiyatta ve iade koşullarında sürpriz yok; her şey açık yazılıdır.</p>
            </div>
        </div>

        {{-- CTA --}}
        <div class="flex flex-col items-start justify-between gap-4 rounded-2xl bg-linear-to-r from-indigo-600 to-purple-600 p-8 text-white sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-bold">Bizi yakından tanımaya hazır mısınız?</h2>
                <p class="mt-1 text-sm text-indigo-100">Koleksiyonlarımızı gezmeye başlayın.</p>
            </div>
            <x-button variant="inverse" href="{{ url('/') }}">Alışverişe Başla</x-button>
        </div>
    </section>
</div>
