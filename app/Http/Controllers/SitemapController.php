<?php

namespace App\Http\Controllers;

use App\SupportedLocales;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $publicRoutes = ['home', 'notices', 'gallery', 'contact', 'privacy-policy', 'legal-notice'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach (SupportedLocales::all() as $locale) {
            foreach ($publicRoutes as $routeName) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>' . htmlspecialchars(route(SupportedLocales::routeName($routeName, $locale))) . '</loc>' . "\n";
                $xml .= "  </url>\n";
            }
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
