<?php use function App\{e, url, label, artists, tools, all_releases, all_videos, key_figures, contract_formulas, about}; $L = label();
$releases = array_slice(all_releases(), 0, 4); $videos = array_slice(all_videos(), 0, 2); $figs = key_figures(); ?>
<section class="hero">
  <h1 class="visually-hidden"><?= e($L['name']) ?></h1>
  <div class="hero-logo-wrap" style="--logo:url('<?= e($L['logo']) ?>')">
    <img class="hero-logo" src="<?= e($L['logo']) ?>" alt="<?= e($L['name']) ?> · <?= e($L['slogan']) ?>">
    <span class="sheen" aria-hidden="true"></span>
  </div>
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

<?php if ($releases): ?>
<section>
  <h2>Nos sorties</h2>
  <p>Les dernières sorties de nos artistes, sur toutes les plateformes.</p>
  <div class="grid releases"><?php foreach ($releases as $r) require __DIR__ . '/_release.php'; ?></div>
  <p><a href="<?= e(url(null, '/sorties')) ?>">Toutes les sorties →</a></p>
</section>
<?php endif; ?>

<?php if (count($figs) >= 2): ?>
<section class="figures">
  <h2 class="visually-hidden">Le label en chiffres</h2>
  <?php foreach ($figs as $f): ?>
    <div class="figure"><strong><?= e($f['value']) ?></strong><span><?= e($f['label']) ?></span></div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<section>
  <h2>Artistes</h2>
  <div class="grid">
  <?php foreach (artists() as $a): ?>
    <a class="card" href="<?= e(url($a['slug'])) ?>" style="--accent:<?= e($a['theme']['accent'] ?? '') ?>">
      <img src="<?= e($a['photo']) ?>" alt="" loading="lazy">
      <h3><?= e($a['name']) ?><?php if (($a['kind'] ?? 'solo') !== 'solo'): ?> <span class="tag kind-tag"><?= e(App\KINDS[$a['kind']]) ?></span><?php endif; ?></h3>
      <p><?= e($a['tagline']) ?></p>
      <span class="small"><?= e($a['slug']) ?>.<?= e(App\config()['domain']) ?> →</span>
    </a>
  <?php endforeach; ?>
  </div>
</section>

<?php if ($videos): ?>
<section>
  <h2>Clips &amp; visuels</h2>
  <p>Le son et l'image avancent ensemble : clips, lyric videos et captations live.</p>
  <div class="grid clips"><?php foreach ($videos as $v) require __DIR__ . '/_video.php'; ?></div>
  <p><a href="<?= e(url(null, '/clips')) ?>">Tous les clips →</a></p>
</section>
<?php endif; ?>

<section>
  <h2>Services du label</h2>
  <p>De la production complète à un coup de pouce sur la promotion : chaque artiste choisit la formule qui lui correspond.</p>
  <div class="grid services">
  <?php foreach (contract_formulas() as $f): ?>
    <a class="card" href="<?= e(url(null, '/formules')) ?>"><span class="tag">0<?= $f['n'] ?></span><h3><?= e($f['name']) ?></h3><p><?= e($f['for']) ?>.</p></a>
  <?php endforeach; ?>
  </div>
  <p><a href="<?= e(url(null, '/formules')) ?>">Comparer les formules →</a> · <a href="<?= e(url(null, '/demo?objet=booking')) ?>">Booking d'un artiste →</a></p>
</section>

<section>
  <h2>Outils du label</h2>
  <div class="grid">
  <?php foreach (array_slice(tools(), 0, 3) as $t): ?>
    <div class="card"><h3><?= e($t['name']) ?></h3><p><?= e($t['summary']) ?></p></div>
  <?php endforeach; ?>
  </div>
  <p><a href="<?= e(url(null, '/outils')) ?>">Voir tous les outils →</a></p>
</section>

<section class="verse">
  <blockquote>« Chantez à l'Éternel un cantique nouveau ! Chantez à l'Éternel, vous tous, habitants de la terre ! »</blockquote>
  <cite>Psaume 96.1</cite>
</section>

<section class="cta-band">
  <h2>Vous servez Dieu par la musique ?</h2>
  <p>Envoyez-nous vos titres : nous écoutons chaque démo et revenons vers vous.</p>
  <p><a class="btn pulse" href="<?= e(url(null, '/demo')) ?>">Soumettre une démo</a></p>
</section>
