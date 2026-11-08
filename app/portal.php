<?php
declare(strict_types=1);
namespace App;

/** Toutes les sorties des artistes publiés, les plus récentes d'abord. */
function all_releases(): array {
    $out = [];
    foreach (artists() as $a) foreach ($a['releases'] ?? [] as $r) $out[] = $r + ['artist' => $a];
    usort($out, fn($x, $y) => strcmp(sort_date($y['date'] ?? ''), sort_date($x['date'] ?? '')));
    return $out;
}

/** Tous les clips des artistes publiés (ordre de la fiche, artistes alternés). */
function all_videos(): array {
    $lists = [];
    foreach (artists() as $a) $lists[] = array_map(fn($v) => $v + ['artist' => $a], $a['videos'] ?? []);
    $out = [];
    for ($i = 0; $lists && $i < 50; $i++) foreach ($lists as $l) if (isset($l[$i])) $out[] = $l[$i];
    return $out;
}

/** « 12/03/2025 », « 2025-03 », « 2025 »… -> clé triable. */
function sort_date(string $d): string {
    if (preg_match('/(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{4})/', $d, $m)) return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
    if (preg_match('/(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?/', $d, $m)) return sprintf('%04d-%02d-%02d', $m[1], $m[2] ?? 0, $m[3] ?? 0);
    return '0000';
}

/** Chiffres clés : calculés à partir du site, plus ceux saisis dans l'admin. */
function key_figures(): array {
    $artists = artists();
    $figs = [
        ['value' => (string)count($artists), 'label' => count($artists) > 1 ? 'artistes accompagnés' : 'artiste accompagné'],
        ['value' => (string)count(all_releases()), 'label' => 'titres et projets sortis'],
        ['value' => (string)count(all_videos()), 'label' => 'clips vidéo'],
    ];
    foreach (label()['stats'] ?? [] as $s) if (($s['value'] ?? '') !== '') $figs[] = $s;
    return array_values(array_filter($figs, fn($f) => $f['value'] !== '0'));
}

const SUBMISSION_KINDS = ['demo' => 'Soumettre une démo', 'booking' => 'Booking / invitation', 'partenariat' => 'Partenariat', 'autre' => 'Autre demande'];

/** Formulaire public « Soumettre une démo / booking ». */
function demo_page(): void {
    $v = ['kind' => in_array($_GET['objet'] ?? '', array_keys(SUBMISSION_KINDS), true) ? $_GET['objet'] : 'demo'];
    $errors = []; $sent = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        foreach (['kind' => 20, 'name' => 120, 'email' => 200, 'phone' => 40, 'city' => 80, 'artist_type' => 20, 'links' => 1000, 'message' => 4000] as $f => $max)
            $v[$f] = mb_substr(trim((string)($_POST[$f] ?? '')), 0, $max);
        if (!isset(SUBMISSION_KINDS[$v['kind']])) $v['kind'] = 'autre';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (($_POST['website'] ?? '') !== '') { $sent = true; }  // pot de miel anti-robots
        else {
            if ($v['name'] === '') $errors[] = 'Indiquez votre nom ou celui du groupe.';
            if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse e-mail invalide.';
            if ($v['kind'] === 'demo' && !preg_match('#https?://#', $v['links'])) $errors[] = 'Ajoutez au moins un lien vers votre musique (YouTube, SoundCloud, Drive…).';
            if ($v['kind'] !== 'demo' && $v['message'] === '') $errors[] = 'Décrivez votre demande.';
            $q = db()->prepare("SELECT COUNT(*) FROM submissions WHERE ip = ? AND created_at > datetime('now', '-1 hour')");
            $q->execute([$ip]);
            if ($q->fetchColumn() >= 5) $errors[] = 'Trop de messages envoyés. Réessayez plus tard ou écrivez-nous par e-mail.';
            if (!$errors) {
                db()->prepare('INSERT INTO submissions (kind, data, ip) VALUES (?, ?, ?)')->execute([$v['kind'], json_encode($v, JSON_UNESCAPED_UNICODE), $ip]);
                notify_submission($v);
                $sent = true;
            }
        }
    }
    render('portal/demo', ['section' => 'portal', 'v' => $v, 'errors' => $errors, 'sent' => $sent]);
}

function notify_submission(array $v): void {
    $to = label()['email'] ?? '';
    if (!$to || !function_exists('mail')) return;
    $body = '';
    foreach (['Objet' => SUBMISSION_KINDS[$v['kind']], 'Nom' => $v['name'], 'E-mail' => $v['email'], 'Téléphone' => $v['phone'], 'Ville' => $v['city'], 'Type' => $v['artist_type'], 'Liens' => $v['links'], 'Message' => $v['message']] as $k => $x)
        if ($x !== '') $body .= "$k : $x\n";
    $body .= "\nÀ traiter dans l'admin : " . url(null, '/admin/messages') . "\n";
    $subject = '=?UTF-8?B?' . base64_encode('[Seven.com] ' . SUBMISSION_KINDS[$v['kind']] . ' – ' . $v['name']) . '?=';
    $headers = "Content-Type: text/plain; charset=UTF-8\r\nFrom: Seven.com <$to>\r\nReply-To: " . str_replace(["\r", "\n"], '', $v['email']);
    @mail($to, $subject, $body, $headers);
}
