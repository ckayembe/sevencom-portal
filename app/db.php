<?php
declare(strict_types=1);
namespace App;

use PDO;

/**
 * Données modifiables (artistes, comptes, infos du label) dans une base SQLite
 * stockée HORS du dossier du site : les déploiements GitHub n'y touchent jamais.
 * Hostinger : /home/<user>/domains/sevencom.tech/storage/
 */
function storage_dir(): string {
    $dir = getenv('SEVENCOM_STORAGE') ?: dirname(realpath(ROOT)) . '/storage';
    if (!is_dir("$dir/uploads")) mkdir("$dir/uploads", 0750, true);
    return $dir;
}

/** Compte administrateur créé au tout premier lancement (mot de passe à changer à la 1re connexion). */
const INITIAL_ADMIN_EMAIL = 'infos@sevencom.tech';
const INITIAL_ADMIN_HASH  = '$2y$10$PVMQAyrDKRNxsmUEoXovgOR5hj.H2HjQ08zwB77KLQ0y9s1dxC3oW';

function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . storage_dir() . '/sevencom.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    migrate($pdo);
    return $pdo;
}

function migrate(PDO $pdo): void {
    $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT NOT NULL);
        CREATE TABLE IF NOT EXISTS artists (
            slug TEXT PRIMARY KEY,
            data TEXT NOT NULL,
            published INTEGER NOT NULL DEFAULT 1,
            sort INTEGER NOT NULL DEFAULT 99,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE COLLATE NOCASE,
            name TEXT NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL CHECK (role IN ('admin','artist')),
            artist_slug TEXT REFERENCES artists(slug) ON UPDATE CASCADE ON DELETE SET NULL,
            must_change_password INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login_at TEXT
        );
        CREATE TABLE IF NOT EXISTS login_attempts (ip TEXT NOT NULL, at INTEGER NOT NULL);
        CREATE TABLE IF NOT EXISTS submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kind TEXT NOT NULL,
            data TEXT NOT NULL,
            ip TEXT NOT NULL,
            handled INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        );
    SQL);

    // Premier lancement : on importe le contenu de data/*.json et on crée le compte admin.
    if (!$pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn()) {
        $set = $pdo->prepare('INSERT INTO settings (key, value) VALUES (?, ?)');
        $set->execute(['label', json_encode(seed_json('label.json'), JSON_UNESCAPED_UNICODE)]);
        $set->execute(['tools', json_encode(seed_json('tools.json'), JSON_UNESCAPED_UNICODE)]);
        foreach (glob(ROOT . '/data/artists/*.json') as $f) {
            $a = seed_json('artists/' . basename($f));
            save_artist($a, $pdo);
        }
    }
    if (!$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn()) {
        $pdo->prepare("INSERT INTO users (email, name, password_hash, role, must_change_password) VALUES (?, ?, ?, 'admin', 1)")
            ->execute([INITIAL_ADMIN_EMAIL, 'Christian Kayembe', INITIAL_ADMIN_HASH]);
    }
}

function seed_json(string $path): array {
    $file = ROOT . '/data/' . $path;
    return is_file($file) ? json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR) : [];
}

function setting(string $key): array {
    $v = db()->prepare('SELECT value FROM settings WHERE key = ?');
    $v->execute([$key]);
    return json_decode((string)$v->fetchColumn(), true) ?: [];
}

function save_setting(string $key, array $value): void {
    db()->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value')
        ->execute([$key, json_encode($value, JSON_UNESCAPED_UNICODE)]);
}

function save_artist(array $a, ?PDO $pdo = null): void {
    $pdo ??= db();
    $pdo->prepare('INSERT INTO artists (slug, data, published, sort, updated_at) VALUES (:s, :d, :p, :o, CURRENT_TIMESTAMP)
        ON CONFLICT(slug) DO UPDATE SET data = excluded.data, published = excluded.published, sort = excluded.sort, updated_at = CURRENT_TIMESTAMP')
        ->execute([
            's' => $a['slug'],
            'd' => json_encode($a, JSON_UNESCAPED_UNICODE),
            'p' => ($a['published'] ?? true) ? 1 : 0,
            'o' => (int)($a['order'] ?? 99),
        ]);
}

/** Tous les artistes (publiés seulement, sauf $all). */
function all_artists(bool $all = false): array {
    $rows = db()->query('SELECT data, published FROM artists ' . ($all ? '' : 'WHERE published = 1 ') . 'ORDER BY sort, slug')->fetchAll();
    $list = [];
    foreach ($rows as $r) { $a = json_decode($r['data'], true); $a['published'] = (bool)$r['published']; $list[$a['slug']] = $a; }
    return $list;
}
