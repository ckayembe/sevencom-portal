<?php
declare(strict_types=1);
namespace App;

/** Routes de l'espace d'administration (sevencom.tech/admin). Retourne false si la route n'existe pas. */
function admin_route(string $path): bool {
    $m = [];
    $post = $_SERVER['REQUEST_METHOD'] === 'POST';
    if ($post) check_csrf();

    if ($path === '/admin/connexion') { admin_login($post); return true; }
    if ($path === '/admin/deconnexion') { logout(); redirect('/admin/connexion'); return true; }

    $u = current_user();
    if (!$u) { redirect('/admin/connexion'); return true; }
    if ($u['must_change_password'] && $path !== '/admin/mot-de-passe') { redirect('/admin/mot-de-passe'); return true; }

    switch (true) {
        case $path === '/admin':
            if (!is_admin()) { redirect('/admin/artistes/' . $u['artist_slug']); return true; }
            admin_view('admin/dashboard', ['artists' => all_artists(true), 'users' => db()->query('SELECT * FROM users ORDER BY role, name')->fetchAll()]);
            return true;
        case $path === '/admin/mot-de-passe':
            admin_password($post, $u); return true;
        case $path === '/admin/label':
            require_admin(); admin_label($post); return true;
        case $path === '/admin/artistes/nouveau':
            require_admin(); admin_artist(null, $post); return true;
        case (bool)preg_match('#^/admin/artistes/([a-z0-9-]+)$#', $path, $m):
            if (!can_edit_artist($m[1])) { forbidden(); return true; }
            admin_artist($m[1], $post); return true;
        case (bool)preg_match('#^/admin/comptes/(\d+)/nouveau-mot-de-passe$#', $path, $m) && $post:
            require_admin(); admin_reset_password((int)$m[1]); return true;
    }
    return false;
}

function redirect(string $path): void { header('Location: ' . url(null, $path), true, 303); }
function forbidden(): void { admin_view('admin/message', ['title' => 'Accès refusé', 'text' => "Vous n'avez pas accès à cette page."], 403); }
function require_admin(): void { if (!is_admin()) { forbidden(); exit; } }

function admin_view(string $view, array $vars = [], int $status = 200): void {
    header('X-Robots-Tag: noindex');
    header('Cache-Control: no-store');
    render($view, $vars + ['section' => 'admin', 'flash' => flash()], $status);
}

function admin_login(bool $post): void {
    if (current_user()) { redirect('/admin'); return; }
    $error = null;
    if ($post) {
        $error = attempt_login((string)($_POST['email'] ?? ''), (string)($_POST['password'] ?? ''));
        if (!$error) { redirect('/admin'); return; }
    }
    admin_view('admin/login', ['error' => $error, 'email' => $_POST['email'] ?? '']);
}

function admin_password(bool $post, array $u): void {
    $error = null;
    if ($post) {
        $cur = (string)($_POST['current'] ?? ''); $new = (string)($_POST['new'] ?? ''); $conf = (string)($_POST['confirm'] ?? '');
        if (!password_verify($cur, $u['password_hash'])) $error = 'Mot de passe actuel incorrect.';
        elseif (strlen($new) < 10) $error = 'Le nouveau mot de passe doit faire au moins 10 caractères.';
        elseif ($new !== $conf) $error = 'Les deux mots de passe ne correspondent pas.';
        else {
            db()->prepare('UPDATE users SET password_hash = ?, must_change_password = 0 WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
            flash('Mot de passe enregistré.');
            redirect('/admin'); return;
        }
    }
    admin_view('admin/password', ['error' => $error, 'forced' => (bool)$u['must_change_password']]);
}

function admin_reset_password(int $id): void {
    $pw = random_password();
    db()->prepare('UPDATE users SET password_hash = ?, must_change_password = 1 WHERE id = ?')->execute([password_hash($pw, PASSWORD_DEFAULT), $id]);
    $q = db()->prepare('SELECT * FROM users WHERE id = ?'); $q->execute([$id]);
    admin_view('admin/credentials', ['user' => $q->fetch(), 'password' => $pw, 'artist' => null]);
}

/* ---------- Formulaires ---------- */

function post_str(string $k, int $max = 2000): string { return mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max); }

