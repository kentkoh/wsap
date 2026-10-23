<?php
declare(strict_types=1);

/**
 * SEO helpers: hreflang alternates for the two domains, JSON-LD builders, and the list of
 * indexable marketing pages shared by the sitemap and the footer.
 */

/** Pages that exist on both domains and should be indexed. [path => lastmod file]. */
function seo_public_pages(): array
{
    $pages = ['/' => 'index.php', '/pricing' => 'pricing.php', '/members' => 'members.php',
        '/premium-links' => 'premium-links.php', '/smart-links' => 'smart-links.php', '/whatsapp-forms' => 'whatsapp-forms.php', '/whatsapp-shop' => 'whatsapp-shop.php',
        '/terms' => 'terms.php', '/privacy' => 'privacy.php'];
    require_once WSAP_ROOT . '/lib/guides.php';
    foreach (array_keys(guide_slugs()) as $slug) {
        $pages['/' . $slug] = 'lib/guides.php';
    }
    return $pages;
}

/** The same path on the other language's domain. */
function seo_alternate_urls(string $path): array
{
    $out = [];
    foreach (cfg('domains') as $host => $dc) {
        $out[$dc['lang']] = 'https://' . $host . $path;
    }
    return $out;
}

/** <link rel="alternate" hreflang> tags for the current path (only for indexable pages). */
function seo_alternates_html(string $path): string
{
    $alts = seo_alternate_urls($path);
    if (count($alts) < 2) {
        return '';
    }
    $h = '';
    foreach ($alts as $lang => $url) {
        $h .= '<link rel="alternate" hreflang="' . e($lang === 'ms' ? 'ms-MY' : 'en') . '" href="' . e($url) . '">' . "\n";
    }
    $h .= '<link rel="alternate" hreflang="x-default" href="' . e($alts['en'] ?? reset($alts)) . '">' . "\n";
    return $h;
}

function seo_org(): array
{
    return ['@type' => 'Organization', '@id' => base_url() . '/#org', 'name' => cfg('site_name'), 'url' => base_url(),
        'logo' => base_url() . '/assets/img/og.png', 'sameAs' => array_values(array_filter([
            'https://wsap.me', 'https://wsap.my',
        ]))];
}

function seo_website(): array
{
    return ['@type' => 'WebSite', '@id' => base_url() . '/#website', 'url' => base_url(), 'name' => cfg('site_name'), 'inLanguage' => lang() === 'ms' ? 'ms-MY' : 'en',
        'publisher' => ['@id' => base_url() . '/#org']];
}

/** BreadcrumbList from [[name, path], ...]. */
function seo_breadcrumbs(array $items): array
{
    $list = [];
    foreach ($items as $i => [$name, $path]) {
        $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => base_url() . $path];
    }
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
}

/** FAQPage from [[question, answer], ...]. */
function seo_faq(array $qa): array
{
    return ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($p) => ['@type' => 'Question', 'name' => $p[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $p[1]]], $qa)];
}

/** HowTo from name + [[step name, text], ...]. */
function seo_howto(string $name, array $steps, string $desc = ''): array
{
    return ['@type' => 'HowTo', 'name' => $name, 'description' => $desc, 'totalTime' => 'PT1M',
        'step' => array_map(fn($s, $i) => ['@type' => 'HowToStep', 'position' => $i + 1, 'name' => $s[0], 'text' => $s[1]], $steps, array_keys($steps))];
}

/** SoftwareApplication / product entry with a price. */
function seo_app(string $name, string $desc, string $path, string $priceAmount, string $currency, bool $free = false): array
{
    return ['@type' => 'SoftwareApplication', 'name' => $name, 'description' => $desc, 'url' => base_url() . $path,
        'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web',
        'offers' => ['@type' => 'Offer', 'price' => $free ? '0' : $priceAmount, 'priceCurrency' => $currency, 'url' => base_url() . $path]];
}

/** Numeric price for the current display currency ("39" / "9"). */
function seo_price_amount(string $plan): string
{
    $p = plan_cfg($plan);
    return (string) ($p[currency() === 'MYR' ? 'myr' : 'usd'] ?? 0);
}

/** <script type="application/ld+json"> with Organization + WebSite + the given nodes. */
function seo_jsonld_html(array $nodes): string
{
    $graph = array_merge([seo_org(), seo_website()], $nodes);
    return '<script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
