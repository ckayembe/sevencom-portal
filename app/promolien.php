<?php
declare(strict_types=1);
namespace App;

/**
 * Liaison avec Udhibiti PromoLien (promolien.udhibiti.com), le service de liens d'écoute du label.
 *
 * 1. sevencom.tech/promolien.json publie la liste des artistes (lue chaque jour par PromoLien).
 * 2. Les sorties créées par PromoLien sont lues sur promolien.udhibiti.com/api/labels/sevencom.json,
 *    gardées 10 minutes en cache, et affichées sur le site de chaque artiste.
 * Aucun mot de passe ni clé n'est échangé. Jamais d'e-mail ni de téléphone dans la liste publiée.
 */

const PROMOLIEN_URL = 'https://promolien.udhibiti.com';
const PROMOLIEN_FEED = PROMOLIEN_URL . '/api/labels/sevencom.json';
const PROMOLIEN_TTL = 600;          // 10 minutes
const PROMOLIEN_RETRY = 120;        // après une panne, nouvel essai au plus toutes les 2 minutes

/** Clés de profils acceptées par PromoLien => domaines correspondants. */
const PROMOLIEN_PROFILES = [
    'spotify' => ['open.spotify.com'], 'apple' => ['music.apple.com'], 'deezer' => ['deezer.com', 'deezer.page.link'],
    'youtube' => ['youtube.com', 'youtu.be', 'music.youtube.com'], 'boomplay' => ['boomplay.com'], 'audiomack' => ['audiomack.com'],
    'soundcloud' => ['soundcloud.com'], 'instagram' => ['instagram.com'], 'tiktok' => ['tiktok.com'], 'facebook' => ['facebook.com', 'fb.com'],
];

/** Retire adresses e-mail et numéros de téléphone d'un texte destiné à une page publique. */
function strip_contacts(string $s): string {
    $s = preg_replace('/[\w.+-]+@[\w-]+(\.[\w-]+)+/u', '', $s);
    // Numéros de téléphone : au moins 9 chiffres (« 2010 - 2020 » n'en est pas un)
    $s = preg_replace_callback('/(?<![\w\/])\+?\d[\d .\-()]{7,}\d(?![\w\/])/u', fn($m) => preg_match_all('/\d/', $m[0]) >= 9 ? '' : $m[0], $s);
    // Libellés restés seuls (« Contact : », « Booking - », « Tél. : »…)
    $s = preg_replace('/\b(contacts?|booking|e-?mail|mail|t[ée]l(?:[ée]phone)?\.?|whatsapp|infos?)\s*[:\-–]\s*(?=$|[|·,;—–])/imu', '', $s);
    $s = preg_replace('/[ \t]*([—–|·,;-])(?:[ \t]*[—–|·,;-])+/u', ' $1', $s);   // séparateurs doublés
    $s = preg_replace('/[ \t]{2,}/', ' ', $s);
    return trim(preg_replace('/^[\s,;:|·—–-]+|[\s,;:|·—–-]+$/u', '', $s));
}

/** Version publique d'une fiche artiste : jamais de coordonnées dans la présentation. */
function public_artist(array $a): array {
    $a['tagline'] = strip_contacts((string)($a['tagline'] ?? ''));
    $a['bio'] = array_values(array_filter(array_map(fn($p) => strip_contacts((string)$p), $a['bio'] ?? []), fn($p) => $p !== ''));
    if ($a['tagline'] === '' && $a['bio']) $a['tagline'] = mb_strimwidth($a['bio'][0], 0, 160, '…');
    return $a;
}

/* ---------- 1. Liste publiée pour PromoLien ---------- */

function promolien_profile_key(string $url): ?string {
    $host = preg_replace('/^(www\.|m\.)/', '', strtolower((string)parse_url($url, PHP_URL_HOST)));
    $path = trim((string)parse_url($url, PHP_URL_PATH), '/');
    if ($path === '' || !str_starts_with($url, 'https://')) return null;   // page d'accueil d'une plateforme : inutile
    foreach (PROMOLIEN_PROFILES as $k => $domains)
        foreach ($domains as $d) if ($host === $d || str_ends_with($host, ".$d")) return $k;
    return null;
}

function promolien_list(): array {
    $out = [];
    foreach (array_slice(artists(), 0, 200) as $a) {
        $a = public_artist($a);
        $item = ['id' => substr($a['slug'], 0, 60), 'name' => mb_substr($a['name'] ?? $a['slug'], 0, 80)];
        $slug = substr(slugify($a['name'] ?? ''), 0, 40);
        if (strlen($slug) >= 2) $item['slug'] = rtrim($slug, '-');
        if (($lead = $a['tagline']) !== '') $item['lead'] = mb_strimwidth($lead, 0, 140, '…');
        $photo = $a['photo'] ?? '';
        if (preg_match('#^/(media|assets)/[\w/.-]+\.(jpe?g|png|webp)$#i', $photo)) $photo = 'https://' . config()['domain'] . $photo;
        if (preg_match('#^https://\S+\.(jpe?g|png|webp)(\?\S*)?$#i', $photo) && strlen($photo) <= 500) $item['photo'] = $photo;
        $profiles = [];
        foreach (($a['socials'] ?? []) as $url) {
            if (!is_string($url) || str_contains($url, '@') && !preg_match('#/@[\w.-]+#', $url)) continue;
            if (($k = promolien_profile_key($url)) && !isset($profiles[$k])) $profiles[$k] = $url;
        }
        if ($profiles) $item['profiles'] = $profiles;
        $out[] = $item;
    }
    return ['label' => label()['name'] ?? 'Seven.com', 'artists' => $out];
}

