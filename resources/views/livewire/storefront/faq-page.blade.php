{{--
    ExportDepo "Sıkça Sorulan Sorular" kurumsal sayfası.

    Statik soru-cevap listesi; her soru Alpine ile açılıp kapanan bir
    accordion'dur (aria-expanded senkron çalışır, yumuşak geçiş içerir).
--}}
<div>
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="anim-fade-up mb-10">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Sıkça Sorulan Sorular</h1>
            <p class="mt-3 text-base text-slate-500 dark:text-slate-400">Sipariş, kargo, ödeme ve iade hakkında en çok sorulanlar. Cevabını bulamadığınız sorular için <a href="{{ url('/iletisim') }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">bize yazın</a>.</p>
        </div>

        <div class="stagger-in space-y-3">
            @foreach ([
                ['Siparişimi nasıl takip edebilirim?', 'Sipariş onay e-postanızdaki referans koduyla sitedeki Sipariş Takibi formunu kullanabilirsiniz; siparişinizin durumu adım adım görünür.'],
                ['Kargom ne zaman teslim edilir?', 'Siparişleriniz ödeme onayından sonra 1-2 iş günü içinde kargoya verilir; teslimat kargo firmasına göre 1-4 iş günü sürer.'],
                ['Hangi ödeme yöntemlerini kullanabilirim?', 'Kredi/banka kartı ile güvenli ödeme yapabilirsiniz; yapılandırmaya göre kapıda ödeme seçeneği de sunulur. Ödeme sayfasındaki seçenekler geçerli yöntemleri gösterir.'],
                ['Ürünü iade etmek istiyorum, ne yapmalıyım?', 'Teslimat tarihinden itibaren 14 gün içinde, kullanılmamış ve orijinal ambalajında olmak şartıyla iade edebilirsiniz. Süreç için İade ve Değişim sayfamızdaki adımları izleyin.'],
                ['Varyant (beden/renk) değişimi yapabilir miyim?', 'Evet; stok bulunmak şartıyla farklı varyanta geçiş yapılabilir. İstek listenize ekleyerek stok yenilendiğinde haberdar olabilirsiniz.'],
                ['Fiyatlar KDV dahil mi?', 'Ürün sayfalarında ve sepette gösterilen tüm fiyatlar KDV dahildir; sepet özetinde ara toplam ve KDV ayrıca breakdown halinde listelenir.'],
                ['Üye olmadan alışveriş yapabilir miyim?', 'Evet, misafir olarak checkout akışını tamamlayabilirsiniz; sipariş takibi referans kodunuzla yapılır. Üyelilik ise adres kaydı, sipariş geçmişi ve istek listesi gibi avantajlar sunar.'],
                ['Bülten aboneliğimi nasıl iptal ederim?', 'Bülten e-postalarındaki bağlantıyla veya iletişim formumuzdan e-posta adresinizi bildirerek aboneliğinizi dilediğiniz zaman sonlandırabilirsiniz.'],
                ['Kişisel verilerim nasıl korunuyor?', 'Verileriniz 6698 sayılı KVKK kapsamında, yalnızca hizmetin sunulması için gerekli ölçüde işlenir. Ayrıntılar için Gizlilik Politikası sayfamıza göz atın.'],
                ['Kupon veya indirim kodu nasıl kullanılır?', 'Varsa indirim kodunuzu checkout adımındaki kod alanına girin; tutar, ödemeye geçmeden önce otomatik güncellenir.'],
            ] as $index => [$question, $answer])
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                     x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }">
                    <h2>
                        <button type="button"
                                @click="open = !open"
                                :aria-expanded="open"
                                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-slate-900 transition-colors hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800">
                            <span>{{ $question }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </h2>
                    <div x-show="open"
                         x-collapse
                         x-cloak
                         class="border-t border-slate-100 px-5 py-4 text-sm leading-relaxed text-slate-600 dark:border-slate-800 dark:text-slate-300">
                        {{ $answer }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
