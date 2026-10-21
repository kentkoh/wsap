<?php
declare(strict_types=1);

/** Country calling codes for the phone picker. [iso => [dial, name]] — Malaysia first, then the region, then A–Z. */
function country_codes(): array
{
    return [
        'MY' => ['60', 'Malaysia'],
        'SG' => ['65', 'Singapore'],
        'ID' => ['62', 'Indonesia'],
        'BN' => ['673', 'Brunei'],
        'TH' => ['66', 'Thailand'],
        'PH' => ['63', 'Philippines'],
        'VN' => ['84', 'Vietnam'],
        'IN' => ['91', 'India'],
        'AE' => ['971', 'UAE'],
        'AU' => ['61', 'Australia'],
        'BD' => ['880', 'Bangladesh'],
        'BR' => ['55', 'Brazil'],
        'CA' => ['1', 'Canada'],
        'CN' => ['86', 'China'],
        'CO' => ['57', 'Colombia'],
        'DE' => ['49', 'Germany'],
        'EG' => ['20', 'Egypt'],
        'ES' => ['34', 'Spain'],
        'FR' => ['33', 'France'],
        'GB' => ['44', 'United Kingdom'],
        'HK' => ['852', 'Hong Kong'],
        'IT' => ['39', 'Italy'],
        'JP' => ['81', 'Japan'],
        'KE' => ['254', 'Kenya'],
        'KH' => ['855', 'Cambodia'],
        'KR' => ['82', 'South Korea'],
        'LK' => ['94', 'Sri Lanka'],
        'MX' => ['52', 'Mexico'],
        'MM' => ['95', 'Myanmar'],
        'NG' => ['234', 'Nigeria'],
        'NL' => ['31', 'Netherlands'],
        'NP' => ['977', 'Nepal'],
        'NZ' => ['64', 'New Zealand'],
        'PK' => ['92', 'Pakistan'],
        'PT' => ['351', 'Portugal'],
        'QA' => ['974', 'Qatar'],
        'SA' => ['966', 'Saudi Arabia'],
        'TR' => ['90', 'Turkey'],
        'TW' => ['886', 'Taiwan'],
        'US' => ['1', 'United States'],
        'ZA' => ['27', 'South Africa'],
    ];
}

function default_country(): string
{
    return current_domain() === 'wsap.my' || lang() === 'ms' ? 'MY' : (request_country_or('MY'));
}

function request_country_or(string $fallback): string
{
    $c = strtoupper(trim((string) ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')));
    return isset(country_codes()[$c]) ? $c : $fallback;
}

/** <option> list for the phone picker. */
function country_options(string $selected = 'MY'): string
{
    $out = '';
    foreach (country_codes() as $iso => [$dial, $name]) {
        $sel = $iso === $selected ? ' selected' : '';
        $out .= '<option value="' . $dial . '"' . $sel . '>' . $iso . ' +' . $dial . '</option>';
    }
    return $out;
}

/** Split a stored E.164 number into [dial, national] using the known dial codes (longest match). */
function split_phone(string $digits): array
{
    $dials = array_map(fn($c) => $c[0], country_codes());
    usort($dials, fn($a, $b) => strlen($b) <=> strlen($a));
    foreach ($dials as $d) {
        if (str_starts_with($digits, $d)) {
            return [$d, substr($digits, strlen($d))];
        }
    }
    return ['', $digits];
}
