<?php
/* Frise animée des supports audio, du phonographe à l'audio spatial. Dessins originaux en SVG (trait « gravé » bleu). */
use function App\{e, url};
$S = 'fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"';
$items = [
  ['year' => 1877, 'name' => 'Phonographe à cylindre', 'who' => 'Thomas Edison, États-Unis',
   'text' => "Pour la première fois, la voix humaine est enregistrée : une aiguille grave les vibrations du son en creux sur un cylindre d'étain, puis de cire.",
   'facts' => ['Environ 2 minutes', 'Analogique, mécanique', 'Un exemplaire à la fois'],
   'svg' => "<g $S><rect x='30' y='132' width='140' height='34' rx='4' class='fillp'/><line x1='40' y1='166' x2='40' y2='176'/><line x1='160' y1='166' x2='160' y2='176'/>
     <rect x='52' y='96' width='86' height='34' rx='6' class='fillp'/><ellipse cx='52' cy='113' rx='6' ry='17' class='fillp'/>
     <path class='grooves' d='M64 100v26M74 100v26M84 100v26M94 100v26M104 100v26M114 100v26M124 100v26' stroke-width='1.6' stroke-dasharray='4 4'/>
     <path d='M138 113h14M152 113l8-8'/><path d='M160 105 L118 34 Q150 14 186 30 Z' class='fillp horn'/><ellipse cx='152' cy='30' rx='34' ry='12' transform='rotate(-14 152 30)' class='fillp'/>
     <circle cx='176' cy='150' r='7'/><path d='M176 150l12-8'/></g>"],
  ['year' => 1887, 'name' => 'Gramophone et disque 78 tours', 'who' => 'Emile Berliner, États-Unis',
   'text' => "Le cylindre devient un disque plat, facile à copier en série. Ses 3 minutes par face ont fixé la durée de la chanson populaire, encore valable aujourd'hui.",
   'facts' => ['3 à 4 minutes par face', 'Gomme-laque, 78 tours/min', 'Premier disque vendu en masse'],
   'svg' => "<g $S><path d='M28 150h144l-10 26H38z' class='fillp'/><ellipse cx='100' cy='146' rx='62' ry='14' class='fillp'/>
     <ellipse class='grooves' cx='100' cy='146' rx='48' ry='10' stroke-width='1.4' stroke-dasharray='6 5'/><ellipse class='grooves rev' cx='100' cy='146' rx='34' ry='7' stroke-width='1.4' stroke-dasharray='5 5'/>
     <ellipse cx='100' cy='146' rx='12' ry='3' class='fillp'/>
     <path d='M150 140 L150 112 Q150 96 132 92'/><path d='M132 92 C110 88 92 70 84 44'/>
     <path class='fillp horn' d='M84 44 C70 20 40 12 18 22 C30 42 42 58 74 60 C82 58 86 52 84 44Z'/><path d='M18 22 Q34 34 40 52' stroke-width='1.6'/><path d='M34 14 Q46 32 50 56' stroke-width='1.6'/>
     <circle cx='150' cy='140' r='4'/></g>"],
  ['year' => 1935, 'name' => 'Bande magnétique', 'who' => 'AEG, Allemagne',
   'text' => "Le son devient magnétique : on peut couper, coller, réenregistrer. Les studios l'adoptent après 1945, puis le multipiste permet de superposer voix et instruments.",
   'facts' => ['Montage et multipiste', 'Analogique, magnétique', 'Le studio devient un instrument'],
   'svg' => "<g $S><rect x='18' y='120' width='164' height='56' rx='8' class='fillp'/><circle cx='60' cy='70' r='40' class='fillp'/><circle cx='140' cy='70' r='40' class='fillp'/>
     <g class='spin'><circle cx='60' cy='70' r='9'/><path d='M60 61V36M68 75l21 12M52 75L31 87' /><circle cx='60' cy='70' r='28' stroke-width='1.4' stroke-dasharray='3 6'/></g>
     <g class='spin'><circle cx='140' cy='70' r='9'/><path d='M140 61V36M148 75l21 12M132 75l-21 12'/><circle cx='140' cy='70' r='22' stroke-width='1.4' stroke-dasharray='3 6'/></g>
     <path d='M30 82 Q40 132 100 132 Q160 132 170 82'/><rect x='88' y='126' width='24' height='12' rx='2'/><circle cx='50' cy='156' r='7'/><circle cx='76' cy='156' r='7'/><path d='M120 150h44M120 160h30' stroke-width='2'/></g>"],
  ['year' => 1948, 'name' => 'Vinyle 33 et 45 tours', 'who' => 'Columbia puis RCA, États-Unis',
   'text' => "Le microsillon tient 20 minutes par face : l'album est né, avec sa pochette. Le 45 tours, lancé en 1949, devient le format du single. Le vinyle connaît aujourd'hui un vrai retour.",
   'facts' => ['Environ 20 minutes par face', 'Analogique, vinyle', "Naissance de l'album"],
   'svg' => "<g $S><g class='spin slow'><circle cx='96' cy='104' r='74' class='filld'/>
     <circle cx='96' cy='104' r='64' stroke-width='1' opacity='.6'/><circle cx='96' cy='104' r='56' stroke-width='1' opacity='.6'/><circle cx='96' cy='104' r='48' stroke-width='1' opacity='.6'/><circle cx='96' cy='104' r='40' stroke-width='1' opacity='.6'/>
     <circle cx='96' cy='104' r='24' class='fillp'/><path d='M84 96h24M86 104h16' stroke-width='2'/><circle cx='96' cy='104' r='3'/>
     <path d='M44 60 A70 70 0 0 1 80 36' stroke='rgba(255,255,255,.85)' stroke-width='5'/></g>
     <circle cx='176' cy='34' r='9' class='fillp'/><path d='M176 34 L168 120 L146 140' stroke-width='4'/><rect x='136' y='134' width='16' height='10' rx='2' transform='rotate(40 144 139)' class='fillp'/></g>"],
  ['year' => 1963, 'name' => 'Cassette audio', 'who' => 'Philips, Pays-Bas',
   'text' => "Petite, solide et enregistrable : chacun copie, partage et compose ses compilations. Avec le baladeur, la musique sort dans la rue. En Afrique, elle a longtemps été le support roi, gospel compris.",
   'facts' => ['30 à 45 minutes par face', 'Analogique, magnétique', 'La musique devient nomade'],
   'svg' => "<g $S><rect x='16' y='46' width='168' height='108' rx='10' class='fillp'/><rect x='30' y='58' width='140' height='38' rx='4'/><path d='M40 70h70M40 80h48' stroke-width='2'/>
     <rect x='50' y='104' width='100' height='32' rx='14'/>
     <g class='spin'><circle cx='72' cy='120' r='10'/><path d='M72 110v4M72 126v4M62 120h4M78 120h4'/></g>
     <g class='spin'><circle cx='128' cy='120' r='10'/><path d='M128 110v4M128 126v4M118 120h4M134 120h4'/></g>
     <rect x='88' y='112' width='24' height='16' rx='2' stroke-width='1.6'/><path d='M52 154 L62 140h76l10 14'/><circle cx='80' cy='148' r='3'/><circle cx='120' cy='148' r='3'/></g>"],
  ['year' => 1982, 'name' => 'Disque compact (CD)', 'who' => 'Philips et Sony',
   'text' => "Le son devient numérique : un laser lit des milliards de creux microscopiques. Plus de souffle, plus d'usure, et 74 minutes sans retourner le disque.",
   'facts' => ['74 à 80 minutes', 'Numérique, 16 bits / 44,1 kHz', 'Lecture laser sans contact'],
   'svg' => "<g class='spin slow'><circle cx='100' cy='100' r='78' class='cd'/><circle cx='100' cy='100' r='78' fill='none' stroke='currentColor' stroke-width='3'/>
     <circle cx='100' cy='100' r='22' fill='var(--paper)' stroke='currentColor' stroke-width='3'/><circle cx='100' cy='100' r='8' fill='none' stroke='currentColor' stroke-width='3'/>
     <circle cx='100' cy='100' r='60' fill='none' stroke='rgba(255,255,255,.5)' stroke-width='1'/></g>
     <path class='laser' d='M100 196 L136 64' stroke='#5aa6d8' stroke-width='3' stroke-linecap='round'/>"],
  ['year' => 1995, 'name' => 'MP3 et baladeur numérique', 'who' => 'Institut Fraunhofer, Allemagne',
   'text' => "La compression divise la taille des fichiers par dix : la musique voyage par internet. En 2001, un baladeur promet « mille chansons dans la poche ».",
   'facts' => ['Des milliers de titres', 'Numérique compressé', 'La musique se dématérialise'],
   'svg' => "<g $S><rect x='56' y='16' width='88' height='168' rx='16' class='fillp'/><rect x='68' y='30' width='64' height='56' rx='4' class='filld'/>
     <g class='eq' stroke='#fff' stroke-width='5'><path d='M80 76v-14'/><path d='M92 76v-24'/><path d='M104 76v-18'/><path d='M116 76v-28'/></g>
     <circle cx='100' cy='134' r='32' class='fillp'/><circle cx='100' cy='134' r='11'/><path d='M100 108v6M100 154v6M74 134h6M120 134h6' stroke-width='2'/>
     <path class='wheel' d='M100 102 A32 32 0 0 1 132 134' stroke='#5aa6d8' stroke-width='4'/></g>"],
  ['year' => 2008, 'name' => 'Streaming', 'who' => 'Plateformes en ligne',
   'text' => "On n'achète plus la musique, on y accède : tout le catalogue dans le téléphone. Le streaming représente aujourd'hui la majorité des revenus de la musique enregistrée dans le monde.",
   'facts' => ['Des millions de titres', 'Numérique, en ligne', "L'artiste touche le monde entier"],
   'svg' => "<g $S><rect x='58' y='10' width='84' height='180' rx='14' class='fillp'/><path d='M88 20h24' stroke-width='2'/><rect x='68' y='32' width='64' height='64' rx='6' class='filld'/>
     <path d='M92 52 L114 64 L92 76 Z' fill='#fff' stroke='#fff'/>
     <path d='M70 110h60' stroke-width='2' opacity='.4'/><path class='prog' d='M70 110h60' stroke='#5aa6d8' stroke-width='3'/>
     <g class='eq'><path d='M76 160v-20'/><path d='M88 160v-32'/><path d='M100 160v-14'/><path d='M112 160v-26'/><path d='M124 160v-18'/></g></g>
     <g class='waves' fill='none' stroke='currentColor' stroke-width='2'><path d='M152 70 Q164 100 152 130'/><path d='M164 58 Q182 100 164 142'/><path d='M48 70 Q36 100 48 130'/><path d='M36 58 Q18 100 36 142'/></g>"],
  ['year' => 2020, 'name' => 'Audio spatial et haute résolution', 'who' => "Aujourd'hui",
   'text' => "Le son vous entoure : chaque voix, chaque instrument se place dans l'espace autour de l'auditeur, au casque comme en salle. La qualité studio arrive jusque dans le téléphone.",
   'facts' => ['Son en trois dimensions', 'Numérique haute résolution', "L'auditeur au cœur du chœur"],
   'svg' => "<g class='rings' fill='none' stroke='currentColor' stroke-width='2'><circle cx='100' cy='106' r='40'/><circle cx='100' cy='106' r='40'/><circle cx='100' cy='106' r='40'/></g>
     <g $S><path d='M44 118 V96 A56 56 0 0 1 156 96 V118' stroke-width='6'/><rect x='30' y='108' width='30' height='52' rx='10' class='filld'/><rect x='140' y='108' width='30' height='52' rx='10' class='filld'/>
     <circle cx='100' cy='108' r='4' fill='currentColor'/></g>"],
];
?>
<section class="audio-history" aria-label="L'histoire des supports audio">
  <div class="ah-pin" style="--n:<?= count($items) ?>">
    <div class="ah-sticky">
      <div class="ah-year" aria-hidden="true"><?= $items[0]['year'] ?></div>
      <header class="ah-head">
        <p class="kicker">De 1877 à aujourd'hui</p>
        <h2 class="ah-title">Du phonographe au streaming</h2>
      </header>
      <svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs>
        <linearGradient id="cdgrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#dfe8f0"/><stop offset=".25" stop-color="#f6d6f0"/><stop offset=".42" stop-color="#cfe9ff"/><stop offset=".58" stop-color="#d9f7e4"/><stop offset=".75" stop-color="#fff2c7"/><stop offset="1" stop-color="#c9d9ea"/></linearGradient>
      </defs></svg>
      <div class="ah-stage">
        <?php foreach ($items as $i => $it): ?>
          <article class="ah-item" style="--i:<?= $i ?>">
            <div class="ah-art"><svg viewBox="0 0 200 200" aria-hidden="true"><?= $it['svg'] ?></svg></div>
            <div class="ah-body">
              <p class="ah-date"><?= $it['year'] ?><span><?= e($it['who']) ?></span></p>
              <h3><?= e($it['name']) ?></h3>
              <p><?= e($it['text']) ?></p>
              <ul class="ah-facts"><?php foreach ($it['facts'] as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="ah-axis" aria-hidden="true">
        <div class="ah-line"><span class="ah-fill"></span></div>
        <?php foreach ($items as $i => $it): ?><span class="ah-tick" style="--i:<?= $i ?>"><?= $it['year'] ?></span><?php endforeach; ?>
        <span class="ah-needle"></span>
      </div>
      <p class="ah-end">Seven.com publie vos titres sur tous les supports d'aujourd'hui. <a href="<?= e(url(null, '/formules')) ?>">Découvrir nos formules →</a></p>
    </div>
  </div>
</section>
