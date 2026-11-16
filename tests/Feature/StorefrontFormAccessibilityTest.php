<?php

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Storefront form bileşenlerinin erişilebilirlik gereksinimlerini doğrular.
 *
 * Bileşenler ayrı ayrı render edilerek denetlenir: `/iletisim` sayfasında üç
 * `<x-input>` ve bir `<x-textarea>` aynı sınıf dizesini paylaştığı için
 * sayfa genelinde yapılan "token var mı" kontrolleri, yalnızca bir bileşen
 * geriye döndüğünde (drift) yeşil kalırdı. Bileşen bazlı denetim bu boşluğu
 * kapatır.
 */
class StorefrontFormAccessibilityTest extends TestCase
{
    use SeedsLunarDefaults;

    /**
     * WCAG 1.4.3 (Contrast) için gereken sınıf belirteçleri.
     *
     * Light placeholder: slate-500 / beyaz = 4.76:1
     * Dark placeholder:  slate-400 / slate-800 = 5.71:1
     * Dark disabled:     slate-400 / slate-900 = 6.96:1
     * Light disabled:    slate-500 / slate-50  = 4.55:1 (değişmedi, hâlâ uygun)
     *
     * @var list<string>
     */
    private const CONTRAST_TOKENS = [
        'placeholder:text-slate-500',
        'dark:placeholder:text-slate-400',
        'disabled:bg-slate-50',
        'disabled:text-slate-500',
        'dark:disabled:bg-slate-900',
        'dark:disabled:text-slate-400',
    ];

    /**
     * `sr-only` sınıfını taşıyan öğeleri seçen XPath öbeği.
     */
    private const SR_ONLY_PREDICATE = 'contains(concat(" ", normalize-space(@class), " "), " sr-only ")';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    public function test_input_and_textarea_components_share_the_same_aa_contrast_tokens(): void
    {
        foreach ($this->componentTemplates('required disabled') as $component => $template) {
            $control = $this->controlOf($this->renderComponent($template));

            foreach (self::CONTRAST_TOKENS as $token) {
                $this->assertContains(
                    $token,
                    $this->classesOf($control),
                    "{$component} bileşeni '{$token}' sınıfını taşımıyor.",
                );
            }
        }
    }

    public function test_required_fields_announce_the_requirement_inside_their_associated_label(): void
    {
        foreach ($this->componentTemplates('required') as $component => $template) {
            $xpath = $this->renderComponent($template);
            $control = $this->controlOf($xpath);

            $this->assertTrue(
                $control->hasAttribute('required'),
                "{$component} bileşeni zorunlu alanda `required` niteliğini basmıyor.",
            );

            $label = $this->firstNode($xpath, '//label[@for="'.$control->getAttribute('id').'"]');

            $this->assertNotNull($label, "{$component} bileşeninde etiket eşleşmesi bulunamadı.");

            $announced = $this->firstSrOnly($xpath, $label);

            $this->assertNotNull(
                $announced,
                "{$component} bileşeninde görsel olarak gizlenen zorunluluk metni etiketin içinde değil.",
            );
            $this->assertSame('(zorunlu)', trim($announced->textContent));

            // Görsel yıldız `aria-hidden` olduğu için ekran okuyucu metni de
            // gizliyor olmamalı; aksi halde "(zorunlu)" duyurulmaz.
            $this->assertSame(
                0,
                $this->countHiddenSrOnly($xpath, $label),
                "{$component} bileşeninde zorunluluk metni `aria-hidden` yıldızın içinde kalmış.",
            );
        }
    }

    public function test_optional_fields_do_not_announce_the_requirement(): void
    {
        foreach ($this->componentTemplates() as $component => $template) {
            $xpath = $this->renderComponent($template);

            $this->assertSame(
                0,
                $this->countNodes($xpath, '//span['.self::SR_ONLY_PREDICATE.']'),
                "{$component} bileşeni zorunlu olmayan alanda zorunluluk metni basıyor.",
            );
            $this->assertFalse(
                $this->controlOf($xpath)->hasAttribute('required'),
                "{$component} bileşeni zorunlu olmayan alanda `required` basıyor.",
            );
        }
    }

