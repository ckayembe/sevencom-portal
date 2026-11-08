<?php use function App\{e, url, contract_formulas, label}; ?>
<section>
  <h1>Nos formules</h1>
  <p class="lead">Chaque artiste avance à son rythme. <?= e(label()['name']) ?> propose six façons de travailler ensemble, de la production complète à un simple coup de pouce sur la promotion ou les visuels.</p>
  <div class="grid">
  <?php foreach (contract_formulas() as $f): ?>
    <div class="card formula">
      <span class="tag">Formule <?= $f['n'] ?></span>
      <h3><?= e($f['name']) ?></h3>
      <p><?= e($f['for']) ?>.</p>
      <dl>
        <dt>L'enregistrement</dt><dd>Financé par <?= e(preg_match('/^[A-Z]{2}/', $f['pays']) ? $f['pays'] : lcfirst($f['pays'])) ?></dd>
        <dt>Les masters</dt><dd>Appartiennent à <?= e(preg_match('/^[A-Z]{2}/', $f['masters']) ? $f['masters'] : lcfirst($f['masters'])) ?></dd>
        <dt>Engagement</dt><dd><?= e($f['duration']) ?></dd>
      </dl>
    </div>
  <?php endforeach; ?>
  </div>
  <p>Les formules se combinent, par exemple distribution et promotion pour un artiste autoproduit. Les conditions sont fixées par écrit avec chaque artiste.</p>
  <p><a class="btn" href="<?= e(url(null, '/contact')) ?>">Parlons de votre projet</a></p>
</section>
