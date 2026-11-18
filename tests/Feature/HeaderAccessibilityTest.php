<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Header'ın erişilebilirlik sözleşmesini doğrular.
 *
 * Kapsam: üyelik menüsünde Escape ile kapatma ve odak geri alma
 * (WCAG 2.1.1 / 2.4.3), ikon-only düğmelerde 44×44px dokunma hedefi
 * (WCAG 2.5.5) ve sekme sırasının görsel sırayla eşleşmesi (WCAG 2.4.3).
 */
class HeaderAccessibilityTest extends TestCase
{
    use SeedsLunarDefaults;

    /**
     * Tema düğmesinin etiketi Alpine ile bağlı (`:aria-label`), bu yüzden
     * statik `aria-label` üzerinden bulunamaz.
     */
    private const THEME_BUTTON = '//button[contains(@*[name()=":aria-label"], "temaya geç")]';

    protected function setUp(): void
    {
        parent::setUp();

        // Header'daki `livewire:storefront.cart-indicator` bileşeni Lunar'ın
        // varsayılan para birimi / dil / kanal kayıtlarına bağlıdır.
        $this->seedLunarDefaults();
    }

    public function test_authenticated_user_dropdown_closes_on_escape_and_restores_focus(): void
    {
        $user = User::factory()->create();

        $xpath = $this->parseHtml(
            $this->actingAs($user)->get('/')->assertOk()->getContent(),
        );

        $trigger = $this->firstNode($xpath, '//button[@aria-controls="user-dropdown"]');

        $this->assertNotNull($trigger, 'Üyelik menüsü tetikleyicisi bulunamadı.');

        $escape = $trigger->getAttribute('data-a11y-keydown.escape.window');

        $this->assertNotNull(
            $escape,
            'Giriş yapmış kullanıcının menüsünde Escape işleyicisi olmalı.',
        );
        $this->assertStringContainsString(
            'open = false',
            $escape,
            'Escape menüyü kapatmalı.',
        );
        $this->assertStringContainsString(
            'focus()',
            $escape,
            'Escape odağı menü tetikleyicisine döndürmeli.',
        );
    }

    public function test_user_dropdown_escape_does_not_steal_focus_when_closed(): void
    {
        $user = User::factory()->create();

        $xpath = $this->parseHtml(
            $this->actingAs($user)->get('/')->assertOk()->getContent(),
        );

        $trigger = $this->firstNode($xpath, '//button[@aria-controls="user-dropdown"]');

        $this->assertNotNull($trigger, 'Üyelik menüsü tetikleyicisi bulunamadı.');

        // Menü kapalıyken Escape odak bağlamadan çalışmalı; aksi halde
        // sayfanın herhangi bir yerinde Escape odakı tetikleyiciye çeker.
        $this->assertStringContainsString(
            'if (open)',
            $trigger->getAttribute('data-a11y-keydown.escape.window'),
            'Escape işleyicisi yalnızca menü açıkken odak çalmalı.',
        );
    }

    public function test_guest_dropdown_escape_closes_the_menu_and_restores_focus(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $trigger = $this->firstNode($xpath, '//button[@aria-controls="guest-auth-dropdown"]');

        $this->assertNotNull($trigger, 'Misafir menüsü tetikleyicisi bulunamadı.');

        $escape = $trigger->getAttribute('data-a11y-keydown.escape.window');

        $this->assertNotNull($escape, 'Misafir menüsünde Escape işleyicisi olmalı.');
        $this->assertStringContainsString('if (open)', $escape);
        $this->assertStringContainsString('focus()', $escape);
    }

    public function test_icon_only_header_buttons_have_44px_touch_targets(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        // 44px = 2.75rem = Tailwind `h-11 w-11`.
        foreach ([
            '//button[@id="search-trigger-button"]' => ['h-11', 'w-11', 'Arama düğmesi'],
            '//button[@id="mobile-menu-trigger"]' => ['h-11', 'w-11', 'Hamburger menü düğmesi'],
            self::THEME_BUTTON => ['h-11', 'w-11', 'Tema düğmesi'],
            '//button[@aria-controls="guest-auth-dropdown"]' => ['h-11', null, 'Misafir menü düğmesi'],
        ] as $query => [$height, $width, $label]) {
            $button = $this->firstNode($xpath, $query);

            $this->assertNotNull($button, $label.' bulunamadı.');
            $this->assertStringContainsString(
                $height,
                $button->getAttribute('class'),
                $label.' 44px yüksekliğinde olmalı ('.$height.').',
            );

            if ($width !== null) {
                $this->assertStringContainsString(
                    $width,
                    $button->getAttribute('class'),
                    $label.' 44px genişliğinde olmalı ('.$width.').',
                );
            }
        }
    }

