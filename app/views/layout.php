<?php use function App\{e, url, label, artists, asset}; $L = label();
$title = isset($artist) ? $artist['name'] . ' · ' . $L['name'] : $L['name'] . ' · ' . $L['tagline'];
$accent = $artist['theme']['accent'] ?? $L['theme']['accent']; ?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($artist['tagline'] ?? $L['description']) ?>">
<link rel="icon" href="<?= e(asset('/assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('/assets/css/style.css')) ?>">
<style>:root{--accent:<?= e($accent) ?>}</style>
</head>
<body class="<?= e($section) ?>">
<?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
<?php if ($section === 'artist'): ?>
  <div class="labelbar">
    <a href="<?= e(url()) ?>" class="brand-sm"><img src="/assets/img/logo-mark.webp" alt=""><?= e($L['name']) ?></a>
    <span>Artiste du label</span>
    <nav>
      <?php foreach (artists() as $a): if ($a['slug'] === $artist['slug']) continue; ?>
        <a href="<?= e(url($a['slug'])) ?>"><?= e($a['name']) ?></a>
      <?php endforeach; ?>
      <a href="<?= e(url(null, '/artistes')) ?>">Tous les artistes</a>
    </nav>
  </div>
<?php elseif ($section === 'admin'): $me = App\current_user(); ?>
  <header class="site admin-bar">
    <a href="<?= e(url(null, '/admin')) ?>" class="brand"><img src="/assets/img/logo-mark.webp" alt="">Admin</a>
    <nav>
      <?php if ($me && !$me['must_change_password']): ?>
        <?php if ($me['role'] === 'admin'): ?>
          <a href="<?= e(url(null, '/admin')) ?>">Artistes</a>
          <a href="<?= e(url(null, '/admin/label')) ?>">Label &amp; outils</a>
        <?php elseif ($me['artist_slug']): ?>
          <a href="<?= e(url(null, '/admin/artistes/' . $me['artist_slug'])) ?>">Ma fiche</a>
        <?php endif; ?>
        <a href="<?= e(url(null, '/admin/mot-de-passe')) ?>">Mot de passe</a>
      <?php endif; ?>
      <a href="<?= e(url()) ?>">Voir le site</a>
      <?php if ($me): ?><a href="<?= e(url(null, '/admin/deconnexion')) ?>">Déconnexion</a><?php endif; ?>
    </nav>
  </header>
<?php else: ?>
  <header class="site">
    <a href="<?= e(url()) ?>" class="brand"><img src="/assets/img/logo-mark.webp" alt=""><?= e($L['name']) ?></a>
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