function serve_promolien_list(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=300');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(promolien_list(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

/* ---------- 2. Sorties créées par PromoLien ---------- */

/**
 * Données du label chez PromoLien, indexées par id d'artiste. Tableau vide si le label n'est pas
 * (encore) relié ou si PromoLien n'a jamais répondu. Cache fichier hors du site : 10 minutes.
 */
function promolien_artists(): array {
    static $memo;
    if ($memo !== null) return $memo;
    $file = storage_dir() . '/promolien-cache.json';
    $cache = is_file($file) ? (json_decode((string)file_get_contents($file), true) ?: []) : [];
    $age = time() - (int)($cache['fetched_at'] ?? 0);
    $wait = ($cache['ok'] ?? true) ? PROMOLIEN_TTL : PROMOLIEN_RETRY;
    if (!$cache || $age >= $wait) {
        [$code, $body] = promolien_fetch(PROMOLIEN_FEED);
        $data = $code === 200 ? json_decode((string)$body, true) : null;
        if (is_array($data) && isset($data['artists']) && is_array($data['artists'])) {
            $cache = ['fetched_at' => time(), 'ok' => true, 'data' => $data];
        } elseif ($code === 404 || $code === 410) {
            // Label pas encore relié, en pause ou bloqué : on n'affiche rien de PromoLien.
            $cache = ['fetched_at' => time(), 'ok' => true, 'data' => null];
        } else {
            // PromoLien ne répond pas : on garde la dernière bonne réponse.
            $cache = ['fetched_at' => time(), 'ok' => false, 'data' => $cache['data'] ?? null];
        }
        $tmp = $file . '.' . bin2hex(random_bytes(4));
        if (@file_put_contents($tmp, json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) @rename($tmp, $file);
    }
    $memo = [];
    foreach (($cache['data']['artists'] ?? []) as $a) if (is_array($a) && isset($a['id'])) $memo[(string)$a['id']] = $a;
    return $memo;
}

/** [code HTTP, corps] — 5 secondes au plus, pas de redirection hors de PromoLien. */
function promolien_fetch(string $url): array {
    if (getenv('SEVENCOM_PROMOLIEN_FILE')) {   // tests en local : réponse lue dans un fichier
        $f = getenv('SEVENCOM_PROMOLIEN_FILE');
        return is_file($f) ? [200, file_get_contents($f)] : [404, '{"error":"Label inconnu."}'];
    }
    if (!function_exists('curl_init')) return [0, null];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 5, CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_USERAGENT => 'Seven.com (sevencom.tech)',
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$code, is_string($body) && strlen($body) < 5_000_000 ? $body : null];
}

/** Sorties PromoLien d'un artiste (ordre de PromoLien, 20 au plus), seulement les champs sûrs. */
function promolien_releases(string $artistId): array {
    $p = promolien_artists()[$artistId] ?? null;
    if (!$p) return [];
    $https = fn($u) => is_string($u) && str_starts_with($u, 'https://') ? $u : null;
    $out = [];
    foreach (array_slice($p['releases'] ?? [], 0, 20) as $r) {
        if (!is_array($r) || empty($r['title']) || !$https($r['page'] ?? null)) continue;
        $platforms = [];
        foreach (($r['platforms'] ?? []) as $pl) if (is_array($pl) && $https($pl['url'] ?? null) && !empty($pl['name'])) $platforms[] = ['name' => (string)$pl['name'], 'url' => $pl['url']];
        $out[] = [
            'title' => (string)$r['title'], 'artist' => (string)($r['artist'] ?? ''),
            'kind' => ['single' => 'Single', 'ep' => 'EP', 'album' => 'Album'][$r['kind'] ?? ''] ?? '',
            'date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($r['release_date'] ?? '')) ? $r['release_date'] : '',
            'upcoming' => !empty($r['upcoming']),
            'page' => $r['page'], 'cover' => $https($r['cover_small'] ?? null) ?? $https($r['cover'] ?? null),
            'platforms' => $platforms,
        ];
    }
    return $out;
}

function promolien_artist_page(string $artistId): ?string {
    $u = promolien_artists()[$artistId]['page'] ?? null;
    return is_string($u) && str_starts_with($u, 'https://') ? $u : null;
}
