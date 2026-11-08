<?php use function App\{e, url, label, tools}; $a = $artist; ?>
<section class="hero artist-hero" style="background-image:linear-gradient(180deg,rgba(20,50,74,.55),rgba(20,50,74,.9)),url('<?= e($a['cover'] ?? $a['photo']) ?>')">
  <p class="kicker"><?= e($a['genre']) ?> · <?= e($a['city']) ?></p>
  <h1><?= e($a['name']) ?></h1>
  <p class="lead"><?= e($a['tagline']) ?></p>
  <?php if (!empty($a['releases'][0]['links'])): $r = $a['releases'][0]; ?>
    <p><a class="btn" href="<?= e(reset($r['links'])) ?>">Écouter « <?= e($r['title']) ?> »</a></p>
  <?php endif; ?>
</section>

<section>
  <h2>Biographie</h2>
  <?php foreach ($a['bio'] as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
</section>

<?php if (!empty($a['releases'])): ?>
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
  <p>Booking : <a href="mailto:<?= e($a['booking'] ?? label()['email']) ?>"><?= e($a['booking'] ?? label()['email']) ?></a></p>
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
