<?php use function App\{e, url, plan_progress, days_label, date_fr, task_due, today, is_admin};
$names = array_map(fn($a) => $a['name'], App\all_artists(true));
$today = today();
$upcoming = array_filter($plans, fn($p) => $p['date'] >= App\add_days($today, -30));
$past = array_filter($plans, fn($p) => $p['date'] < App\add_days($today, -30));
// Toutes les prochaines échéances, tous plannings confondus
$todo = [];
foreach ($upcoming as $p) foreach ($p['tasks'] as $t) if (empty($t['done'])) { $d = task_due($p, $t); if ($d <= App\add_days($today, 14)) $todo[] = ['due' => $d, 't' => $t, 'p' => $p]; }
usort($todo, fn($a, $b) => strcmp($a['due'], $b['due']));
?>
<section>
  <h1>Planning des sorties</h1>
  <p class="lead">Un rétroplanning par sortie : choisissez la date, les étapes (master, pochette, distribution, pré-sauvegarde, promo…) se calculent toutes seules.</p>
  <p><a class="btn" href="<?= e(url(null, '/admin/planning/nouveau')) ?>">+ Planifier une sortie</a></p>

  <?php if ($todo): ?>
  <h2>À faire ces 15 prochains jours</h2>
  <ul class="plan-todo">
    <?php foreach (array_slice($todo, 0, 12) as $x): $late = $x['due'] < $today; ?>
      <li class="<?= $late ? 'late' : '' ?>">
        <span class="due"><?= $late ? 'En retard · ' : '' ?><?= e(date_fr($x['due'])) ?></span>
        <a href="<?= e(url(null, '/admin/planning/' . $x['p']['id'])) ?>"><?= e($x['t']['label']) ?></a>
        <span class="small"><?= e(trim(($names[$x['p']['artist_slug']] ?? '') . ' · ' . $x['p']['title'], ' ·')) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>

  <h2>Sorties prévues</h2>
  <?php if (!$upcoming): ?><p>Aucune sortie planifiée pour l'instant.</p><?php endif; ?>
  <div class="grid">
  <?php foreach ($upcoming as $p): [$done, $total, $late, $next] = plan_progress($p); $pct = $total ? round($done * 100 / $total) : 0; ?>
    <a class="card plan-card" href="<?= e(url(null, '/admin/planning/' . $p['id'])) ?>">
      <span class="tag"><?= e($p['type']) ?></span>
      <h3><?= e($p['title']) ?></h3>
      <p><?= e($names[$p['artist_slug']] ?? 'Label') ?></p>
      <p><strong><?= e(date_fr($p['date'])) ?></strong> <span class="small">(<?= e(days_label($p['date'])) ?>)</span></p>
      <div class="progress" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?= $pct ?>%"></span></div>
      <p class="small"><?= $done ?>/<?= $total ?> étapes faites<?= $late ? ' · <strong class="late">' . $late . ' en retard</strong>' : '' ?></p>
      <?php if ($next): ?><p class="small">Prochaine : <?= e($next['label']) ?> (<?= e(date_fr($next['due'])) ?>)</p><?php endif; ?>
    </a>
  <?php endforeach; ?>
  </div>

  <?php if ($past): ?>
  <h2>Sorties passées</h2>
  <table class="table">
    <thead><tr><th>Sortie</th><th>Artiste</th><th>Date</th><th>Étapes</th></tr></thead>
    <tbody>
    <?php foreach (array_reverse($past) as $p): [$done, $total] = plan_progress($p); ?>
      <tr><td><a href="<?= e(url(null, '/admin/planning/' . $p['id'])) ?>"><?= e($p['title']) ?></a></td>
        <td><?= e($names[$p['artist_slug']] ?? 'Label') ?></td><td><?= e(date_fr($p['date'])) ?></td><td><?= $done ?>/<?= $total ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
