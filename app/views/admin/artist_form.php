<?php use function App\{e, url, csrf_field, is_admin}; use const App\LINK_FIELDS;
$releaseRows = array_map(function ($r) {
    $row = ['title' => $r['title'] ?? '', 'type' => $r['type'] ?? '', 'date' => $r['date'] ?? '', 'old_cover' => $r['cover'] ?? ''];
    foreach (LINK_FIELDS as $k => $label) $row[$k] = $r['links'][$label] ?? '';
    // anciens libellés éventuels
    foreach (($r['links'] ?? []) as $label => $u) if (!in_array($label, LINK_FIELDS, true) && !$row['autre']) $row['autre'] = $u;
    return $row;
}, $a['releases'] ?? []);
$socialRows = []; foreach (($a['socials'] ?? []) as $n => $u) $socialRows[] = ['name' => $n, 'url' => $u];
$videoRows = array_map(fn($v) => ['title' => $v['title'], 'url' => 'https://youtu.be/' . $v['youtube']], $a['videos'] ?? []);
?>
<section>
  <h1><?= $isNew ? 'Nouvel artiste' : e($a['name']) ?></h1>
  <?php if (!$isNew): ?><p><a href="<?= e(url($a['slug'])) ?>" target="_blank">Voir son site →</a></p><?php endif; ?>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="form wide">
    <?= csrf_field() ?>

    <?php if ($isNew): ?>
    <fieldset><legend>Compte et adresse</legend>
      <label>Sous-domaine <span class="small">(ex. nadia → nadia.<?= e(App\config()['domain']) ?>)</span>
        <input name="slug" required pattern="[a-z0-9-]+" value="<?= e($_POST['slug'] ?? '') ?>"></label>
      <label>E-mail du compte de l'artiste<input type="email" name="account_email" required value="<?= e($_POST['account_email'] ?? '') ?>"></label>
    </fieldset>
    <?php endif; ?>

    <fieldset><legend>Identité</legend>
      <label>Nom d'artiste<input name="name" required value="<?= e($a['name'] ?? '') ?>"></label>
      <label>Phrase d'accroche<input name="tagline" value="<?= e($a['tagline'] ?? '') ?>"></label>
      <div class="two">
        <label>Style musical<input name="genre" value="<?= e($a['genre'] ?? '') ?>"></label>
        <label>Ville<input name="city" value="<?= e($a['city'] ?? '') ?>"></label>
      </div>
      <div class="two">
        <label>E-mail de booking<input type="email" name="booking" value="<?= e($a['booking'] ?? '') ?>"></label>
        <label>Couleur du site<input type="color" name="accent" value="<?= e($a['theme']['accent'] ?? '#2c6e9e') ?>"></label>
      </div>
      <label class="file">Photo <span class="small">(JPG, PNG ou WebP, 8 Mo max.)</span><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
      <?php if (!empty($a['photo'])): ?><img class="thumb" src="<?= e($a['photo']) ?>" alt=""><?php endif; ?>
    </fieldset>

    <fieldset><legend>Biographie</legend>
      <label>Laissez une ligne vide entre deux paragraphes.
        <textarea name="bio" rows="8"><?= e(implode("\n\n", $a['bio'] ?? [])) ?></textarea></label>
    </fieldset>

    <fieldset><legend>Sorties (singles, albums)</legend>
      <?php $name = 'releases'; $rows = $releaseRows; $fields = [
        'title' => ['Titre', 'text'], 'type' => ['Type (Single, EP, Album)', 'text'], 'date' => ['Date', 'text'],
        'cover' => ['Pochette', 'file'], 'old_cover' => ['', 'hidden'],
        'spotify' => ['Lien Spotify', 'url'], 'apple' => ['Lien Apple Music', 'url'], 'deezer' => ['Lien Deezer', 'url'],
        'youtube' => ['Lien YouTube', 'url'], 'autre' => ['Autre lien', 'url']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>

    <fieldset><legend>Vidéos YouTube</legend>
      <?php $name = 'videos'; $rows = $videoRows; $fields = ['title' => ['Titre', 'text'], 'url' => ['Lien YouTube', 'text']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>

    <fieldset><legend>Concerts</legend>
      <?php $name = 'events'; $rows = $a['events'] ?? []; $fields = ['date' => ['Date', 'text'], 'venue' => ['Lieu', 'text'], 'city' => ['Ville', 'text']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>

    <fieldset><legend>Réseaux sociaux</legend>
      <?php $name = 'socials'; $rows = $socialRows; $fields = ['name' => ['Réseau (Instagram, TikTok…)', 'text'], 'url' => ['Lien', 'text']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>

    <?php if (is_admin()): ?>
    <fieldset><legend>Réglages du label</legend>
      <label class="check"><input type="checkbox" name="published" value="1" <?= ($a['published'] ?? true) ? 'checked' : '' ?>> Site en ligne</label>
      <label>Ordre d'affichage<input type="number" name="order" value="<?= e((string)($a['order'] ?? 99)) ?>"></label>
    </fieldset>
    <?php endif; ?>

    <p><button class="btn"><?= $isNew ? 'Créer l\'artiste et son compte' : 'Enregistrer' ?></button></p>
  </form>
</section>
<script src="/assets/js/admin.js"></script>
