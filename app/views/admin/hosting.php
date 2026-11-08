<?php use function App\{e, url, csrf_field, config};
$enabled = $c['token'] !== '' && $c['username'] !== '';
$labels = ['done' => 'Active', 'ssl_pending' => 'SSL en attente', 'error_subdomain' => 'Erreur', 'error_ssl' => 'Erreur SSL', 'new' => 'En cours', 'manual' => 'À activer']; ?>
<section>
  <h1>Hébergement</h1>
  <p class="lead">Activation automatique des adresses <strong>artiste.<?= e(config()['domain']) ?></strong> : à la création d'un artiste, le site déclare son sous-domaine sur Hostinger et demande le cadenas SSL tout seul.</p>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

  <div class="notice">
    <p><strong>À faire une seule fois chez Infomaniak</strong> (Domaines › <?= e(config()['domain']) ?> › Zone DNS) : une entrée de type <strong>A</strong>, source <code>*</code>, cible <code><?= e($c['ip'] ?: '147.93.93.251') ?></code>. Elle couvre tous les artistes, présents et futurs. Ne touchez pas aux entrées MX et TXT (la messagerie).</p>
  </div>

  <form method="post" class="form wide">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <fieldset><legend>Clé d'API Hostinger</legend>
      <p class="small">Créez-la dans hPanel › Profil › <a href="https://hpanel.hostinger.com/profile/api" target="_blank" rel="noopener">API</a> (bouton « Générer un jeton »), puis collez-la ici. Elle est rangée hors du site, n'est jamais réaffichée et n'est utilisée que pour ajouter les sous-domaines et leurs certificats SSL.</p>
      <label>Clé d'API <?= $enabled ? '<span class="small">(enregistrée — laissez vide pour la garder)</span>' : '' ?>
        <input type="password" name="token" autocomplete="off" <?= $enabled ? '' : 'required' ?>></label>
      <div class="two">
        <label>Identifiant d'hébergement<input name="username" required pattern="u[0-9]+" value="<?= e($c['username'] ?: 'u548989100') ?>"></label>
        <label>Adresse IP du serveur<input name="ip" value="<?= e($c['ip'] ?: '147.93.93.251') ?>"></label>
      </div>
      <p><button class="btn"><?= $enabled ? 'Mettre à jour' : 'Activer' ?></button></p>
    </fieldset>
  </form>
  <?php if ($enabled): ?>
  <form method="post" onsubmit="return confirm('Supprimer la clé et arrêter l\'activation automatique ?')"><?= csrf_field() ?>
    <input type="hidden" name="action" value="remove"><button class="linkbtn">Supprimer la clé</button></form>
  <?php endif; ?>

  <h2>Adresses des artistes</h2>
  <table class="table">
    <thead><tr><th>Artiste</th><th>Adresse</th><th>État</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($artists as $a): $st = $states[$a['slug']] ?? null;
      $onHost = is_array($remote) && in_array($a['slug'], $remote, true);
      $step = $st['step'] ?? ($onHost ? 'done' : 'manual'); ?>
      <tr>
        <td><?= e($a['name']) ?></td>
        <td><a href="<?= e(url($a['slug'])) ?>" target="_blank"><?= e($a['slug']) ?>.<?= e(config()['domain']) ?></a></td>
        <td><span class="tag"><?= e($labels[$step] ?? $step) ?></span><?php if (!empty($st['message']) && $step !== 'done'): ?><br><span class="small"><?= e($st['message']) ?></span><?php endif; ?></td>
        <td><?php if ($enabled && $step !== 'done'): ?>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="provision"><input type="hidden" name="slug" value="<?= e($a['slug']) ?>">
            <button class="linkbtn"><?= $step === 'manual' ? 'Activer' : 'Réessayer' ?></button></form>
        <?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
