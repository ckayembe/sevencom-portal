<?php
declare(strict_types=1);
namespace App;

/**
 * Outil « Planificateur de sorties » : pour chaque single / EP / album, un rétroplanning
 * calculé à partir de la date de sortie (J-60 … J+14), avec les étapes à cocher,
 * les échéances en retard et un export vers l'agenda du téléphone (.ics).
 */

/** Étapes proposées par défaut : [jours par rapport à la sortie, étape, catégorie] */
const PLAN_TEMPLATE = [
    [-60, 'Enregistrement terminé, mixage et mastering validés', 'Production'],
    [-50, 'Pochette finale (3000 × 3000 px) prête', 'Production'],
    [-45, 'Crédits et métadonnées : auteurs, compositeurs, musiciens, ISRC', 'Administratif'],
    [-42, 'Contrat et partage des revenus signés', 'Administratif'],
    [-35, 'Envoi au distributeur (audio, pochette, métadonnées)', 'Distribution'],
    [-28, 'Pitch éditorial envoyé depuis Spotify for Artists', 'Distribution'],
    [-21, 'Lien de pré-sauvegarde créé et partagé', 'Promotion'],
    [-21, 'Visuels réseaux sociaux et teaser prêts', 'Promotion'],
    [-14, 'Annonce aux églises, radios et médias chrétiens', 'Promotion'],
    [-7,  'Teaser vidéo publié', 'Promotion'],
    [-3,  'Fiche artiste à jour sur le site (sortie, liens)', 'Promotion'],
    [0,   'Jour J : publication sur tous les réseaux', 'Sortie'],
    [0,   'Clip ou visualiser en ligne sur YouTube', 'Sortie'],
    [7,   'Remerciements au public et contenu live / coulisses', 'Après la sortie'],
    [14,  'Bilan des premières écoutes et suite de la campagne', 'Après la sortie'],
];

const PLAN_TYPES = ['Single', 'EP', 'Album', 'Clip', 'Live'];

function planner_migrate(\PDO $pdo): void {
    $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS release_plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            artist_slug TEXT REFERENCES artists(slug) ON UPDATE CASCADE ON DELETE CASCADE,
            release_date TEXT NOT NULL,
            data TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
    SQL);
}

function plan_row(array $r): array {
    return json_decode($r['data'], true) + ['id' => (int)$r['id'], 'artist_slug' => $r['artist_slug'], 'date' => $r['release_date']];
}

function plan(int $id): ?array {
    $q = db()->prepare('SELECT * FROM release_plans WHERE id = ?'); $q->execute([$id]);
    $r = $q->fetch();
    return $r ? plan_row($r) : null;
}

/** Plannings visibles (tous pour l'admin, ceux de son artiste sinon), du plus proche au plus lointain. */
function my_plans(): array {
    if (is_admin()) $q = db()->query('SELECT * FROM release_plans ORDER BY release_date');
    else { $q = db()->prepare('SELECT * FROM release_plans WHERE artist_slug = ? ORDER BY release_date'); $q->execute([current_user()['artist_slug'] ?? '']); }
    return array_map('App\plan_row', $q->fetchAll());
}

function can_edit_plan(array $p): bool {
    return is_admin() || ($p['artist_slug'] && $p['artist_slug'] === (current_user()['artist_slug'] ?? null));
}

