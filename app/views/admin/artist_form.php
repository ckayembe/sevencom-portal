<?php use function App\{e, url, csrf_field, is_admin}; use const App\{LINK_FIELDS, KINDS};
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
  <?php if (!empty($notice)): ?><p class="flash"><?= e($notice) ?></p><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="form wide">
    <?= csrf_field() ?>
    <input type="hidden" name="photo_current" value="<?= e($a['photo'] ?? '') ?>">
    <input type="hidden" name="logo_current" value="<?= e($a['logo'] ?? '') ?>">

    <fieldset class="import"><legend>Pré-remplir depuis des liens</legend>
      <label>Collez les liens existants, un par ligne : chaîne YouTube, page Spotify, Facebook, Instagram, site web…
        <textarea name="import_links" rows="3" placeholder="https://www.youtube.com/@nom-de-la-chaine&#10;https://open.spotify.com/artist/..."></textarea></label>
      <p class="small">On récupère le nom, la description, le logo, la photo de bannière et les dernières vidéos. Rien n'est enregistré avant que vous cliquiez sur « <?= $isNew ? 'Créer' : 'Enregistrer' ?> ».</p>
      <button class="btn ghost" name="action" value="import" formnovalidate>Importer les infos</button>
    </fieldset>

    <?php if ($isNew): ?>
    <fieldset><legend>Compte et adresse</legend>
      <label>Sous-domaine <span class="small">(ex. nadia → nadia.<?= e(App\config()['domain']) ?>)</span>
        <input name="slug" required pattern="[a-z0-9-]+" value="<?= e($_POST['slug'] ?? '') ?>"></label>
      <label>E-mail du compte de l'artiste<input type="email" name="account_email" required value="<?= e($_POST['account_email'] ?? '') ?>"></label>
    </fieldset>
    <?php endif; ?>

    <fieldset><legend>Identité</legend>
      <div class="two">
        <label>Type
          <select name="kind" id="kind">
            <?php foreach (KINDS as $k => $label): ?><option value="<?= e($k) ?>" <?= ($a['kind'] ?? 'solo') === $k ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
          </select></label>
        <label>Nom <span class="small">(de l'artiste, du groupe ou de la chorale)</span><input name="name" required value="<?= e($a['name'] ?? '') ?>"></label>
      </div>
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
      <label class="file">Logo <span class="small">(facultatif, idéal pour un groupe ou une chorale)</span><input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
      <?php if (!empty($a['logo'])): ?><img class="thumb logo" src="<?= e($a['logo']) ?>" alt=""><label class="check"><input type="checkbox" name="logo_remove" value="1"> Retirer le logo</label><?php endif; ?>
    </fieldset>

    <fieldset id="members-block"><legend>Membres</legend>
      <p class="small">Pour un groupe ou une chorale : membres, chef de chœur, musiciens…</p>
      <?php $name = 'members'; $rows = $a['members'] ?? []; $fields = ['name' => ['Nom', 'text'], 'role' => ['Rôle (voix, instrument, direction…)', 'text']]; require __DIR__ . '/_rows.php'; ?>
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

    <fieldset><legend>Réseaux sociaux et pages d'écoute</legend>
      <p class="small">Mettez l'adresse complète de la page de l'artiste (pas l'accueil de la plateforme). <strong>Deezer et Apple Music</strong> sont les plus importantes : Udhibiti PromoLien y repère chaque nouvelle sortie et crée son lien d'écoute tout seul. Spotify, YouTube, Boomplay et Audiomack aident aussi.</p>
      <?php $name = 'socials'; $rows = $socialRows; $fields = ['name' => ['Réseau (Instagram, TikTok…)', 'text'], 'url' => ['Lien', 'text']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>

    <?php if (is_admin()): ?>
    <fieldset><legend>Réglages du label</legend>
      <label class="check"><input type="checkbox" name="published" value="1" <?= ($a['published'] ?? true) ? 'checked' : '' ?>> Site en ligne</label>
      <label>Ordre d'affichage<input type="number" name="order" value="<?= e((string)($a['order'] ?? 99)) ?>"></label>
    </fieldset>
    <?php endif; ?>

    <p><button class="btn" name="action" value="save"><?= $isNew ? 'Créer l\'artiste et son compte' : 'Enregistrer' ?></button></p>
  </form>
</section>
<script src="/assets/js/admin.js"></script>
