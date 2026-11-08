<?php use function App\{e, slides}; $sl = slides(); $n = count($sl); ?>
<section class="carousel3d" aria-roledescription="carrousel" aria-label="À la une">
  <div class="c3d-stage"><div class="c3d-world">
    <div class="c3d-fx" aria-hidden="true">
      <?php mt_srand(7); for ($k = 0; $k < 14; $k++):
        $cls = ['note', 'dot'][$k % 2]; $ang = $k * 137.5; $rad = mt_rand(18, 46);
        $x = round(cos(deg2rad($ang)) * $rad * 1.6) . 'vw'; $y = round(sin(deg2rad($ang)) * $rad * .9) . 'vh';
        $st = sprintf('--x:%s;--y:%s;--s:%dpx;--d:%.1fs;--w:-%.1fs;--r:%ddeg', $x, $y, $cls === 'dot' ? mt_rand(10, 22) : mt_rand(28, 54), mt_rand(70, 120) / 10, $k * 0.75, mt_rand(-40, 40)); ?>
        <i class="<?= $cls ?>" style="<?= $st ?>"><?= $cls === 'note' ? ['♪', '♫', '♬'][intdiv($k, 2) % 3] : '' ?></i>
      <?php endfor; ?>
    </div>
      <?php foreach ($sl as $i => $s): ?>
        <a class="c3d-slide<?= $s['image'] ? '' : ' no-img' ?>" href="<?= e($s['link'] ?? '#') ?>" style="--i:<?= $i ?>" aria-label="<?= e($s['title']) ?>" data-i="<?= $i ?>">
          <?php if ($s['image']): ?><img src="<?= e($s['image']) ?>" alt="" class="<?= ($s['fit'] ?? '') === 'contain' ? 'contain' : '' ?>" loading="<?= $i < 3 ? 'eager' : 'lazy' ?>" draggable="false"><?php endif; ?>
          <span class="c3d-text"><span class="c3d-kicker"><?= e($s['kicker'] ?? '') ?></span><strong><?= e($s['title']) ?></strong><?php if (!empty($s['text'])): ?><em><?= e($s['text']) ?></em><?php endif; ?></span>
        </a>
      <?php endforeach; ?>
  </div></div>
  <div class="c3d-controls">
    <button type="button" class="c3d-prev" aria-label="Diapo précédente">‹</button>
    <button type="button" class="c3d-next" aria-label="Diapo suivante">›</button>
  </div>
</section>
