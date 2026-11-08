<?php use function App\{e, url, label, tools, promolien_releases, promolien_artist_page, date_fr}; $a = $artist;
// Sorties publiées par Udhibiti PromoLien (liens comptés « via Seven.com ») ; sinon celles de la fiche.
$plReleases = promolien_releases($a['slug']);
$plPage = $plReleases ? promolien_artist_page($a['slug']) : null;
$plLatest = null; foreach ($plReleases as $r) if (!$r['upcoming']) { $plLatest = $r; break; } ?>
<section class="hero artist-hero">
  <div class="artist-hero-text">
    <?php if (!empty($a['logo'])): ?><img class="artist-logo" src="<?= e($a['logo']) ?>" alt="Logo <?= e($a['name']) ?>"><?php endif; ?>
    <p class="kicker"><?= e(implode(' · ', array_filter([($a['kind'] ?? 'solo') !== 'solo' ? App\KINDS[$a['kind']] : '', $a['genre'] ?? '', $a['city'] ?? '']))) ?></p>
    <h1 class="engraved"><?= e($a['name']) ?></h1>
    <p class="lead"><?= e($a['tagline']) ?></p>
    <?php if ($plLatest): ?>
      <p><a class="btn" href="<?= e($plLatest['page']) ?>">Écouter « <?= e($plLatest['title']) ?> »</a></p>
    <?php elseif (!$plReleases && !empty($a['releases'][0]['links'])): $r = $a['releases'][0]; ?>
      <p><a class="btn" href="<?= e(reset($r['links'])) ?>">Écouter « <?= e($r['title']) ?> »</a></p>
    <?php endif; ?>
  </div>
  <img class="artist-photo" src="<?= e($a['photo']) ?>" alt="<?= e($a['name']) ?>">
</section>

<section>
  <h2>Biographie</h2>
  <?php foreach ($a['bio'] as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
</section>

<?php if (!empty($a['members'])): ?>
<section>
  <h2><?= ($a['kind'] ?? '') === 'chorale' ? 'La chorale' : 'Les membres' ?></h2>
  <ul class="members"><?php foreach ($a['members'] as $m): ?>
    <li><strong><?= e($m['name']) ?></strong><?php if ($m['role']): ?><br><span class="small"><?= e($m['role']) ?></span><?php endif; ?></li>
  <?php endforeach; ?></ul>
</section>
<?php endif; ?>

<?php if ($plReleases): ?>
<section class="pl-releases">
  <h2>Sorties</h2>
  <div class="grid">
  <?php foreach ($plReleases as $r): ?>
    <div class="card release">
      <?php if ($cv = $r['cover'] ?: ($a['photo'] ?? '')): ?><a href="<?= e($r['page']) ?>"><img src="<?= e($cv) ?>" alt="Pochette de « <?= e($r['title']) ?> »" loading="lazy"></a><?php endif; ?>
      <h3><?= e($r['title']) ?></h3>
      <?php if ($r['artist'] && $r['artist'] !== $a['name']): ?><p><?= e($r['artist']) ?></p><?php endif; ?>
      <p><?php if ($r['upcoming']): ?><span class="tag">Bientôt</span> <?php endif; ?><?= e(trim($r['kind'] . ($r['date'] ? ' · ' . date_fr($r['date']) : ''), ' ·')) ?></p>
      <?php if ($r['platforms']): ?><p class="links-inline"><?php foreach ($r['platforms'] as $pl): ?><a href="<?= e($pl['url']) ?>" rel="noopener"><?= e($pl['name']) ?></a><?php endforeach; ?></p><?php endif; ?>
      <p><a class="btn ghost" href="<?= e($r['page']) ?>" rel="noopener">Toutes les plateformes</a></p>
    </div>
  <?php endforeach; ?>
  </div>
  <?php if ($plPage): ?><p class="small">Liens d'écoute fournis par <a href="<?= e($plPage) ?>" rel="noopener">Udhibiti PromoLien</a>.</p><?php endif; ?>
</section>
<?php elseif (!empty($a['releases'])): ?>
<section>
  <h2>Sorties</h2>
  <div class="grid">
  <?php foreach ($a['releases'] as $r): ?>
    <div class="card">
      <?php if (!empty($r['cover'])): ?><img src="<?= e($r['cover']) ?>" alt="" loading="lazy"><?php endif; ?>
      <h3><?= e($r['title']) ?></h3>
      <p><?= e($r['type']) ?> · <?= e($r['date']) ?></p>
      <p class="links-inline"><?php foreach ($r['links'] as $n => $h): ?><a href="<?= e($h) ?>"><?= e($n) ?></a><?php endforeach; ?></p>
    </div>
  <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($a['videos'])): ?>
<section>
  <h2>Vidéos</h2>
  <div class="grid">
  <?php foreach ($a['videos'] as $v): ?>
    <div class="video"><iframe src="https://www.youtube-nocookie.com/embed/<?= e($v['youtube']) ?>" title="<?= e($v['title']) ?>" loading="lazy" allowfullscreen></iframe></div>
  <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($a['events'])): ?>
<section>
  <h2>Concerts</h2>
  <ul class="events"><?php foreach ($a['events'] as $ev): ?>
    <li><strong><?= e($ev['date']) ?></strong> <?= e($ev['venue']) ?>, <?= e($ev['city']) ?></li>
  <?php endforeach; ?></ul>
</section>
<?php endif; ?>

<section>
  <h2>Suivre <?= e($a['name']) ?></h2>
  <ul class="links"><?php foreach ($a['socials'] as $n => $h): ?><li><a href="<?= e($h) ?>"><?= e($n) ?></a></li><?php endforeach; ?></ul>
  <p>Booking : <a href="mailto:<?= e(($a['booking'] ?? '') ?: label()['email']) ?>"><?= e(($a['booking'] ?? '') ?: label()['email']) ?></a></p>
</section>

<section class="label-promo">
  <h2>Signé chez <?= e(label()['name']) ?></h2>
  <div class="grid">
  <?php foreach (array_slice(tools(), 0, 3) as $t): ?>
    <div class="card"><h3><?= e($t['name']) ?></h3><p><?= e($t['summary']) ?></p></div>
  <?php endforeach; ?>
  </div>
  <p><a class="btn ghost" href="<?= e(url()) ?>">Découvrir le label</a></p>
</section>
