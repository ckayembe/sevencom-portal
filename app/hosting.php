<?php
declare(strict_types=1);
namespace App;

/**
 * Activation automatique de l'adresse <artiste>.sevencom.tech sur Hostinger.
 *
 * - À la création d'un artiste, le sous-domaine est déclaré sur Hostinger (même dossier que le site).
 * - Le certificat SSL est demandé dès que l'adresse répond au DNS (entrée « * » chez Infomaniak),
 *   avec de nouveaux essais automatiques à chaque visite de l'admin.
 * La clé d'API Hostinger est saisie dans l'admin et rangée HORS du site (storage/hostinger.json) :
 * elle n'est jamais dans le code ni réaffichée.
 */

const HOSTINGER_API = 'https://developers.hostinger.com';
const SSL_RETRY_SECONDS = 600;     // nouvel essai SSL au plus toutes les 10 minutes
const SSL_MAX_TRIES = 12;

function hosting_config(): array {
    $f = storage_dir() . '/hostinger.json';
    $c = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    return $c + ['token' => '', 'username' => '', 'ip' => ''];
}

function save_hosting_config(array $c): void {
    $f = storage_dir() . '/hostinger.json';
    file_put_contents($f, json_encode($c, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    @chmod($f, 0600);
}

function hosting_enabled(): bool { $c = hosting_config(); return $c['token'] !== '' && $c['username'] !== ''; }

/** Appel à l'API Hostinger : [code HTTP, réponse décodée]. */
function hostinger_call(string $method, string $path, ?array $body = null, ?string $token = null): array {
    $c = hosting_config();
    $token ??= $c['token'];
    if (!function_exists('curl_init') || $token === '') return [0, null];
    $base = getenv('SEVENCOM_HOSTINGER_API') ?: HOSTINGER_API;   // autre adresse : tests en local uniquement
    $ch = curl_init($base . $path);
    $headers = ['Authorization: Bearer ' . $token, 'Accept: application/json'];
    if ($body !== null) $headers[] = 'Content-Type: application/json';
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_PROTOCOLS => getenv('SEVENCOM_HOSTINGER_API') ? CURLPROTO_HTTP | CURLPROTO_HTTPS : CURLPROTO_HTTPS,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body !== null ? json_encode($body) : null,
    ]);
    $res = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$code, is_string($res) ? json_decode($res, true) : null];
}

function hosting_path(string $suffix): string {
    $c = hosting_config();
    return '/api/hosting/v1/accounts/' . rawurlencode($c['username']) . '/websites/' . rawurlencode(config()['domain']) . $suffix;
}

/** Sous-domaines déjà déclarés sur Hostinger (préfixes), ou null si l'API ne répond pas. */
function hosting_subdomains(?string $token = null): ?array {
    [$code, $data] = hostinger_call('GET', hosting_path('/subdomains'), null, $token);
    if ($code !== 200 || !is_array($data)) return null;
    $list = $data['data'] ?? $data;
    $out = [];
    foreach ((array)$list as $s) if (is_array($s)) $out[] = (string)($s['subdomain'] ?? strtok((string)($s['domain'] ?? ''), '.'));
    return $out;
}

/* ---------- État de l'adresse de chaque artiste ---------- */

function site_states(): array { return setting('site_states'); }

function save_site_state(string $slug, array $st): void {
    $all = site_states();
    $all[$slug] = $st + ($all[$slug] ?? []) + ['tries' => 0];
    save_setting('site_states', $all);
}

function host_resolves(string $host): bool {
    $ip = hosting_config()['ip'] ?: (gethostbyname(config()['domain']) ?: '');
    $ips = @gethostbynamel($host) ?: [];
    return $ip !== '' && in_array($ip, $ips, true);
}

/** Déclare le sous-domaine puis demande le SSL si le DNS est prêt. Renvoie l'état. */
function provision_artist_site(string $slug): array {
    if (!hosting_enabled()) return ['step' => 'manual', 'message' => "Activation automatique non configurée (clé d'API Hostinger)."];
    $st = site_states()[$slug] ?? ['step' => 'new', 'tries' => 0];

    if (in_array($st['step'], ['new', 'error_subdomain'], true)) {
        $existing = hosting_subdomains() ?? [];
        if (!in_array($slug, $existing, true)) {
            [$code, $data] = hostinger_call('POST', hosting_path('/subdomains'), ['subdomain' => $slug, 'is_using_public_directory' => true]);
            if ($code < 200 || $code >= 300) {
                $st = ['step' => 'error_subdomain', 'message' => 'Hostinger a refusé le sous-domaine (' . $code . ') : ' . api_message($data), 'at' => time()];
                save_site_state($slug, $st);
                return $st;
            }
        }
        $st = ['step' => 'ssl_pending', 'message' => 'Sous-domaine créé sur Hostinger. Certificat SSL en attente.', 'at' => 0, 'tries' => 0];
        save_site_state($slug, $st);
    }
    if ($st['step'] === 'ssl_pending') $st = try_ssl($slug, $st);
    return $st;
}