    public function test_icon_only_header_buttons_have_hover_background(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        foreach ([
            '//button[@id="search-trigger-button"]' => 'Arama düğmesi',
            '//button[@id="mobile-menu-trigger"]' => 'Hamburger menü düğmesi',
            self::THEME_BUTTON => 'Tema düğmesi',
        ] as $query => $label) {
            $button = $this->firstNode($xpath, $query);

            $this->assertNotNull($button, $label.' bulunamadı.');
            $this->assertStringContainsString(
                'hover:bg-slate-100',
                $button->getAttribute('class'),
                $label.' fare üzerine gelindiğinde görünür bir arka plan almalı.',
            );
            $this->assertStringContainsString(
                'dark:hover:bg-slate-800',
                $button->getAttribute('class'),
                $label.' koyu temada da arka plan almalı.',
            );
        }
    }

    public function test_header_tab_order_matches_visual_order(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        // Yalnızca görünen üst çubuk: arama modalı ile mobil menü paneli de
        // `<header>` içinde yer alır ama `x-show` ile gizlidir.
        $bar = $this->firstNode($xpath, '//header//div[contains(concat(" ", normalize-space(@class), " "), " h-16 ")]');

        $this->assertNotNull($bar, 'Header üst çubuğu bulunamadı.');

        $focusable = $xpath->query(
            './/a[@href] | .//button[not(@disabled)] | .//*[@tabindex and @tabindex != "-1"]',
            $bar,
        );

        $labels = [];

        foreach ($focusable as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            // Kapalıyken `x-show` ile gizlenen yüzeyler (sepet önizleme kartı,
            // sepet çekmecesi, üyelik menüleri) statik HTML'de bulunur ama
            // sekme sırasına girmez.
            if ($this->isInsideHiddenSurface($node)) {
                continue;
            }

            $labels[] = match (true) {
                $node->getAttribute('id') !== '' => $node->getAttribute('id'),
                $node->getAttribute('aria-label') !== '' => $node->getAttribute('aria-label'),
                $node->getAttribute(':aria-label') !== '' => 'tema',
                default => trim($node->textContent),
            };
        }

        // Beklenen sıra: logo → gezinme → arama → tema → istek listesi →
        // sepet → kullanıcı menüsü → hamburger. Görsel sıra da aynıdır
        // (logo solda, gezinme ortada, aksiyonlar sağda).
        $expected = [
            'ExportDepo ana sayfa',
            'Elektronik',
            'Giyim',
            'Ev & Yaşam',
            'search-trigger-button',
            'tema',
            'İstek Listem',
            'cart-trigger-button',
            'Giriş ve üyelik seçenekleri',
            'mobile-menu-trigger',
        ];

        $this->assertSame(
            $expected,
            $labels,
            'Header sekme sırası görsel sırayla eşleşmeli.',
        );
    }

    /**
     * Düğme, `x-show` ile gizlenen bir yüzeyin içinde mi?
     */
    private function isInsideHiddenSurface(DOMElement $node): bool
    {
        $hidden = [
            'cart-drawer',
            'header-cart-popup',
            'guest-auth-dropdown',
            'user-dropdown',
        ];

        for ($parent = $node->parentNode; $parent instanceof DOMElement; $parent = $parent->parentNode) {
            if (in_array($parent->getAttribute('id'), $hidden, true)) {
                return true;
            }
        }

        return false;
    }

    private function parseHtml(string $html): DOMXPath
    {
        $document = new DOMDocument;

        // Alpine'ın `@` ile başlayan nitelikleri (`:focusin` gibi) HTML
        // ayrıştırıcı tarafından düşürülür; denetim yapılabilsin diye
        // nitelik adları geçici olarak `data-a11y-` önekiyle yeniden adlandırılır.
        $html = preg_replace('/(\s)@([a-zA-Z][\w.:-]*)=/', '$1data-a11y-$2=', $html) ?? $html;

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function firstNode(DOMXPath $xpath, string $query): ?DOMElement
    {
        $nodes = $xpath->query($query);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }
}
