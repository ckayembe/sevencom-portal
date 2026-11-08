<?php use function App\{e, csrf_field};
$socialRows = []; foreach (($L['socials'] ?? []) as $n => $u) $socialRows[] = ['name' => $n, 'url' => $u]; ?>
<section>
  <h1>Label &amp; outils</h1>
  <form method="post" enctype="multipart/form-data" class="form wide">
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
    <fieldset><legend>Diaporama 3D de l'accueil</legend>
      <p class="small">Vos diapos passent en premier (affiche d'un concert, nouvel album…). Le diaporama ajoute ensuite automatiquement le label, les artistes, la dernière sortie, le dernier clip et les services. Image idéale : format paysage 1600 × 1000.</p>
      <?php $name = 'slides'; $rows = array_map(fn($x) => $x + ['old_image' => $x['image'] ?? ''], $L['slides'] ?? []);
      $fields = ['image' => ['Image', 'file'], 'old_image' => ['', 'hidden'], 'title' => ['Titre', 'text'], 'text' => ['Sous-titre', 'text'], 'link' => ['Lien (ex. /sorties ou https://…)', 'text']]; require __DIR__ . '/_rows.php'; ?>
    </fieldset>
    <?php $ab = App\about(); ?>
    <fieldset><legend>Qui sommes-nous (page « Le label »)</legend>
      <label>Présentation courte<textarea name="about_intro" rows="3"><?= e($ab['intro']) ?></textarea></label>
      <label>Notre mission<textarea name="about_mission" rows="3"><?= e($ab['mission']) ?></textarea></label>
      <label>Notre vision<textarea name="about_vision" rows="3"><?= e($ab['vision']) ?></textarea></label>
      <label>Notre histoire<textarea name="about_story" rows="5"><?= e($ab['story']) ?></textarea></label>
      <p class="small">Nos valeurs</p>
      <?php $name = 'values'; $rows = $ab['values']; $fields = ['title' => ['Valeur', 'text'], 'text' => ['Explication', 'textarea']]; require __DIR__ . '/_rows.php'; ?>
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
