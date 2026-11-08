<?php use function App\{e, label}; $L = label(); ?>
<section>
  <h1>Contact</h1>
  <p class="lead">Booking, partenariats, démos : écrivez-nous.</p>
  <p><a class="btn" href="mailto:<?= e($L['email']) ?>"><?= e($L['email']) ?></a></p>
  <ul class="links">
  <?php foreach ($L['socials'] as $name => $href): ?><li><a href="<?= e($href) ?>"><?= e($name) ?></a></li><?php endforeach; ?>
  </ul>
</section>