/** Lignes répétables : $_POST[$prefix][$champ][] -> liste de lignes non vides */
function post_rows(string $prefix, array $fields): array {
    $in = $_POST[$prefix] ?? [];
    $n = max(array_map(fn($f) => count($in[$f] ?? []), $fields) ?: [0]);
    $rows = [];
    for ($i = 0; $i < $n; $i++) {
        $row = [];
        foreach ($fields as $f) $row[$f] = mb_substr(trim((string)($in[$f][$i] ?? '')), 0, 2000);
        if (implode('', $row) !== '') $rows[] = $row + ['_i' => $i];
    }
    return $rows;
}

function clean_url(string $u): string {
    if ($u === '') return '';
    if (!preg_match('#^https?://#i', $u)) $u = 'https://' . $u;
    return filter_var($u, FILTER_VALIDATE_URL) ? $u : '';
}

function youtube_id(string $s): string {
    if (preg_match('#(?:v=|youtu\.be/|embed/|shorts/)([\w-]{11})#', $s, $m)) return $m[1];
    return preg_match('#^[\w-]{11}$#', $s) ? $s : '';
}

/** Enregistre une image envoyée, renvoie son URL publique (/media/...) ou null. */
function upload_image(?array $f): ?string {
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;
    if ($f['size'] > 8 * 1024 * 1024) throw new \RuntimeException('Image trop lourde (8 Mo maximum).');
    $info = @getimagesize($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
    if (!$ext) throw new \RuntimeException('Format d\'image non accepté (JPG, PNG ou WebP).');
    $name = bin2hex(random_bytes(12)) . ".$ext";
    if (!move_uploaded_file($f['tmp_name'], storage_dir() . "/uploads/$name")) throw new \RuntimeException('Envoi de l\'image impossible.');
    return "/media/$name";
}

/** $_FILES[prefix][...][i] -> fichier i */
function nth_file(string $prefix, string $field, int $i): ?array {
    $F = $_FILES[$prefix] ?? null;
    if (!$F || !isset($F['error'][$field][$i])) return null;
    return ['name' => $F['name'][$field][$i], 'tmp_name' => $F['tmp_name'][$field][$i], 'error' => $F['error'][$field][$i], 'size' => $F['size'][$field][$i]];
}

const LINK_FIELDS = ['spotify' => 'Spotify', 'apple' => 'Apple Music', 'deezer' => 'Deezer', 'youtube' => 'YouTube', 'autre' => 'Autre lien'];

function admin_artist(?string $slug, bool $post): void {
    $isNew = $slug === null;
    $a = $isNew ? ['slug' => '', 'published' => true, 'order' => 99, 'theme' => ['accent' => '#2c6e9e'], 'bio' => [], 'releases' => [], 'videos' => [], 'events' => [], 'socials' => []]
                : (all_artists(true)[$slug] ?? null);
    if (!$a) { admin_view('admin/message', ['title' => 'Artiste introuvable', 'text' => ''], 404); return; }
    $error = null; $account = null;

    if ($post) {
        try {
            if ($isNew) {
                $newSlug = strtolower(post_str('slug', 40));
                if (!preg_match('/^[a-z0-9]([a-z0-9-]{0,38}[a-z0-9])?$/', $newSlug)) throw new \RuntimeException('Sous-domaine invalide : lettres minuscules, chiffres et tirets seulement.');
                if (in_array($newSlug, ['www', 'admin', 'mail', 'ftp', 'api', 'media', 'webmail'], true) || isset(all_artists(true)[$newSlug])) throw new \RuntimeException('Ce sous-domaine est déjà pris.');
                $a['slug'] = $newSlug;
                $email = post_str('account_email', 200);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \RuntimeException('Adresse e-mail du compte artiste invalide.');
                $q = db()->prepare('SELECT 1 FROM users WHERE email = ?'); $q->execute([$email]);
                if ($q->fetchColumn()) throw new \RuntimeException('Un compte existe déjà avec cet e-mail.');
            }
            $a['name'] = post_str('name', 120) ?: throw new \RuntimeException('Le nom de l\'artiste est obligatoire.');
            $a['tagline'] = post_str('tagline', 300);
            $a['genre'] = post_str('genre', 80);
            $a['city'] = post_str('city', 80);
            $a['booking'] = filter_var(post_str('booking', 200), FILTER_VALIDATE_EMAIL) ?: '';
            $accent = post_str('accent', 7);
            $a['theme']['accent'] = preg_match('/^#[0-9a-f]{6}$/i', $accent) ? $accent : '#2c6e9e';
            $a['bio'] = array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', post_str('bio', 10000)))));
            if ($p = upload_image($_FILES['photo'] ?? null)) { $a['photo'] = $p; $a['cover'] = $p; }
            $a['photo'] ??= '/assets/img/artists/placeholder.svg';

            $a['socials'] = [];
            foreach (post_rows('socials', ['name', 'url']) as $r) if ($r['name'] && ($u = clean_url($r['url']))) $a['socials'][$r['name']] = $u;

            $old = array_values($a['releases']);
            $a['releases'] = [];
            foreach (post_rows('releases', array_merge(['title', 'type', 'date', 'old_cover'], array_keys(LINK_FIELDS))) as $r) {
                if ($r['title'] === '') continue;
                $links = [];
                foreach (LINK_FIELDS as $k => $label) if ($u = clean_url($r[$k])) $links[$label] = $u;
                $cover = upload_image(nth_file('releases', 'cover', $r['_i'])) ?: (str_starts_with($r['old_cover'], '/') ? $r['old_cover'] : '');
                $a['releases'][] = ['title' => $r['title'], 'type' => $r['type'] ?: 'Single', 'date' => $r['date'], 'cover' => $cover ?: $a['photo'], 'links' => $links];
            }
            $a['videos'] = [];
            foreach (post_rows('videos', ['title', 'url']) as $r) if ($id = youtube_id($r['url'])) $a['videos'][] = ['title' => $r['title'] ?: 'Vidéo', 'youtube' => $id];
            $a['events'] = [];
            foreach (post_rows('events', ['date', 'venue', 'city']) as $r) if ($r['date'] && $r['venue']) $a['events'][] = ['date' => $r['date'], 'venue' => $r['venue'], 'city' => $r['city']];

            if (is_admin()) {
                $a['published'] = !empty($_POST['published']);
                $a['order'] = (int)($_POST['order'] ?? 99);
            }
            save_artist($a);

            if ($isNew) {
                $pw = random_password();
                db()->prepare("INSERT INTO users (email, name, password_hash, role, artist_slug, must_change_password) VALUES (?, ?, ?, 'artist', ?, 1)")
                    ->execute([$email, $a['name'], password_hash($pw, PASSWORD_DEFAULT), $a['slug']]);
                $q = db()->prepare('SELECT * FROM users WHERE email = ?'); $q->execute([$email]);
                admin_view('admin/credentials', ['user' => $q->fetch(), 'password' => $pw, 'artist' => $a]);
                return;
            }
            flash('Fiche enregistrée. Le site est déjà à jour.');
            redirect('/admin/artistes/' . $a['slug']); return;
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
    admin_view('admin/artist_form', ['a' => $a, 'isNew' => $isNew, 'error' => $error]);
}

function admin_label(bool $post): void {
    $L = setting('label'); $tools = setting('tools'); $error = null;
    if ($post) {
        $L['name'] = post_str('name', 80) ?: $L['name'];
        $L['tagline'] = post_str('tagline', 200);
        $L['slogan'] = post_str('slogan', 200);
        $L['description'] = post_str('description', 2000);
        $L['email'] = filter_var(post_str('email', 200), FILTER_VALIDATE_EMAIL) ?: $L['email'];
        $L['socials'] = [];
        foreach (post_rows('socials', ['name', 'url']) as $r) if ($r['name'] && ($u = clean_url($r['url']))) $L['socials'][$r['name']] = $u;
        $tools = [];
        foreach (post_rows('tools', ['name', 'summary', 'status', 'url', 'cta']) as $r) {
            if ($r['name'] === '') continue;
            $tools[] = ['name' => $r['name'], 'summary' => $r['summary'], 'status' => $r['status'], 'url' => clean_url($r['url']), 'cta' => $r['cta']];
        }
        save_setting('label', $L); save_setting('tools', $tools);
        flash('Informations du label enregistrées.');
        redirect('/admin/label'); return;
    }
    admin_view('admin/label_form', ['L' => $L, 'tools' => $tools, 'error' => $error]);
}

/** Sert une image envoyée depuis l'admin. */
function serve_media(string $name): void {
    $file = storage_dir() . '/uploads/' . basename($name);
    if (!preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $name) || !is_file($file)) { http_response_code(404); return; }
    header('Content-Type: ' . ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][pathinfo($file, PATHINFO_EXTENSION)]);
    header('Cache-Control: public, max-age=31536000, immutable');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
}
