<?php
declare(strict_types=1);
namespace App;

/**
 * Pré-remplissage d'une fiche artiste à partir de liens existants
 * (chaîne YouTube, page Spotify, Facebook, Instagram, site web...).
 * On lit les informations publiques des pages (titre, description, images, dernières vidéos).
 */

const PLATFORMS = [
    'youtube.com' => 'YouTube', 'youtu.be' => 'YouTube', 'open.spotify.com' => 'Spotify', 'spotify.com' => 'Spotify',
    'instagram.com' => 'Instagram', 'facebook.com' => 'Facebook', 'fb.com' => 'Facebook', 'tiktok.com' => 'TikTok',
    'soundcloud.com' => 'SoundCloud', 'deezer.com' => 'Deezer', 'music.apple.com' => 'Apple Music',
    'audiomack.com' => 'Audiomack', 'boomplay.com' => 'Boomplay', 'x.com' => 'X', 'twitter.com' => 'X',
];

function platform_of(string $url): string {
    $host = preg_replace('/^(www\.|m\.)/', '', strtolower((string)parse_url($url, PHP_URL_HOST)));
    foreach (PLATFORMS as $domain => $name) if ($host === $domain || str_ends_with($host, ".$domain")) return $name;
    return 'Site web';
}

/** Hôte public uniquement (pas d'adresse interne). */
function is_public_url(string $url): bool {
    if (!preg_match('#^https?://#i', $url)) return false;
    if (getenv('SEVENCOM_DEV_ALLOW_LOCAL')) return true;   // tests en local uniquement
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return false;
    $ips = gethostbynamel($host) ?: [];
    if (!$ips) return false;
    foreach ($ips as $ip) if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false;
    return true;
}

function http_get(string $url, int $max = 4_000_000): ?string {
    if (!is_public_url($url) || !function_exists('curl_init')) return null;
    $ch = curl_init($url);
    $buf = '';
    curl_setopt_array($ch, [
        CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 4, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP, CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36',
        CURLOPT_HTTPHEADER => ['Accept-Language: fr-FR,fr;q=0.9,en;q=0.8'],
        CURLOPT_COOKIE => 'CONSENT=YES+cb; SOCS=CAI',   // évite la page de consentement YouTube
        CURLOPT_ENCODING => '',
        CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$buf, $max) { $buf .= $chunk; return strlen($buf) > $max ? 0 : strlen($chunk); },
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 300 && $buf !== '') ? $buf : null;
}

