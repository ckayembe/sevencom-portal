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
    <p>Pour l'adresse <strong><?= e($artist['slug']) ?>.<?= e(App\config()['domain']) ?></strong>, il faut encore activer le sous-domaine :
       sur Hostinger (sous-domaine pointant vers le même dossier) et chez Infomaniak (enregistrement <code>A <?= e($artist['slug']) ?> → 147.93.93.251</code>).</p>
    <p><a class="btn" href="<?= e(url(null, '/admin/artistes/' . $artist['slug'])) ?>">Compléter sa fiche</a> <a class="btn ghost" href="<?= e(url(null, '/admin')) ?>">Retour</a></p>
  <?php else: ?>
    <p><a class="btn ghost" href="<?= e(url(null, '/admin')) ?>">Retour</a></p>
  <?php endif; ?>
</section>
