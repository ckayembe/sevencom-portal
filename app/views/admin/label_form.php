<?php use function App\{e, csrf_field};
$socialRows = []; foreach (($L['socials'] ?? []) as $n => $u) $socialRows[] = ['name' => $n, 'url' => $u]; ?>
<section>
  <h1>Label &amp; outils</h1>
  <form method="post" class="form wide">
    <?= csrf_field() ?>
    <fieldset><legend>Le label</legend>
      <label>Nom<input name="name" required value="<?= e($L['name'] ?? '') ?>"></label>
      <label>Sous-titre<input name="tagline" value="<?= e($L['tagline'] ?? '') ?>"></label>
      <label>Slogan<input name="slogan" value="<?= e($L['slogan'] ?? '') ?>"></label>
      <label>Présentation<textarea name="description" rows="4"><?= e($L['description'] ?? '') ?></textarea></label>
      <label>E-mail de contact<input type="email" name="email" value="<?= e($L['email'] ?? '') ?>"></label>
    </fieldset>
    <fieldset><legend>Réseaux sociaux du label</legend>
      <?php $name = 'socials'; $rows = $socialRows; $fields = ['name' => ['Réseau', 'text'], 'url' => ['Lien', 'text']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>
    <fieldset><legend>Chiffres clés (page d'accueil)</legend>
      <p class="small">Le nombre d'artistes, de sorties et de clips est calculé automatiquement. Ajoutez ici d'autres chiffres réels, par exemple « 150K+ » vues YouTube.</p>
      <?php $name = 'stats'; $rows = $L['stats'] ?? []; $fields = ['value' => ['Chiffre', 'text'], 'label' => ['Libellé', 'text']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>
    <fieldset><legend>Outils du label</legend>
      <?php $name = 'tools'; $rows = $tools; $fields = ['name' => ['Nom', 'text'], 'summary' => ['Description', 'textarea'], 'status' => ['Statut (Disponible, Bientôt…)', 'text'], 'url' => ['Lien', 'text'], 'cta' => ['Texte du bouton', 'text']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>
    <p><button class="btn">Enregistrer</button></p>
  </form>
</section>
<script src="/assets/js/admin.js"></script>
