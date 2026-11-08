<?php
/**
 * Bloc de lignes répétables.
 * $name : préfixe des champs ; $fields : [champ => [libellé, type, attributs]] ; $rows : valeurs existantes.
 */
use function App\e;
$renderRow = function (array $r) use ($name, $fields) {
    echo '<div class="row-item">';
    foreach ($fields as $f => [$label, $type]) {
        $v = $r[$f] ?? '';
        if ($type === 'hidden') { echo '<input type="hidden" name="' . e($name) . '[' . e($f) . '][]" value="' . e($v) . '">'; continue; }
        echo '<label class="' . e($type === 'file' ? 'file' : '') . '">' . e($label);
        if ($type === 'file') echo '<input type="file" accept="image/jpeg,image/png,image/webp" name="' . e($name) . '[' . e($f) . '][]">';
        elseif ($type === 'textarea') echo '<textarea rows="2" name="' . e($name) . '[' . e($f) . '][]">' . e($v) . '</textarea>';
        else echo '<input type="' . e($type) . '" name="' . e($name) . '[' . e($f) . '][]" value="' . e($v) . '">';
        echo '</label>';
    }
    echo '<button type="button" class="linkbtn remove-row">Retirer</button></div>';
};
?>
<div class="rows" data-rows="<?= e($name) ?>">
  <?php foreach ($rows as $r) $renderRow($r); ?>
  <?php if (!$rows) $renderRow([]); ?>
</div>
<template id="tpl-<?= e($name) ?>"><?php $renderRow([]); ?></template>
<button type="button" class="btn ghost add-row" data-target="<?= e($name) ?>">+ Ajouter</button>
