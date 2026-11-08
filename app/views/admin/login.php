<?php use function App\{e, csrf_field}; ?>
<section class="narrow">
  <h1>Connexion</h1>
  <p class="lead">Espace réservé au label et à ses artistes.</p>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label>E-mail<input type="email" name="email" value="<?= e($email) ?>" required autofocus autocomplete="username"></label>
    <label>Mot de passe<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn">Se connecter</button>
  </form>
  <p class="small">Mot de passe oublié ? Contactez l'administrateur du label.</p>
</section>
