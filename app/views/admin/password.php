<?php use function App\{e, csrf_field}; ?>
<section class="narrow">
  <h1>Mot de passe</h1>
  <?php if ($forced): ?><p class="lead">Première connexion : choisissez votre propre mot de passe pour continuer.</p><?php endif; ?>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label>Mot de passe actuel<input type="password" name="current" required autocomplete="current-password"></label>
    <label>Nouveau mot de passe <span class="small">(10 caractères minimum)</span><input type="password" name="new" minlength="10" required autocomplete="new-password"></label>
    <label>Confirmer<input type="password" name="confirm" minlength="10" required autocomplete="new-password"></label>
    <button class="btn">Enregistrer</button>
  </form>
</section>
