<?php use function App\{e, url, csrf_field}; ?>
<section>
  <h1>Messages reçus</h1>
  <p class="lead">Démos, demandes de booking et partenariats envoyés depuis le formulaire du site.</p>
  <?php if (!$rows): ?><p>Aucun message pour l'instant.</p><?php endif; ?>
  <?php foreach ($rows as $r): $d = json_decode($r['data'], true); ?>
    <article class="card message<?= $r['handled'] ? ' handled' : '' ?>">
      <p><span class="tag"><?= e(App\SUBMISSION_KINDS[$r['kind']] ?? $r['kind']) ?></span> <span class="small"><?= e($r['created_at']) ?> UTC</span></p>
      <h3><?= e($d['name']) ?></h3>
      <p><a href="mailto:<?= e($d['email']) ?>"><?= e($d['email']) ?></a><?= $d['phone'] ? ' · ' . e($d['phone']) : '' ?><?= $d['city'] ? ' · ' . e($d['city']) : '' ?><?= !empty($d['artist_type']) ? ' · ' . e(App\KINDS[$d['artist_type']] ?? $d['artist_type']) : '' ?></p>
      <?php if ($d['links']): ?><ul class="small"><?php foreach (preg_split('/\s+/', $d['links']) as $l): if (!$l) continue; ?>
        <li><?php if (preg_match('#^https?://#', $l)): ?><a href="<?= e($l) ?>" rel="noopener noreferrer" target="_blank"><?= e($l) ?></a><?php else: ?><?= e($l) ?><?php endif; ?></li>
      <?php endforeach; ?></ul><?php endif; ?>
      <?php if ($d['message']): ?><p><?= nl2br(e($d['message'])) ?></p><?php endif; ?>
      <form method="post" action="<?= e(url(null, '/admin/messages/' . $r['id'] . '/traite')) ?>"><?= csrf_field() ?>
        <button class="btn ghost"><?= $r['handled'] ? 'Marquer non traité' : 'Marquer comme traité' ?></button></form>
    </article>
  <?php endforeach; ?>
</section>
