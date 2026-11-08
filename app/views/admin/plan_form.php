<?php use function App\{e, url, csrf_field, is_admin, task_due, offset_label, date_fr, days_label, plan_progress, today};
use const App\PLAN_TYPES;
$isNew = empty($p['id']);
$today = today();
[$done, $total, $late] = plan_progress($p);
$hasRelease = false;
if ($p['artist_slug'] && ($a = App\all_artists(true)[$p['artist_slug']] ?? null))
    foreach ($a['releases'] ?? [] as $r) if (mb_strtolower($r['title'] ?? '') === mb_strtolower($p['title'])) $hasRelease = true;
$taskRow = function (array $t, string $due = '') use ($today) {
    $isDone = !empty($t['done']); $late = !$isDone && $due !== '' && $due < $today; ?>
  <div class="task<?= $isDone ? ' done' : '' ?><?= $late ? ' late' : '' ?>">
    <label class="check"><input type="hidden" name="tasks[done][]" value="<?= $isDone ? '1' : '0' ?>"><input type="checkbox" class="done-toggle"<?= $isDone ? ' checked' : '' ?> aria-label="Fait"></label>
    <div class="task-main">
      <input name="tasks[label][]" value="<?= e($t['label'] ?? '') ?>" placeholder="Étape" aria-label="Étape">
      <input name="tasks[note][]" value="<?= e($t['note'] ?? '') ?>" placeholder="Note (qui s'en occupe, lien…)" aria-label="Note" class="note">
    </div>
    <input name="tasks[cat][]" value="<?= e($t['cat'] ?? '') ?>" placeholder="Catégorie" aria-label="Catégorie" class="cat">
    <label class="when"><span class="small"><?= isset($t['offset']) && $t['offset'] !== '' ? e(offset_label((int)$t['offset'])) : 'Date' ?><?= $late ? ' · en retard' : '' ?></span>
      <input type="hidden" name="tasks[offset][]" value="<?= e((string)($t['offset'] ?? '0')) ?>">
      <input type="date" name="tasks[due][]" value="<?= e($due) ?>" aria-label="Échéance"></label>
    <button type="button" class="linkbtn remove-task">Retirer</button>
  </div>
<?php }; ?>
<section>
  <p><a href="<?= e(url(null, '/admin/planning')) ?>">← Tout le planning</a></p>
  <h1><?= $isNew ? 'Planifier une sortie' : e($p['title']) ?></h1>
  <?php if (!$isNew): ?>
    <p class="lead">Sortie le <strong><?= e(date_fr($p['date'])) ?></strong> (<?= e(days_label($p['date'])) ?>) · <?= $done ?>/<?= $total ?> étapes faites<?= $late ? " · <strong class=\"late\">$late en retard</strong>" : '' ?></p>
    <p><a class="btn ghost" href="<?= e(url(null, '/admin/planning/' . $p['id'] . '.ics')) ?>">Ajouter les échéances à mon agenda</a>
      <span class="small">Fichier .ics : s'ouvre avec Google Agenda, l'agenda de l'iPhone ou Outlook.</span></p>
  <?php endif; ?>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

  <form method="post" class="form wide plan-form">
    <?= csrf_field() ?>
    <input type="hidden" name="date_before" value="<?= e($p['date']) ?>">
    <fieldset><legend>La sortie</legend>
      <div class="two">
        <label>Titre<input name="title" required value="<?= e($p['title']) ?>"></label>
        <label>Artiste
          <?php if (is_admin()): ?>
          <select name="artist_slug"><option value="">Le label (sans artiste)</option>
            <?php foreach ($artists as $a): ?><option value="<?= e($a['slug']) ?>"<?= $a['slug'] === $p['artist_slug'] ? ' selected' : '' ?>><?= e($a['name']) ?></option><?php endforeach; ?>
          </select>
          <?php else: ?><input value="<?= e($artists[$p['artist_slug']]['name'] ?? '') ?>" disabled><?php endif; ?>
        </label>
      </div>
      <div class="two">
        <label>Type<select name="type"><?php foreach (PLAN_TYPES as $t): ?><option<?= $t === $p['type'] ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
        <label>Date de sortie<input type="date" name="date" id="release-date" required value="<?= e($p['date']) ?>">
          <span class="small">Changer la date décale toutes les étapes, sauf celles dont vous avez fixé la date à la main.</span></label>
      </div>
      <label>Notes générales<textarea name="notes" rows="3" placeholder="Distributeur, budget, personnes à contacter…"><?= e($p['notes']) ?></textarea></label>
      <?php if (!$hasRelease): ?>
      <label class="check"><input type="checkbox" name="add_release" value="1"> Ajouter aussi cette sortie à la fiche de l'artiste (visible sur son site et dans « Nos sorties »)</label>
      <?php endif; ?>
    </fieldset>

    <fieldset><legend>Étapes</legend>
      <p class="small">Cochez une étape quand elle est faite. Les dates sont calculées depuis la date de sortie (J-60 = 60 jours avant) ; vous pouvez en changer une à la main.</p>
      <div class="tasks" id="tasks">
        <?php foreach ($p['tasks'] as $t) $taskRow($t, task_due($p, $t)); ?>
      </div>
      <template id="tpl-task"><?php $taskRow(['offset' => '']); ?></template>
      <button type="button" class="btn ghost" id="add-task">+ Ajouter une étape</button>
    </fieldset>

    <p><button class="btn"><?= $isNew ? 'Créer le planning' : 'Enregistrer' ?></button></p>
  </form>
  <?php if (!$isNew): ?>
  <form method="post" action="<?= e(url(null, '/admin/planning/' . $p['id'] . '/supprimer')) ?>" onsubmit="return confirm('Supprimer ce planning ?')">
    <?= csrf_field() ?><button class="linkbtn">Supprimer ce planning</button>
  </form>
  <?php endif; ?>
</section>
<script src="<?= e(App\asset('/assets/js/admin.js')) ?>"></script>
