<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            route('home'),
            route('notices'),
            route('gallery'),
            route('contact'),
            route('privacy-policy'),
            route('legal-notice'),
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url).'</loc>'."\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
