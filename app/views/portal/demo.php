<?php use function App\{e, url, csrf_field, label}; ?>
<section>
<?php if ($sent): ?>
  <h1>Merci !</h1>
  <p class="lead">Votre message est bien arrivé. L'équipe <?= e(label()['name']) ?> vous répond par e-mail dès que possible.</p>
  <p><a class="btn" href="<?= e(url()) ?>">Retour à l'accueil</a></p>
<?php else: ?>
  <h1>Soumettre une démo</h1>
  <p class="lead">Vous êtes artiste, groupe ou chorale de musique chrétienne ? Partagez-nous vos titres. Ce formulaire sert aussi pour le booking d'un artiste du label et les partenariats.</p>
  <?php if ($errors): ?><div class="errors"><?php foreach ($errors as $er): ?><p><?= e($er) ?></p><?php endforeach; ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label class="hp" aria-hidden="true">Site web<input name="website" tabindex="-1" autocomplete="off"></label>
    <label>Objet
      <select name="kind"><?php foreach (\App\SUBMISSION_KINDS as $k => $lab): ?><option value="<?= e($k) ?>"<?= ($v['kind'] ?? '') === $k ? ' selected' : '' ?>><?= e($lab) ?></option><?php endforeach; ?></select>
    </label>
    <label>Nom (artiste, groupe, chorale ou organisation)<input name="name" required maxlength="120" value="<?= e($v['name'] ?? '') ?>"></label>
    <label>E-mail<input type="email" name="email" required value="<?= e($v['email'] ?? '') ?>"></label>
    <label>Téléphone / WhatsApp<input name="phone" maxlength="40" value="<?= e($v['phone'] ?? '') ?>"></label>
    <label>Ville, pays<input name="city" maxlength="80" value="<?= e($v['city'] ?? '') ?>"></label>
    <label>Vous êtes
      <select name="artist_type"><?php foreach (['' => '—'] + App\KINDS + ['organisateur' => 'Église / organisateur d\'événement'] as $k => $lab): ?><option value="<?= e($k) ?>"<?= ($v['artist_type'] ?? '') === $k ? ' selected' : '' ?>><?= e($lab) ?></option><?php endforeach; ?></select>
    </label>
    <label>Liens vers votre musique (YouTube, Spotify, SoundCloud, Google Drive…)<textarea name="links" rows="3" placeholder="Un lien par ligne"><?= e($v['links'] ?? '') ?></textarea></label>
    <label>Message (pour un booking : date, lieu, type d'événement)<textarea name="message" rows="5"><?= e($v['message'] ?? '') ?></textarea></label>
    <p><button class="btn">Envoyer</button></p>
    <p class="small">Vos informations servent uniquement à vous répondre. Vous pouvez aussi écrire à <a href="mailto:<?= e(label()['email']) ?>"><?= e(label()['email']) ?></a>.</p>
  </form>
<?php endif; ?>
</section>
