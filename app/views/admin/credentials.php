<?php use function App\{e, url}; ?>
<section class="narrow">
  <h1><?= $artist ? 'Artiste créé' : 'Nouveau mot de passe' ?></h1>
  <p class="lead">Transmettez ces accès à <?= e($user['name']) ?>. Le mot de passe ne sera plus affiché ensuite ; il devra le changer à sa première connexion.</p>
  <div class="card credentials">
    <p><strong>Adresse :</strong> <?= e(url(null, '/admin')) ?></p>
    <p><strong>E-mail :</strong> <?= e($user['email']) ?></p>
    <p><strong>Mot de passe provisoire :</strong> <code><?= e($password) ?></code></p>
  </div>
  <?php if ($artist): ?>
    <h2>Site de l'artiste</h2>
    <p>Il est déjà visible ici : <a href="<?= e(url(null, '/artistes/' . $artist['slug'])) ?>"><?= e(url(null, '/artistes/' . $artist['slug'])) ?></a></p>
    <?php $site ??= ['step' => 'manual', 'message' => '']; ?>
    <?php if ($site['step'] === 'done'): ?>
      <p>Son adresse <a href="<?= e(url($artist['slug'])) ?>"><strong><?= e($artist['slug']) ?>.<?= e(App\config()['domain']) ?></strong></a> est activée. Le cadenas SSL arrive dans quelques minutes.</p>
    <?php elseif ($site['step'] === 'ssl_pending'): ?>
      <p>L'adresse <strong><?= e($artist['slug']) ?>.<?= e(App\config()['domain']) ?></strong> est créée sur Hostinger. <?= e($site['message']) ?> Le site s'en occupe tout seul.</p>
    <?php elseif ($site['step'] === 'manual'): ?>
      <p>Pour l'adresse <strong><?= e($artist['slug']) ?>.<?= e(App\config()['domain']) ?></strong>, activez une fois l'<a href="<?= e(url(null, '/admin/hebergement')) ?>">activation automatique</a> : ensuite, chaque nouvel artiste aura son adresse tout seul.</p>
    <?php else: ?>
      <p class="error"><?= e($site['message']) ?> <a href="<?= e(url(null, '/admin/hebergement')) ?>">Voir Hébergement</a></p>
    <?php endif; ?>
    <p><a class="btn" href="<?= e(url(null, '/admin/artistes/' . $artist['slug'])) ?>">Compléter sa fiche</a> <a class="btn ghost" href="<?= e(url(null, '/admin')) ?>">Retour</a></p>
  <?php else: ?>
    <p><a class="btn ghost" href="<?= e(url(null, '/admin')) ?>">Retour</a></p>
  <?php endif; ?>
</section>
