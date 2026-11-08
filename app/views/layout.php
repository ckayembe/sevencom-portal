<?php use function App\{e, url, label, artists}; $L = label();
$title = isset($artist) ? $artist['name'] . ' · ' . $L['name'] : $L['name'] . ' · ' . $L['tagline'];
$accent = $artist['theme']['accent'] ?? $L['theme']['accent']; ?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($artist['tagline'] ?? $L['description']) ?>">
<link rel="icon" href="/assets/img/logo-mark.jpg">
<link rel="stylesheet" href="/assets/css/style.css">
<style>:root{--accent:<?= e($accent) ?>}</style>
</head>
<body class="<?= e($section) ?>">
<?php if ($section === 'artist'): ?>
  <div class="labelbar">
    <a href="<?= e(url()) ?>" class="brand-sm"><img src="/assets/img/logo-mark.jpg" alt=""><?= e($L['name']) ?></a>
    <span>Artiste du label</span>
    <nav>
      <?php foreach (artists() as $a): if ($a['slug'] === $artist['slug']) continue; ?>
        <a href="<?= e(url($a['slug'])) ?>"><?= e($a['name']) ?></a>
      <?php endforeach; ?>
      <a href="<?= e(url(null, '/artistes')) ?>">Tous les artistes</a>
    </nav>
  </div>
<?php else: ?>
  <header class="site">
    <a href="<?= e(url()) ?>" class="brand"><img src="/assets/img/logo-mark.jpg" alt=""><?= e($L['name']) ?></a>
    <nav>
      <a href="<?= e(url(null, '/artistes')) ?>">Artistes</a>
      <a href="<?= e(url(null, '/outils')) ?>">Outils</a>
      <a href="<?= e(url(null, '/contact')) ?>">Contact</a>
    </nav>
  </header>
<?php endif; ?>
<main><?= $content ?></main>
<footer class="site">
  <p>© <?= date('Y') ?> <a href="<?= e(url()) ?>"><?= e($L['name']) ?></a> · <?= e($L['tagline']) ?></p>
  <p class="small"><?php foreach (artists() as $a): ?><a href="<?= e(url($a['slug'])) ?>"><?= e($a['slug']) ?>.<?= e(App\config()['domain']) ?></a> <?php endforeach; ?></p>
</footer>
</body>
</html>
