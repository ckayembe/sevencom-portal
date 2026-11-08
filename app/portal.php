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

/** Textes « Qui sommes-nous » : ceux de l'admin, sinon ces textes par défaut. */
function about(): array {
    $d = [
        'intro' => "Seven.com est un label de production dédié aux artistes chrétiens. Nous accompagnons chanteurs, groupes et chorales pour que leur louange sorte de l'église et touche le plus grand nombre, avec une qualité professionnelle.",
        'mission' => "Révéler, produire et faire rayonner les artistes chrétiens : de l'enregistrement à la diffusion sur toutes les plateformes, en passant par l'image, la promotion et la scène.",
        'vision' => "Devenir la maison de référence de la musique chrétienne, depuis la RDC jusqu'à la diaspora, où chaque artiste trouve les outils et l'accompagnement pour vivre de son appel.",
        'story' => "Seven.com a été fondé par Christian Kayembe, artiste chrétien, avec une conviction : les artistes chrétiens méritent un accompagnement professionnel, des outils modernes et un cadre qui respecte leur foi. Le chiffre sept, symbole biblique de plénitude, résume notre ambition : « le chiffre de votre réussite ».",
        'values' => [
            ['title' => 'La foi d\'abord', 'text' => 'Chaque projet sert un message. Nous ne produisons que ce qui honore Dieu et édifie ceux qui l\'écoutent.'],
            ['title' => 'L\'excellence', 'text' => 'Un son, une image et une communication au niveau des grands labels, parce que le message le mérite.'],
            ['title' => 'La transparence', 'text' => 'Des contrats clairs, des formules lisibles et des comptes rendus réguliers à chaque artiste.'],
            ['title' => 'L\'accompagnement', 'text' => 'Nous faisons grandir l\'artiste dans la durée : carrière, image, scène, et vie spirituelle.'],
        ],
    ];
    $a = label()['about'] ?? [];
    foreach ($d as $k => $v) if (empty($a[$k])) $a[$k] = $v;
    return $a;
}

/** Diapos du carrousel 3D : celles de l'admin d'abord, puis celles générées à partir du site. */
function slides(): array {
    $s = [];
    foreach (label()['slides'] ?? [] as $x) if (!empty($x['title']) || !empty($x['image'])) $s[] = $x + ['kicker' => 'À la une'];
    foreach (artists() as $a) {
        $img = (!empty($a['photo']) && !str_contains($a['photo'], 'placeholder')) ? $a['photo'] : ($a['logo'] ?? '');
        $s[] = ['kicker' => KINDS[$a['kind'] ?? 'solo'] === KINDS['solo'] ? 'Artiste' : KINDS[$a['kind']], 'title' => $a['name'], 'text' => $a['tagline'] ?? '',
                'image' => $img, 'fit' => ($img && $img === ($a['logo'] ?? null)) ? 'contain' : 'cover', 'link' => url($a['slug'])];
    }
    if ($r = all_releases()[0] ?? null) $s[] = ['kicker' => 'Dernière sortie', 'title' => $r['title'], 'text' => $r['artist']['name'], 'image' => str_contains($r['cover'] ?? '', 'placeholder') ? '' : ($r['cover'] ?? ''), 'link' => url(null, '/sorties')];
    if ($v = all_videos()[0] ?? null) $s[] = ['kicker' => 'Clip', 'title' => $v['title'], 'text' => $v['artist']['name'], 'image' => "https://i.ytimg.com/vi/{$v['youtube']}/hqdefault.jpg", 'link' => url(null, '/clips')];
    $s[] = ['kicker' => 'Artistes', 'title' => 'Soumettre une démo', 'text' => 'Nous écoutons chaque titre.', 'image' => '', 'link' => url(null, '/demo')];
    $s[] = ['kicker' => 'Services', 'title' => 'Production, promotion, distribution', 'text' => 'Six formules pour chaque artiste.', 'image' => '', 'link' => url(null, '/formules')];
    $s[] = ['kicker' => 'Booking', 'title' => 'Invitez nos artistes', 'text' => 'Cultes, concerts, conventions.', 'image' => '', 'link' => url(null, '/demo?objet=booking')];
    return array_slice($s, 0, 12);
}
