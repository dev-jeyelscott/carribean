<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

final class RobotsController extends Controller
{
    /**
     * Return environment-aware crawler instructions.
     */
    public function __invoke(): Response
    {
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin',
                'Disallow: /account',
                'Disallow: /cart',
                'Disallow: /checkout',
                '',
                'Sitemap: '.route('sitemap'),
            ]
            : [
                'User-agent: *',
                'Disallow: /',
            ];

        return response(
            implode(PHP_EOL, $lines).PHP_EOL,
            200,
            [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ],
        );
    }
}
