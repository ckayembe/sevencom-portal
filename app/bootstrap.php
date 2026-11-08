<?php
declare(strict_types=1);
namespace App;

const ROOT = __DIR__ . '/..';

function config(): array {
    static $c;
    return $c ??= [
        // Domaine principal. En local : SEVENCOM_DOMAIN=localhost
        'domain' => getenv('SEVENCOM_DOMAIN') ?: 'sevencom.tech',
    ];
}

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/import.php';

function label(): array { static $l; return $l ??= setting('label'); }
function tools(): array { static $t; return $t ??= setting('tools'); }
function artists(): array { static $a; return $a ??= all_artists(); }
function artist(string $slug): ?array { return artists()[$slug] ?? null; }

/** URL du portail ou d'un site artiste (même schéma/port qu'actuellement). */
function url(?string $sub = null, string $path = '/'): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $port = parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_PORT);
    $host = ($sub ? "$sub." : '') . config()['domain'] . ($port ? ":$port" : '');
    return "$scheme://$host$path";
}

/** URL d'un fichier statique avec version (évite l'ancien cache du navigateur). */
function asset(string $path): string {
    $file = ROOT . $path;
    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** Détermine le sous-domaine à partir du nom d'hôte. null = portail. */
function subdomain(): ?string {
    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    $domain = config()['domain'];
    if ($host === $domain || $host === "www.$domain" || $host === '') return null;
    if (str_ends_with($host, ".$domain")) return substr($host, 0, -strlen(".$domain"));
    return null;
}

function render(string $view, array $vars = [], int $status = 200): void {
    http_response_code($status);
    extract($vars);
    ob_start();
    require ROOT . "/app/views/$view.php";
    $content = ob_get_clean();
    require ROOT . '/app/views/layout.php';
}

function run(): void {
    $path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
    $sub = subdomain();

    // www -> domaine nu
    if (str_starts_with(strtolower($_SERVER['HTTP_HOST'] ?? ''), 'www.')) {
        header('Location: ' . url(null, $path), true, 301); return;
    }

    if (preg_match('#^/media/([a-z0-9.]+)$#', $path, $m)) { serve_media($m[1]); return; }

    // L'administration est toujours sur le domaine principal
    if ($path === '/admin' || str_starts_with($path, '/admin/')) {
        if ($sub !== null) { header('Location: ' . url(null, $path), true, 302); return; }
        if (admin_route($path)) return;
        render('portal/404', ['section' => 'portal'], 404); return;
    }

    if ($sub === null) {
        $routes = [
            '/' => 'portal/home',
            '/artistes' => 'portal/artists',
            '/outils' => 'portal/tools',
            '/contact' => 'portal/contact',
        ];
        if (isset($routes[$path])) { render($routes[$path], ['section' => 'portal']); return; }
        // Site artiste aussi accessible via sevencom.tech/artistes/<slug> (utile avant l'activation du sous-domaine)
        if (preg_match('#^/artistes/([a-z0-9-]+)$#', $path, $m) && ($artist = artist($m[1]))) {
            render('artist/home', ['section' => 'artist', 'artist' => $artist]); return;
        }
        render('portal/404', ['section' => 'portal'], 404); return;
    }

    $artist = artist($sub);
    if (!$artist) { render('portal/404', ['section' => 'portal'], 404); return; }
    if ($path !== '/') { header('Location: ' . url($sub), true, 302); return; }
    render('artist/home', ['section' => 'artist', 'artist' => $artist]);
}
