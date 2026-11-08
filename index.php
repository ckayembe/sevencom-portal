<?php
declare(strict_types=1);
// Serveur de dev PHP : laisser passer les fichiers statiques
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) return false;
require __DIR__ . '/app/bootstrap.php';
App\run();