function try_ssl(string $slug, array $st): array {
    $host = $slug . '.' . config()['domain'];
    if (!host_resolves($host)) {
        $st = ['step' => 'ssl_pending', 'message' => "L'adresse n'est pas encore visible dans le DNS. Ajoutez l'entrée « * » chez Infomaniak (une seule fois pour tous les artistes).", 'at' => time()] + $st;
        save_site_state($slug, $st);
        return $st;
    }
    [$code, $data] = hostinger_call('POST', '/api/hosting/v1/accounts/' . rawurlencode(hosting_config()['username']) . '/websites/' . rawurlencode($host) . '/ssl/setup');
    $tries = (int)($st['tries'] ?? 0) + 1;
    if ($code >= 200 && $code < 300) $st = ['step' => 'done', 'message' => 'Adresse active, certificat SSL demandé (actif en quelques minutes).', 'at' => time(), 'tries' => $tries];
    elseif ($tries >= SSL_MAX_TRIES) $st = ['step' => 'error_ssl', 'message' => 'Le certificat SSL n\'a pas pu être demandé (' . $code . ') : ' . api_message($data), 'at' => time(), 'tries' => $tries];
    else $st = ['step' => 'ssl_pending', 'message' => 'Certificat SSL : nouvel essai automatique bientôt (' . $code . ' : ' . api_message($data) . ').', 'at' => time(), 'tries' => $tries];
    save_site_state($slug, $st);
    return $st;
}

function api_message($data): string {
    if (!is_array($data)) return 'pas de réponse';
    return mb_substr((string)($data['message'] ?? $data['error'] ?? json_encode($data, JSON_UNESCAPED_UNICODE)), 0, 200);
}

/** À chaque visite de l'admin : relance (au plus toutes les 10 min) les SSL en attente. */
function retry_pending_sites(): void {
    if (!hosting_enabled()) return;
    $done = 0;
    foreach (site_states() as $slug => $st) {
        if (($st['step'] ?? '') !== 'ssl_pending' || time() - (int)($st['at'] ?? 0) < SSL_RETRY_SECONDS) continue;
        if (!isset(all_artists(true)[$slug])) continue;
        try_ssl((string)$slug, $st);
        if (++$done >= 2) break;   // garder l'admin rapide
    }
}

/* ---------- Page d'administration /admin/hebergement ---------- */

function admin_hosting(bool $post): void {
    require_admin();
    $c = hosting_config(); $error = null;
    if ($post) {
        $action = $_POST['action'] ?? 'save';
        if ($action === 'save') {
            $token = trim((string)($_POST['token'] ?? ''));
            $username = trim((string)($_POST['username'] ?? '')) ?: $c['username'];
            if ($token === '') $token = $c['token'];
            if (!preg_match('/^u\d+$/', $username)) $error = "Identifiant d'hébergement invalide (il ressemble à u548989100).";
            elseif ($token === '') $error = "Collez la clé d'API Hostinger.";
            else {
                $c = ['token' => $token, 'username' => $username, 'ip' => preg_match('/^\d+\.\d+\.\d+\.\d+$/', trim((string)($_POST['ip'] ?? ''))) ? trim($_POST['ip']) : $c['ip']];
                $prev = hosting_config();
                save_hosting_config($c);
                if (hosting_subdomains() === null) { save_hosting_config($prev); $error = "Hostinger refuse cette clé ou cet identifiant. Vérifiez-les puis réessayez."; $c = $prev; }
                else { flash('Activation automatique en marche : les nouveaux artistes auront leur adresse tout seuls.'); redirect('/admin/hebergement'); return; }
            }
        } elseif ($action === 'remove') {
            save_hosting_config(['token' => '', 'username' => $c['username'], 'ip' => $c['ip']]);
            flash('Clé supprimée. Activation automatique arrêtée.'); redirect('/admin/hebergement'); return;
        } elseif ($action === 'provision' && ($slug = (string)($_POST['slug'] ?? '')) && isset(all_artists(true)[$slug])) {
            save_site_state($slug, ['step' => 'new', 'tries' => 0, 'at' => 0]);
            $st = provision_artist_site($slug);
            flash($slug . ' : ' . $st['message']); redirect('/admin/hebergement'); return;
        }
    }
    admin_view('admin/hosting', ['c' => $c, 'error' => $error, 'states' => site_states(), 'artists' => all_artists(true),
        'remote' => hosting_enabled() ? hosting_subdomains() : null]);
}