function meta(string $html, string $prop): string {
    $p = preg_quote($prop, '#');
    if (preg_match('#<meta[^>]+(?:property|name|itemprop)=["\']' . $p . '["\'][^>]*content=["\']([^"\']*)#i', $html, $m)
        || preg_match('#<meta[^>]+content=["\']([^"\']*)["\'][^>]*(?:property|name|itemprop)=["\']' . $p . '["\']#i', $html, $m)) {
        return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
    return '';
}

/** Télécharge une image distante dans storage/uploads. */
function download_image(string $url): ?string {
    if ($url === '') return null;
    if (str_starts_with($url, '//')) $url = "https:$url";
    $data = http_get($url, 8_000_000);
    if (!$data) return null;
    $info = @getimagesizefromstring($data);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
    if (!$ext || ($info[0] ?? 0) < 64) return null;
    $name = bin2hex(random_bytes(12)) . ".$ext";
    file_put_contents(storage_dir() . "/uploads/$name", $data);
    return "/media/$name";
}

/** Lit un lien et renvoie ce qu'on a pu en tirer. */
function scrape_link(string $url): array {
    $out = ['url' => $url, 'platform' => platform_of($url), 'name' => '', 'description' => '', 'avatar' => '', 'banner' => '', 'image' => '', 'videos' => [], 'ok' => false];

    if ($out['platform'] === 'Spotify') {
        $j = json_decode((string)http_get('https://open.spotify.com/oembed?url=' . rawurlencode($url)), true);
        if ($j) { $out['name'] = $j['title'] ?? ''; $out['image'] = $j['thumbnail_url'] ?? ''; $out['ok'] = true; }
    }

    $html = http_get($url);
    if ($html) {
        $out['ok'] = true;
        $out['name'] = $out['name'] ?: preg_replace('/\s*[-|·]\s*(YouTube|Spotify|Facebook|Instagram|TikTok|SoundCloud|Deezer).*$/iu', '', meta($html, 'og:title'));
        $out['description'] = meta($html, 'og:description') ?: meta($html, 'description');
        $out['image'] = $out['image'] ?: meta($html, 'og:image');

        if ($out['platform'] === 'YouTube') {
            $out['avatar'] = $out['image'];   // sur une chaîne, og:image = logo/avatar de la chaîne
            $out['image'] = '';
            if (preg_match('#"(?:imageBannerViewModel|banner)".{0,400}?"url":"(https://yt3\.googleusercontent\.com/[^"]+?=w\d+[^"]*)"#s', $html, $m)) {
                $out['banner'] = preg_replace('/=w\d+.*$/', '=w2120-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj', json_decode('"' . $m[1] . '"'));
            }
            if (preg_match('#(?:"externalId"|"channelId"|itemprop="(?:identifier|channelId)" content=)"?:?"?(UC[\w-]{22})#', $html, $m)
                || preg_match('#youtube\.com/channel/(UC[\w-]{22})#', $html, $m)) {
                $xml = http_get('https://www.youtube.com/feeds/videos.xml?channel_id=' . $m[1]);
                if ($xml && preg_match_all('#<yt:videoId>([\w-]{11})</yt:videoId>.*?<title>([^<]*)</title>#s', $xml, $vv, PREG_SET_ORDER)) {
                    foreach (array_slice($vv, 0, 6) as $v) $out['videos'][] = ['title' => html_entity_decode($v[2], ENT_QUOTES | ENT_XML1, 'UTF-8'), 'youtube' => $v[1]];
                }
            }
        }
    }
    return $out;
}

/**
 * Complète la fiche $a avec les liens fournis. Ne remplace que les champs vides
 * (sauf $overwrite), ajoute les réseaux et vidéos manquants. Renvoie [fiche, résumé].
 */
function import_links(array $a, array $urls): array {
    $done = []; $failed = [];
    $empty = fn($k) => empty($a[$k]) || str_contains((string)$a[$k], 'placeholder') || $a[$k] === 'À compléter';
    $photo = ''; $logo = '';
    foreach ($urls as $url) {
        $url = clean_url($url);
        if (!$url) continue;
        $s = scrape_link($url);
        $a['socials'][$s['platform'] === 'Site web' ? (parse_url($url, PHP_URL_HOST) ?: 'Site web') : $s['platform']] = $url;
        if (!$s['ok']) { $failed[] = $s['platform']; continue; }
        if ($s['name'] && $empty('name')) { $a['name'] = $s['name']; $done['nom'] = 1; }
        if ($s['description'] && empty(array_filter($a['bio'] ?? [], fn($p) => !str_contains($p, 'à compléter')))) {
            $a['bio'] = array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', $s['description']))));
            $done['bio'] = 1;
        }
        if ($s['description'] && $empty('tagline')) { $a['tagline'] = mb_strimwidth(preg_split('/\R/', $s['description'])[0], 0, 160, '…'); }
        $logo = $logo ?: $s['avatar'];
        $photo = $photo ?: ($s['banner'] ?: $s['image']);
        $known = array_column($a['videos'] ?? [], 'youtube');
        foreach ($s['videos'] as $v) if (!in_array($v['youtube'], $known, true)) { $a['videos'][] = $v; $done['vidéos'] = 1; }
    }
    if ($logo && $empty('logo') && ($p = download_image($logo))) { $a['logo'] = $p; $done['logo'] = 1; }
    if ($empty('photo') && (($photo && ($p = download_image($photo))) || (!$photo && $logo && ($p = $a['logo'] ?? null)))) {
        $a['photo'] = $p; $a['cover'] = $p; $done['photo'] = 1;
    }
    $done['réseaux'] = 1;
    $msg = 'Importé : ' . implode(', ', array_keys($done)) . '.';
    if ($failed) $msg .= ' Impossible de lire : ' . implode(', ', array_unique($failed)) . ' (page privée ou bloquée), lien ajouté quand même aux réseaux.';
    return [$a, $msg . ' Vérifiez puis enregistrez.'];
}
