<?php use function App\{e, url, csrf_field}; ?>
<section>
  <h1>Artistes</h1>
  <p><a class="btn" href="<?= e(url(null, '/admin/artistes/nouveau')) ?>">+ Nouvel artiste</a></p>
  <table class="table">
    <thead><tr><th>Artiste</th><th>Site</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($artists as $a): ?>
      <tr>
        <td><strong><?= e($a['name']) ?></strong></td>
        <td><a href="<?= e(url($a['slug'])) ?>"><?= e($a['slug']) ?>.<?= e(App\config()['domain']) ?></a></td>
        <td><span class="tag"><?= $a['published'] ? 'En ligne' : 'Masqué' ?></span></td>
        <td><a href="<?= e(url(null, '/admin/artistes/' . $a['slug'])) ?>">Modifier</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
<section>
  <h2>Comptes</h2>
  <table class="table">
    <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Dernière connexion</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><?= $u['role'] === 'admin' ? 'Administrateur' : 'Artiste (' . e($u['artist_slug']) . ')' ?></td>
        <td class="small"><?= e($u['last_login_at'] ?? 'jamais') ?></td>
        <td><?php if ($u['id'] != App\current_user()['id']): ?>
          <form method="post" action="<?= e(url(null, '/admin/comptes/' . $u['id'] . '/nouveau-mot-de-passe')) ?>" onsubmit="return confirm('Générer un nouveau mot de passe pour <?= e($u['name']) ?> ?')">
            <?= csrf_field() ?><button class="linkbtn">Nouveau mot de passe</button>
          </form><?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
