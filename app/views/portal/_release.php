<?php use function App\{e, url}; /** @var array $r */ ?>
<a class="card release" href="<?= e(url($r['artist']['slug'])) ?>">
  <?php if (!empty($r['cover'])): ?><img src="<?= e($r['cover']) ?>" alt="" loading="lazy"><?php endif; ?>
  <h3><?= e($r['title']) ?></h3>
  <p><?= e($r['artist']['name']) ?></p>
  <span class="small"><?= e(trim(($r['type'] ?? '') . ' · ' . ($r['date'] ?? ''), ' ·')) ?></span>
</a>
