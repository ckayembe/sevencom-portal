<?php
declare(strict_types=1);
namespace App;

/** Modèles de contrats (data/contracts/contrats.md) découpés en sections « ## ». */
function contract_sections(): array {
    static $s;
    if ($s !== null) return $s;
    $md = (string)@file_get_contents(ROOT . '/data/contracts/contrats.md');
    $s = ['intro' => '', 'guide' => '', 'compare' => '', 'contracts' => [], 'annexes' => []];
    $parts = preg_split('/^## /m', $md);
    $s['intro'] = trim(preg_replace('/^# .*\n/', '', array_shift($parts)));
    foreach ($parts as $p) {
        [$title, $body] = array_pad(explode("\n", $p, 2), 2, '');
        $title = trim($title); $body = trim($body);
        if (preg_match('/^Contrat (\d+) : (.*)$/u', $title, $m)) {
            $s['contracts'][(int)$m[1]] = ['n' => (int)$m[1], 'title' => $m[2], 'body' => $body,
                'summary' => preg_match('/^\*(.+)\*$/m', $body, $mm) ? $mm[1] : ''];
        } elseif (str_starts_with($title, 'Annexes')) {
            foreach (preg_split('/^### /m', $body, -1, PREG_SPLIT_NO_EMPTY) as $a) {
                [$at, $ab] = array_pad(explode("\n", $a, 2), 2, '');
                if (preg_match('/^Annexe ([A-Z])/u', $at, $m)) $s['annexes'][$m[1]] = ['title' => trim($at), 'body' => trim($ab)];
            }
        } elseif (str_starts_with($title, 'Comparatif')) {
            $s['compare'] = $body;
        } elseif (str_starts_with($title, "Mode d'emploi")) {
            $s['guide'] = $body;
        }
    }
    return $s;
}

/** Les lignes du tableau comparatif, sous forme de tableaux associatifs. */
function contract_formulas(): array {
    $rows = [];
    foreach (explode("\n", contract_sections()['compare']) as $line) {
        if (!str_starts_with($line, '|') || str_contains($line, '---')) continue;
        $cells = array_map('trim', explode('|', trim($line, " |")));
        if ($cells[0] === 'Formule') continue;
        [$num, $name] = array_pad(explode('. ', $cells[0], 2), 2, '');
        $rows[] = ['n' => (int)$num, 'name' => $name, 'for' => $cells[1] ?? '', 'pays' => $cells[2] ?? '',
                   'masters' => $cells[3] ?? '', 'fee' => $cells[4] ?? '', 'duration' => $cells[5] ?? ''];
    }
    return $rows;
}

/** Texte en ligne : échappement, gras, italique, champs [à compléter] surlignés. */
function md_inline(string $t): string {
    $t = e($t);
    $t = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $t);
    $t = preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/u', '<em>$1</em>', $t);
    // champs entre crochets (un niveau d'imbrication)
    $t = preg_replace('/\[((?:[^\[\]]|\[[^\[\]]*\])+)\]/u', '<mark>[$1]</mark>', $t);
    return $t;
}

/** Petit rendu Markdown : titres, paragraphes, listes, cases à cocher, tableaux. */
function md(string $md): string {
    $out = []; $para = []; $list = null; $table = [];
    $flush = function () use (&$out, &$para, &$list, &$table) {
        if ($para) { $out[] = '<p>' . implode('<br>', array_map('App\md_inline', $para)) . '</p>'; $para = []; }
        if ($list) { $out[] = "<{$list['tag']}>" . implode('', $list['items']) . "</{$list['tag']}>"; $list = null; }
        if ($table) {
            $h = array_shift($table);
            $html = '<div class="table-wrap"><table><thead><tr>' . implode('', array_map(fn($c) => '<th>' . md_inline($c) . '</th>', $h)) . '</tr></thead><tbody>';
            foreach ($table as $r) $html .= '<tr>' . implode('', array_map(fn($c) => '<td>' . md_inline($c) . '</td>', $r)) . '</tr>';
            $out[] = $html . '</tbody></table></div>'; $table = [];
        }
    };
    foreach (preg_split('/\R/', $md) as $line) {
        $l = rtrim($line);
        if ($l === '') { $flush(); continue; }
        if (preg_match('/^(#{1,4}) (.*)$/', $l, $m)) { $flush(); $lv = min(4, strlen($m[1]) + 1); $out[] = "<h$lv>" . md_inline($m[2]) . "</h$lv>"; continue; }
        if (str_starts_with($l, '|')) {
            if ($para || $list) $flush();
            if (!preg_match('/^\|[\s:|-]+\|$/', $l)) $table[] = array_map('trim', explode('|', trim($l, " |")));
            continue;
        }
        if (preg_match('/^\s*(?:[-*]|(\d+)\.) (.*)$/u', $l, $m)) {
            if ($para || $table) $flush();
            $tag = $m[1] !== '' ? 'ol' : 'ul';
            if ($list && $list['tag'] !== $tag) $flush();
            $list ??= ['tag' => $tag, 'items' => []];
            $item = $m[2];
            if (preg_match('/^\[( |x)\] (.*)$/u', $item, $c)) {
                $list['items'][] = '<li class="check"><span class="box">' . ($c[1] === 'x' ? '☒' : '☐') . '</span> ' . md_inline($c[2]) . '</li>';
            } else {
                $list['items'][] = '<li>' . md_inline($item) . '</li>';
            }
            continue;
        }
        if ($list && preg_match('/^\s{2,}\S/', $line)) { $list['items'][count($list['items']) - 1] = preg_replace('#</li>$#', ' ' . md_inline(trim($l)) . '</li>', end($list['items'])); continue; }
        if ($list || $table) $flush();
        $para[] = $l;
    }
    $flush();
    return implode("\n", $out);
}

/** Pré-remplit un modèle avec les informations connues de l'artiste. */
function fill_contract(string $md, ?array $a): string {
    $date = date('d/m/Y');
    $md = preg_replace('/(Fait à \[[^\]]*\], le )\[DATE\]/u', '${1}' . $date, $md);
    if (!$a) return $md;
    $name = $a['name'];
    $group = in_array($a['kind'] ?? 'solo', ['groupe', 'chorale'], true);
    $map = [
        '[NOM DE SCÈNE]' => $name,
        '[NOM LÉGAL / NOM DE SCÈNE / STRUCTURE DE L\'ARTISTE]' => $group ? "[NOM DE LA STRUCTURE], pour « $name »" : "[NOM LÉGAL], dit(e) « $name »",
        '[NOM LÉGAL / NOM DE SCÈNE / STRUCTURE]' => $group ? "[NOM DE LA STRUCTURE], pour « $name »" : "[NOM LÉGAL], dit(e) « $name »",
        '[NOM LÉGAL / NOM DE SCÈNE]' => "[NOM LÉGAL], dit(e) « $name »",
        '[NOM DU GROUPE / DE LA CHORALE]' => $name,
        '[NOM DE L\'ARTISTE]' => $name,
    ];
    return strtr($md, $map);
}
