<?php
declare(strict_types=1);
namespace App;

function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('sc_admin');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function current_user(): ?array {
    start_session();
    if (empty($_SESSION['uid'])) return null;
    static $u;
    if ($u === null) {
        $q = db()->prepare('SELECT * FROM users WHERE id = ?');
        $q->execute([$_SESSION['uid']]);
        $u = $q->fetch() ?: null;
    }
    return $u;
}

function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }

function can_edit_artist(string $slug): bool {
    $u = current_user();
    return $u && ($u['role'] === 'admin' || $u['artist_slug'] === $slug);
}

function csrf_token(): string {
    start_session();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function check_csrf(): void {
    if (!hash_equals(csrf_token(), (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(400); exit('Session expirée, rechargez la page.');
    }
}

function too_many_attempts(string $ip): bool {
    db()->prepare('DELETE FROM login_attempts WHERE at < ?')->execute([time() - 900]);
    $q = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ?');
    $q->execute([$ip]);
    return $q->fetchColumn() >= 8;
}

function attempt_login(string $email, string $password): ?string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '?';
    if (too_many_attempts($ip)) return 'Trop de tentatives. Réessayez dans 15 minutes.';
    $q = db()->prepare('SELECT * FROM users WHERE email = ?');
    $q->execute([trim($email)]);
    $u = $q->fetch();
    if (!$u || !password_verify($password, $u['password_hash'])) {
        db()->prepare('INSERT INTO login_attempts (ip, at) VALUES (?, ?)')->execute([$ip, time()]);
        return 'E-mail ou mot de passe incorrect.';
    }
    start_session();
    session_regenerate_id(true);
    $_SESSION['uid'] = $u['id'];
    db()->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$u['id']]);
    return null;
}

function logout(): void {
    start_session();
    $_SESSION = [];
    session_destroy();
}

function random_password(): string {
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $p = '';
    for ($i = 0; $i < 12; $i++) $p .= $chars[random_int(0, strlen($chars) - 1)];
    return $p;
}

function flash(?string $msg = null): ?string {
    start_session();
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m;
}
