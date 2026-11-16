<?php

namespace Tests\Feature;

use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Storefront form bileşenlerinin erişilebilirlik gereksinimlerini doğrular.
 */
class StorefrontFormAccessibilityTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    public function test_required_contact_form_fields_announce_the_requirement_to_screen_readers(): void
    {
        $response = $this->get('/iletisim');

        $response->assertOk()
            ->assertSee('<span class="text-rose-500" aria-hidden="true">*</span>', false)
            ->assertSee('<span class="sr-only">(zorunlu)</span>', false);

        $this->assertSame(
            4,
            substr_count($response->getContent(), '<span class="sr-only">(zorunlu)</span>'),
        );
    }

    public function test_contact_form_placeholders_use_aa_contrast_colors(): void
    {
        $response = $this->get('/iletisim');

        $response->assertOk()
            ->assertSee('placeholder:text-slate-500 focus:outline-none', false)
            ->assertSee('dark:placeholder:text-slate-400 dark:disabled:bg-slate-900', false);

        $this->assertSame(
            4,
            substr_count($response->getContent(), 'placeholder:text-slate-500 focus:outline-none'),
        );
    }

    public function test_contact_form_disabled_text_uses_an_aa_dark_mode_color(): void
    {
        $response = $this->get('/iletisim');

        $response->assertOk()
            ->assertSee('dark:disabled:bg-slate-900 dark:disabled:text-slate-400', false);

        $this->assertSame(
            4,
            substr_count($response->getContent(), 'dark:disabled:bg-slate-900 dark:disabled:text-slate-400'),
        );
    }
}
