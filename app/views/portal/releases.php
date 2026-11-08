<?php use function App\{e, url, all_releases}; $list = all_releases(); ?>
<section>
  <h1>Nos sorties</h1>
  <p class="lead">Les derniers titres et projets des artistes du label, disponibles sur toutes les plateformes.</p>
  <?php if ($list): ?>
  <div class="grid releases"><?php foreach ($list as $r) require __DIR__ . '/_release.php'; ?></div>
  <?php else: ?><p>Les premières sorties arrivent bientôt.</p><?php endif; ?>
</section>
