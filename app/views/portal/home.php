<?php use function App\{e, url, label, artists, tools}; $L = label(); ?>
<section class="hero">
  <h1 class="visually-hidden"><?= e($L['name']) ?></h1>
  <img class="hero-logo" src="<?= e($L['logo']) ?>" alt="<?= e($L['name']) ?> · <?= e($L['slogan']) ?>">
  <p class="slogan"><?= e($L['tagline']) ?></p>
  <p class="lead"><?= e($L['description']) ?></p>
  <p><a class="btn" href="<?= e(url(null, '/artistes')) ?>">Découvrir nos artistes</a>
     <a class="btn ghost" href="<?= e(url(null, '/outils')) ?>">Nos outils</a></p>
</section>
<section>
  <h2>Artistes</h2>
  <div class="grid">
  <?php foreach (artists() as $a): ?>
    <a class="card" href="<?= e(url($a['slug'])) ?>" style="--accent:<?= e($a['theme']['accent'] ?? '') ?>">
      <img src="<?= e($a['photo']) ?>" alt="" loading="lazy">
      <h3><?= e($a['name']) ?></h3>
      <p><?= e($a['tagline']) ?></p>
      <span class="small"><?= e($a['slug']) ?>.<?= e(App\config()['domain']) ?> →</span>
    </a>
  <?php endforeach; ?>
  </div>
</section>
<section>
  <h2>Outils du label</h2>
  <div class="grid">
  <?php foreach (array_slice(tools(), 0, 3) as $t): ?>
    <div class="card"><h3><?= e($t['name']) ?></h3><p><?= e($t['summary']) ?></p></div>
  <?php endforeach; ?>
  </div>
  <p><a href="<?= e(url(null, '/outils')) ?>">Voir tous les outils →</a></p>
</section>
