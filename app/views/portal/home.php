<?php use function App\{e, url, label, artists, tools, all_releases, all_videos, key_figures, contract_formulas, about}; $L = label();
$releases = array_slice(all_releases(), 0, 4); $videos = array_slice(all_videos(), 0, 2); $figs = key_figures(); ?>
<?php require __DIR__ . '/_audio_history.php'; ?>

<?php require __DIR__ . '/_carousel.php'; ?>

<section class="hero hero-after">
  <h1 class="visually-hidden"><?= e($L['name']) ?></h1>
  <p class="slogan"><?= e($L['tagline']) ?></p>
  <p class="rotator-line" aria-label="Nous produisons la louange, l'adoration, le gospel, les chorales">Nous produisons <span class="rotator" data-words="la louange|l'adoration|le gospel|les chorales|votre message">la louange</span></p>
  <p class="lead"><?= e($L['description']) ?></p>
  <p class="hero-actions"><a class="btn" href="<?= e(url(null, '/artistes')) ?>">Découvrir nos artistes</a>
     <a class="btn ghost" href="<?= e(url(null, '/demo')) ?>">Soumettre une démo</a></p>
</section>
<div class="marquee" aria-hidden="true"><div class="marquee-track">
  <?php $words = ['Louange', 'Adoration', 'Gospel', 'Chorales', 'Production', 'Clips', 'Promotion', 'Distribution', 'Booking'];
  for ($k = 0; $k < 2; $k++) foreach ($words as $w) echo '<span>' . e($w) . '</span>'; ?>
</div></div>

<section class="about-teaser two-col">
  <div>
    <p class="kicker">Qui sommes-nous</p>
    <h2>Une maison pour les artistes chrétiens</h2>
  </div>
  <div>
    <p><?= e(about()['intro']) ?></p>
    <p><a class="btn ghost" href="<?= e(url(null, '/a-propos')) ?>">Découvrir le label</a></p>
  </div>
</section>


<section class="cta-band">
  <h2>Votre louange mérite d'être entendue</h2>
  <p>Envoyez-nous vos titres : nous écoutons chaque démo et revenons vers vous.</p>
  <p><a class="btn pulse" href="<?= e(url(null, '/demo')) ?>">Soumettre une démo</a></p>
<div class="verse">
  <blockquote>« Chantez à l'Éternel un cantique nouveau ! Chantez à l'Éternel, vous tous, habitants de la terre ! »</blockquote>
  <cite>Psaume 96.1</cite>
</div>
</section>
