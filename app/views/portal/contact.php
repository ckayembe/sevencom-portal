<?php use function App\{e, label, url}; $L = label(); ?>
<section>
  <h1>Contact</h1>
  <p class="lead">Booking, partenariats, démos : écrivez-nous.</p>
  <p><a class="btn" href="<?= e(url(null, '/demo')) ?>">Soumettre une démo</a> <a class="btn ghost" href="<?= e(url(null, '/demo?objet=booking')) ?>">Demande de booking</a></p>
  <p>Ou par e-mail :</p>
  <p><a class="btn ghost" href="mailto:<?= e($L['email']) ?>"><?= e($L['email']) ?></a></p>
  <ul class="links">
  <?php foreach ($L['socials'] as $name => $href): ?><li><a href="<?= e($href) ?>"><?= e($name) ?></a></li><?php endforeach; ?>
  </ul>
</section>
