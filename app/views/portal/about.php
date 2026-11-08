<?php use function App\{e, url, about, key_figures}; $ab = about(); $figs = key_figures(); ?>
<section class="hero about-hero">
  <p class="kicker">Qui sommes-nous</p>
  <h1 class="engraved">Le label Seven.com</h1>
  <p class="lead"><?= e($ab['intro']) ?></p>
</section>
<section class="two-col">
  <div><h2>Notre mission</h2><p><?= e($ab['mission']) ?></p></div>
  <div><h2>Notre vision</h2><p><?= e($ab['vision']) ?></p></div>
</section>
<section>
  <h2>Notre histoire</h2>
  <p class="lead"><?= nl2br(e($ab['story'])) ?></p>
</section>
<section>
  <h2>Nos valeurs</h2>
  <div class="grid">
  <?php foreach ($ab['values'] as $v): ?>
    <div class="card"><h3><?= e($v['title']) ?></h3><p><?= e($v['text']) ?></p></div>
  <?php endforeach; ?>
  </div>
</section>
<section>
  <h2>Comment nous travaillons</h2>
  <ol class="steps">
    <li><h3>Écouter</h3><p>Nous découvrons votre musique, votre message et votre parcours.</p></li>
    <li><h3>Construire</h3><p>Nous choisissons ensemble la formule : production, licence, distribution ou promotion.</p></li>
    <li><h3>Produire</h3><p>Enregistrement, mixage, visuels et clips avec nos équipes et partenaires.</p></li>
    <li><h3>Faire rayonner</h3><p>Sortie sur toutes les plateformes, promotion, booking et suivi des revenus.</p></li>
  </ol>
</section>
<?php if (count($figs) >= 2): ?>
<section class="figures">
  <?php foreach ($figs as $f): ?><div class="figure"><strong><?= e($f['value']) ?></strong><span><?= e($f['label']) ?></span></div><?php endforeach; ?>
</section>
<?php endif; ?>
<section><h2>L'histoire du son</h2><p>Du phonographe au streaming : découvrez en animation près de 150 ans de supports audio.</p><p><a class="btn ghost" href="<?= e(url(null, '/histoire-du-son')) ?>">Voyager dans le temps</a></p></section>
<section class="cta-band">
  <h2>Rejoindre l'aventure</h2>
  <p>Artiste, groupe, chorale, église ou partenaire : parlons de votre projet.</p>
  <p><a class="btn" href="<?= e(url(null, '/demo')) ?>">Soumettre une démo</a> <a class="btn ghost" href="<?= e(url(null, '/formules')) ?>">Voir nos formules</a></p>
</section>
