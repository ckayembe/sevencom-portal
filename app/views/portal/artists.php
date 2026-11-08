<?php use function App\{e, url, artists}; ?>
<section>
  <h1>Nos artistes</h1>
  <p class="lead">Chaque artiste Seven.com a son propre site sur un sous-domaine.</p>
  <div class="grid">
  <?php foreach (artists() as $a): ?>
    <a class="card" href="<?= e(url($a['slug'])) ?>" style="--accent:<?= e($a['theme']['accent'] ?? '') ?>">
      <img src="<?= e($a['photo']) ?>" alt="" loading="lazy">
      <h3><?= e($a['name']) ?></h3>
      <p><?= e($a['genre']) ?> · <?= e($a['city']) ?></p>
      <span class="small"><?= e($a['slug']) ?>.<?= e(App\config()['domain']) ?> →</span>
    </a>
  <?php endforeach; ?>
  </div>
</section>
