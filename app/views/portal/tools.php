<?php use function App\{e, tools, builtin_tools}; ?>
<section>
  <h1>Outils du label</h1>
  <p class="lead">Les services et outils que Seven.com met à disposition de ses artistes et partenaires.</p>
  <div class="grid">
  <?php foreach (array_merge(builtin_tools(), tools()) as $t): ?>
    <div class="card">
      <h3><?= e($t['name']) ?></h3>
      <p><?= e($t['summary']) ?></p>
      <?php if (!empty($t['url'])): ?><p><a class="btn" href="<?= e($t['url']) ?>"><?= e($t['cta'] ?? 'Découvrir') ?></a></p><?php endif; ?>
      <?php if (!empty($t['status'])): ?><span class="tag"><?= e($t['status']) ?></span><?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>
</section>
