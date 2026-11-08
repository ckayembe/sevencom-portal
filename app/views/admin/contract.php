<?php use function App\{e, url}; ?>
<section class="no-print">
  <p><a href="<?= e(url(null, '/admin/contrats')) ?>">← Tous les contrats</a></p>
  <form method="get" class="inline-form">
    <label>Artiste
      <select name="artiste" onchange="this.form.submit()">
        <option value="">Modèle vierge</option>
        <?php foreach ($artists as $a): ?><option value="<?= e($a['slug']) ?>"<?= ($artist['slug'] ?? '') === $a['slug'] ? ' selected' : '' ?>><?= e($a['name']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <button type="button" class="btn" onclick="window.print()">Imprimer / PDF</button>
  </form>
  <p class="notice">Cliquez dans le texte pour compléter les champs <mark>[surlignés]</mark> avant d'imprimer (les modifications ne sont pas enregistrées). Annexes A et C incluses<?= $withB ? ', ainsi que l\'annexe B (groupes et chorales)' : '' ?>. À faire relire par un avocat avant signature.</p>
</section>
<article class="doc contract" contenteditable="true" spellcheck="false">
  <p class="contract-brand"><img src="/assets/img/logo-mark.webp" alt=""> SEVEN.COM</p>
  <h1>Contrat <?= $n ?> : <?= e($title) ?></h1>
  <?= $html ?>
</article>