    public function test_disabled_fields_render_the_disabled_attribute(): void
    {
        foreach ($this->componentTemplates('disabled') as $component => $template) {
            $control = $this->controlOf($this->renderComponent($template));

            $this->assertTrue(
                $control->hasAttribute('disabled'),
                "{$component} bileşeni devre dışı alanda `disabled` niteliğini basmıyor.",
            );
        }
    }

    public function test_contact_form_required_fields_are_labelled_and_announced(): void
    {
        $xpath = $this->parseHtml($this->get('/iletisim')->assertOk()->getContent());

        $fields = [
            'input[@name="name"]',
            'input[@name="email"]',
            'input[@name="subject"]',
            'textarea[@name="message"]',
        ];

        foreach ($fields as $selector) {
            $control = $this->firstNode($xpath, '//'.$selector);

            $this->assertNotNull($control, "{$selector} alanı iletişim formunda bulunamadı.");

            $label = $this->firstNode($xpath, '//label[@for="'.$control->getAttribute('id').'"]');

            $this->assertNotNull($label, "{$selector} alanının etiketi bulunamadı.");

            $announced = $this->firstSrOnly($xpath, $label);

            $this->assertNotNull($announced, "{$selector} alanı zorunluluk duyurmuyor.");
            $this->assertSame('(zorunlu)', trim($announced->textContent));

            foreach (self::CONTRAST_TOKENS as $token) {
                $this->assertContains(
                    $token,
                    $this->classesOf($control),
                    "{$selector} alanı '{$token}' sınıfını taşımıyor.",
                );
            }
        }
    }

    /**
     * Bileşen başına tek tek render şablonları.
     *
     * @return array<string, string>
     */
    private function componentTemplates(string $attributes = ''): array
    {
        return [
            'input' => sprintf('<x-input name="probe" label="Probe" type="email" %s />', $attributes),
            'textarea' => sprintf('<x-textarea name="probe" label="Probe" %s />', $attributes),
        ];
    }

    private function renderComponent(string $template): DOMXPath
    {
        return $this->parseHtml(Blade::render($template));
    }

    private function parseHtml(string $html): DOMXPath
    {
        $document = new DOMDocument;

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

    private function countNodes(DOMXPath $xpath, string $query): int
    {
        $nodes = $xpath->query($query);

        return $nodes === false ? 0 : $nodes->length;
    }

    /**
     * Bağlam öğesinin altındaki ilk `sr-only` öğesi.
     */
    private function firstSrOnly(DOMXPath $xpath, DOMElement $context): ?DOMElement
    {
        $nodes = $xpath->query('.//span['.self::SR_ONLY_PREDICATE.']', $context);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    /**
     * `aria-hidden` öğelerin altında kalan `sr-only` öğesi sayısı.
     *
     * Zorunluluk metni görsel yıldızın (`aria-hidden="true"`) içinde kalmışsa
     * ekran okuyucu onu duyurmaz — bu yüzden sayım sıfır olmalıdır.
     */
    private function countHiddenSrOnly(DOMXPath $xpath, DOMElement $context): int
    {
        $hidden = $xpath->query('.//*[@aria-hidden="true"]', $context);

        if ($hidden === false) {
            return 0;
        }

        $total = 0;

        foreach ($hidden as $element) {
            $nodes = $xpath->query('.//span['.self::SR_ONLY_PREDICATE.']', $element);

            $total += $nodes === false ? 0 : $nodes->length;
        }

        return $total;
    }

    private function controlOf(DOMXPath $xpath): DOMElement
    {
        $control = $this->firstNode($xpath, '//input | //textarea');

        $this->assertNotNull($control, 'Form kontrolü bulunamadı.');

        return $control;
    }

    /**
     * @return list<string>
     */
    private function classesOf(DOMElement $element): array
    {
        return preg_split('/\s+/', trim($element->getAttribute('class')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
