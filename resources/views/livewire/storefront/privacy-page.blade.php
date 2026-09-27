{{--
    ExportDepo "Gizlilik Politikası ve KVKK" kurumsal sayfası.

    Statik içeriklidir: 6698 sayılı Kişisel Verilerin Korunması Kanunu
    kapsamındaki aydınlatma metni bölümleri. Yayına alınmadan önce hukuki
    onay önerilir (şablon niteliğindedir).
--}}
<div>
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="anim-fade-up mb-10">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Gizlilik Politikası ve KVKK</h1>
            <p class="mt-3 text-base text-slate-500 dark:text-slate-400">Kişisel verilerinizin işlenmesine ilişkin aydınlatma metni. Son güncelleme: {{ date('m.Y') }}.</p>
        </div>

        <div class="space-y-8 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">1. Veri Sorumlusu</h2>
                <p>Bu gizlilik politikası kapsamında veri sorumlusu ExportDepo'dur ("biz"). 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, kişisel verileriniz aşağıda açıklanan kapsam ve amaçlarla işlenmektedir.</p>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">2. İşlenen Kişisel Veriler</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li>Kimlik ve iletişim verileri: ad, e-posta adresi, telefon numarası</li>
                    <li>Sipariş verileri: sipariş geçmişi, teslimat ve fatura adresleri</li>
                    <li>İşlem güvenliği verileri: IP adresi, tarayıcı bilgisi, işlem kayıtları</li>
                    <li>Pazarlama verileri: bülten aboneliğinizde belirttiğiniz e-posta adresi (yalnızca açık onayınızla)</li>
                </ul>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">3. İşleme Amaçları</h2>
                <ul class="list-disc space-y-1 pl-5">
                    <li>Üyelik hesabınızın oluşturulması ve yönetilmesi</li>
                    <li>Siparişlerinizin alınması, faturalandırılması ve teslim edilmesi</li>
                    <li>Ödeme işlemlerinin güvenli şekilde gerçekleştirilmesi</li>
                    <li>Müşteri destek taleplerinize yanıt verilmesi</li>
                    <li>Yasal yükümlülüklerin (vergi, muhasebe vb.) yerine getirilmesi</li>
                    <li>Açık onayınız halinde kampanya ve bülten iletişimi</li>
                </ul>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">4. Verilerin Aktarımı ve Saklanması</h2>
                <p>Kişisel verileriniz, hizmetlerin sunulması için zorunlu tedarikçilerimiz (ödeme kuruluşları, kargo firmaları) ile yalnızca gerekli ölçüde paylaşılır. Veriler, yasal saklama süreleri boyunca güvenli sistemlerimizde saklanır ve sürenin sonunda silinir ya da anonim hale getirilir.</p>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">5. Haklarınız</h2>
                <p>KVKK'nın 11. maddesi uyarınca; verilerinizin işlenip işlenmediğini öğrenme, bilgileriniz hakkında bilgi talep etme, düzeltilmesini veya silinmesini isteme, işlemeye itiraz etme ve zararın giderilmesini talep etme haklarına sahipsiniz. Taleplerinizi <a href="{{ url('/iletisim') }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">iletişim sayfamızdan</a> bize iletebilirsiniz.</p>
            </div>

            <div class="anim-fade-up">
                <h2 class="mb-2 text-lg font-bold text-slate-900 dark:text-white">6. Çerezler</h2>
                <p>Site deneyimini iyileştirmek için oturum ve tercih çerezleri kullanılır. Tarayıcı ayarlarınızdan çerezleri dilediğiniz zaman devre dışı bırakabilirsiniz; ancak bu durumda bazı işlevler kısıtlanabilir.</p>
            </div>
        </div>

        <div class="anim-fade-up mt-10 rounded-2xl border border-indigo-100 bg-indigo-50 p-6 text-sm text-indigo-800 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300">
            Sorularınız için <a href="{{ url('/iletisim') }}" class="font-semibold underline">iletişim sayfamızı</a> kullanabilir veya destek@exportdepo.test adresine yazabilirsiniz.
        </div>
    </section>
</div>
