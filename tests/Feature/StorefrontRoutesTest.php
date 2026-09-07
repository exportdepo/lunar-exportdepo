<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontRoutesTest extends TestCase
{
    public function test_storefront_pages_render_successfully(): void
    {
        foreach ([
            '/',
            '/koleksiyon/elektronik',
            '/urun/lunarbook-pro-14',
            '/sepet',
            '/checkout',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_unknown_storefront_slugs_return_not_found(): void
    {
        $this->get('/koleksiyon/bulunamayan')->assertNotFound();
        $this->get('/urun/bulunamayan')->assertNotFound();
    }

    public function test_homepage_contains_cart_indicator(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('cart-dropdown')
            ->assertSee('cart-badge');
    }
}
