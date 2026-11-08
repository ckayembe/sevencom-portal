<?php use function App\{e, url, md}; ?>
<section>
  <h1>Contrats</h1>
  <p class="lead">Six modèles selon la formule choisie avec l'artiste. Choisissez un artiste pour pré-remplir son nom, puis complétez les champs surlignés et imprimez ou enregistrez en PDF.</p>
  <p class="notice">Modèles de travail en droit congolais (RDC, OHADA) : à faire relire par un avocat inscrit au barreau avant toute signature.</p>
  <div class="grid">
  <?php foreach ($c['contracts'] as $k): ?>
    <form class="card" method="get" action="<?= e(url(null, '/admin/contrats/' . $k['n'])) ?>">
      <span class="tag">Contrat <?= $k['n'] ?></span>
      <h3><?= e(ucfirst($k['title'])) ?></h3>
      <p class="small"><?= e($k['summary']) ?></p>
      <label>Pour l'artiste
        <select name="artiste">
          <option value="">Modèle vierge</option>
          <?php foreach ($artists as $a): ?><option value="<?= e($a['slug']) ?>"><?= e($a['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <p><button class="btn">Ouvrir le contrat</button></p>
    </form>
  <?php endforeach; ?>
  </div>
</section>
<section class="doc">
  <h2>Comparatif des formules</h2>
  <?= md($c['compare']) ?>
  <h2>Mode d'emploi</h2>
  <?= md($c['guide']) ?>
</section>
