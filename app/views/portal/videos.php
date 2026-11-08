<?php use function App\{e, url, all_videos}; $list = all_videos(); ?>
<section>
  <h1>Clips &amp; visuels</h1>
  <p class="lead">Chez Seven.com, le son et l'image avancent ensemble : clips, lyric videos et captations live de nos artistes.</p>
  <?php if ($list): ?>
  <div class="grid clips"><?php foreach ($list as $v) require __DIR__ . '/_video.php'; ?></div>
  <?php else: ?><p>Les premiers clips arrivent bientôt.</p><?php endif; ?>
  <p><a class="btn" href="<?= e(url(null, '/demo?objet=autre')) ?>">Un projet de clip ? Parlons-en</a></p>
</section>