function save_plan(array $p): int {
    $data = ['title' => $p['title'], 'type' => $p['type'], 'notes' => $p['notes'], 'tasks' => array_values($p['tasks'])];
    $args = [$p['artist_slug'] ?: null, $p['date'], json_encode($data, JSON_UNESCAPED_UNICODE)];
    if (empty($p['id'])) {
        db()->prepare('INSERT INTO release_plans (artist_slug, release_date, data) VALUES (?, ?, ?)')->execute($args);
        return (int)db()->lastInsertId();
    }
    db()->prepare('UPDATE release_plans SET artist_slug = ?, release_date = ?, data = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([...$args, $p['id']]);
    return (int)$p['id'];
}

function add_days(string $date, int $days): string {
    return (new \DateTimeImmutable($date))->modify(($days >= 0 ? '+' : '') . $days . ' days')->format('Y-m-d');
}

function today(): string { return (new \DateTimeImmutable('now', new \DateTimeZone('Africa/Kinshasa')))->format('Y-m-d'); }

/** Échéance d'une étape : date fixée à la main, sinon date de sortie + décalage. */
function task_due(array $p, array $t): string {
    return !empty($t['due']) ? $t['due'] : add_days($p['date'], (int)($t['offset'] ?? 0));
}

/** Avancement : [faites, total, en retard, prochaine étape] */
function plan_progress(array $p): array {
    $done = 0; $late = 0; $next = null; $today = today();
    foreach ($p['tasks'] as $t) {
        if (!empty($t['done'])) { $done++; continue; }
        $due = task_due($p, $t);
        if ($due < $today) $late++;
        if (!$next || $due < $next['due']) $next = array_merge($t, ['due' => $due]);
    }
    return [$done, count($p['tasks']), $late, $next];
}

function days_label(string $releaseDate): string {
    $n = (int)(new \DateTimeImmutable(today()))->diff(new \DateTimeImmutable($releaseDate))->format('%r%a');
    return match (true) { $n > 1 => "dans $n jours", $n === 1 => 'demain', $n === 0 => "aujourd'hui", $n === -1 => 'hier', default => 'il y a ' . (-$n) . ' jours' };
}

function offset_label(int $o): string { return $o === 0 ? 'Jour J' : ($o < 0 ? 'J' . $o : 'J+' . $o); }

/* ---------- Routes /admin/planning ---------- */

function admin_planner_route(string $path, bool $post): bool {
    $m = [];
    if ($path === '/admin/planning') {
        admin_view('admin/plans', ['plans' => my_plans()]); return true;
    }
    if ($path === '/admin/planning/nouveau') { admin_plan(null, $post); return true; }
    if (preg_match('#^/admin/planning/(\d+)\.ics$#', $path, $m)) {
        $p = plan((int)$m[1]);
        if (!$p || !can_edit_plan($p)) { forbidden(); return true; }
        plan_ics($p); return true;
    }
    if (preg_match('#^/admin/planning/(\d+)/supprimer$#', $path, $m) && $post) {
        $p = plan((int)$m[1]);
        if (!$p || !can_edit_plan($p)) { forbidden(); return true; }
        db()->prepare('DELETE FROM release_plans WHERE id = ?')->execute([$p['id']]);
        flash('Planning supprimé.'); redirect('/admin/planning'); return true;
    }
    if (preg_match('#^/admin/planning/(\d+)$#', $path, $m)) { admin_plan((int)$m[1], $post); return true; }
    return false;
}

function admin_plan(?int $id, bool $post): void {
    $u = current_user();
    $artists = array_filter(all_artists(true), fn($a) => can_edit_artist($a['slug']));
    if ($id === null) {
        $p = ['id' => null, 'artist_slug' => is_admin() ? (string)($_GET['artiste'] ?? array_key_first($artists) ?? '') : $u['artist_slug'],
              'title' => '', 'type' => 'Single', 'date' => add_days(today(), 60), 'notes' => '',
              'tasks' => array_map(fn($t) => ['label' => $t[1], 'cat' => $t[2], 'offset' => $t[0], 'due' => '', 'done' => false, 'note' => ''], PLAN_TEMPLATE)];
    } else {
        $p = plan($id);
        if (!$p) { admin_view('admin/message', ['title' => 'Planning introuvable', 'text' => ''], 404); return; }
        if (!can_edit_plan($p)) { forbidden(); return; }
    }
    $error = null;

    if ($post) {
        try {
            $as = post_str('artist_slug', 40);
            $p['artist_slug'] = is_admin() ? (isset($artists[$as]) ? $as : '') : $u['artist_slug'];
            $p['title'] = post_str('title', 160);
            $type = post_str('type', 20);
            $p['type'] = in_array($type, PLAN_TYPES, true) ? $type : 'Single';
            $d = post_str('date', 10);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) || !strtotime($d)) throw new \RuntimeException('Date de sortie invalide.');
            $before = preg_match('/^\d{4}-\d{2}-\d{2}$/', post_str('date_before', 10)) ? post_str('date_before', 10) : $d;
            $p['date'] = $d;
            $p['notes'] = post_str('notes', 4000);
            $p['tasks'] = [];
            foreach (post_rows('tasks', ['label', 'cat', 'offset', 'due', 'note', 'done']) as $r) {
                if ($r['label'] === '') continue;
                $due = preg_match('/^\d{4}-\d{2}-\d{2}$/', $r['due']) && strtotime($r['due']) ? $r['due'] : '';
                if (!is_numeric($r['offset'])) {
                    // Nouvelle étape : on la rattache à la date de sortie (J-x) pour qu'elle suive les changements de date
                    $offset = $due ? (int)(new \DateTimeImmutable($p['date']))->diff(new \DateTimeImmutable($due))->format('%r%a') : 0;
                    $due = '';
                } else {
                    $offset = max(-365, min(365, (int)$r['offset']));
                    // Date calculée automatiquement (avec l'ancienne ou la nouvelle date de sortie) : elle suivra la date de sortie
                    if ($due === add_days($before, $offset) || $due === add_days($p['date'], $offset)) $due = '';
                }
                $p['tasks'][] = ['label' => $r['label'], 'cat' => $r['cat'] ?: 'Autre', 'offset' => $offset, 'due' => $due, 'done' => $r['done'] === '1', 'note' => $r['note']];
            }
            if ($p['title'] === '') throw new \RuntimeException('Le titre de la sortie est obligatoire.');
            if (!is_admin() && !$p['artist_slug']) throw new \RuntimeException('Votre compte n\'est rattaché à aucun artiste.');
            usort($p['tasks'], fn($a, $b) => strcmp(task_due($p, $a), task_due($p, $b)));
            $newId = save_plan($p);

            // Option : ajouter la sortie à la fiche de l'artiste (affichée sur son site et le portail)
            if (!empty($_POST['add_release']) && $p['artist_slug'] && ($a = all_artists(true)[$p['artist_slug']] ?? null)) {
                $exists = false;
                foreach ($a['releases'] ?? [] as $r) if (mb_strtolower($r['title'] ?? '') === mb_strtolower($p['title'])) $exists = true;
                if (!$exists) {
                    $a['releases'][] = ['title' => $p['title'], 'type' => $p['type'], 'date' => $p['date'], 'cover' => $a['photo'] ?? '', 'links' => []];
                    save_artist($a);
                }
            }
            flash($id === null ? 'Planning créé : les échéances sont calculées depuis la date de sortie.' : 'Planning enregistré.');
            redirect('/admin/planning/' . $newId); return;
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
    admin_view('admin/plan_form', ['p' => $p, 'artists' => $artists, 'error' => $error]);
}

/** Export des échéances non faites vers un agenda (Google Agenda, iPhone, Outlook). */
function plan_ics(array $p): void {
    $esc = fn(string $s) => addcslashes(str_replace(["\r\n", "\n"], '\n', $s), ',;\\');
    $artist = $p['artist_slug'] ? (all_artists(true)[$p['artist_slug']]['name'] ?? '') : '';
    $who = trim($artist . ' · ' . $p['title'], ' ·');
    $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Seven.com//Planificateur de sorties//FR', 'CALSCALE:GREGORIAN', 'X-WR-CALNAME:' . $esc('Sortie ' . $who)];
    $events = [['label' => 'SORTIE ' . mb_strtoupper($p['type']), 'note' => $p['notes'], 'due' => $p['date'], 'k' => 'release']];
    foreach ($p['tasks'] as $i => $t) if (empty($t['done'])) $events[] = ['label' => $t['label'], 'note' => $t['note'] ?? '', 'due' => task_due($p, $t), 'k' => $i];
    foreach ($events as $ev) {
        $d = str_replace('-', '', $ev['due']);
        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:plan' . $p['id'] . '-' . $ev['k'] . '@' . config()['domain'];
        $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
        $lines[] = 'DTSTART;VALUE=DATE:' . $d;
        $lines[] = 'DTEND;VALUE=DATE:' . str_replace('-', '', add_days($ev['due'], 1));
        $lines[] = 'SUMMARY:' . $esc($ev['label'] . ' (' . $who . ')');
        if ($ev['note']) $lines[] = 'DESCRIPTION:' . $esc($ev['note']);
        $lines[] = 'END:VEVENT';
    }
    $lines[] = 'END:VCALENDAR';
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="sortie-' . (slugify($p['title']) ?: $p['id']) . '.ics"');
    header('Cache-Control: no-store');
    echo implode("\r\n", $lines) . "\r\n";
}

function date_fr(string $d): string {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m)) return $d;
    $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return ((int)$m[3] === 1 ? '1er' : (int)$m[3]) . ' ' . $mois[(int)$m[2] - 1] . ' ' . $m[1];
}
