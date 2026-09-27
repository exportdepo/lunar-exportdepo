<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Lunar\Models\Collection;
use Lunar\Models\Product;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $entries = collect([
            ['loc' => url('/'), 'lastmod' => null],
        ]);

        // Kurumsal statik sayfalar
        foreach ([
            '/hakkimizda',
            '/iletisim',
            '/gizlilik',
            '/iade-degisim',
            '/sss',
        ] as $path) {
            $entries->push([
                'loc' => url($path),
                'lastmod' => null,
            ]);
        }

        Collection::query()
            ->with('urls')
            ->orderBy('id')
            ->get()
            ->each(function (Collection $collection) use (&$entries): void {
                $slug = $collection->urls->first()?->slug;

                if (filled($slug)) {
                    $entries->push([
                        'loc' => url('/koleksiyon/'.$slug),
                        'lastmod' => $collection->updated_at?->toAtomString(),
                    ]);
                }
            });

        Product::query()
            ->where('status', 'published')
            ->with('urls')
            ->orderBy('id')
            ->get()
            ->each(function (Product $product) use (&$entries): void {
                $slug = $product->urls->first()?->slug;

                if (filled($slug)) {
                    $entries->push([
                        'loc' => url('/urun/'.$slug),
                        'lastmod' => $product->updated_at?->toAtomString(),
                    ]);
                }
            });

        return response()
            ->view('sitemap', ['entries' => $entries->all()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
