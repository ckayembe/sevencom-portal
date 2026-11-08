# Seven.com : portail du label + sites artistes

Un seul code PHP sert **sevencom.tech** (le portail) et **tous les sous-domaines artistes**
(ex. `christiankayembe.sevencom.tech`). Le site affiché dépend du nom de domaine appelé.
Aucune base de données, aucun framework : ça tourne tel quel sur l'hébergement web Infomaniak (PHP 8.1+).

## Structure

Le dépôt correspond directement au dossier `public_html` du site (déployé sur Hostinger).

```
├── index.php         ← point d'entrée unique
├── .htaccess         ← HTTPS forcé, blocage de app/ et data/, routage vers index.php
├── assets/           ← CSS, logo, photos artistes (assets/img/artists/)
├── app/              ← code (inaccessible depuis le web)
│   ├── bootstrap.php ← routage par sous-domaine
│   └── views/        ← gabarits du portail et du site artiste
└── data/             ← contenu en JSON (inaccessible depuis le web)
    ├── label.json
    ├── tools.json
    └── artists/christiankayembe.json   ← nom du fichier = sous-domaine
```

**Liens entre les sites** : chaque site artiste a une barre Seven.com qui renvoie au portail et aux autres artistes, plus une section qui promeut les outils du label.

## Administration (sevencom.tech/admin)

- **Administrateur** (compte `infos@sevencom.tech`) : crée les artistes et leurs comptes, modifie toutes les fiches, les infos du label et les outils, régénère les mots de passe.
- **Artiste** : se connecte avec son e-mail et ne peut modifier que sa propre fiche (bio, photo, sorties, vidéos, concerts, réseaux).
- Chaque nouveau compte doit changer son mot de passe à la première connexion.
- Les modifications sont en ligne immédiatement, sans redéploiement.

Les données modifiables sont dans une base SQLite **hors du dossier du site** :
`/home/<user>/domains/sevencom.tech/storage/` (base `sevencom.sqlite` + images dans `uploads/`).
Les déploiements GitHub n'y touchent jamais. Au tout premier lancement, la base est remplie à partir de `data/*.json`
(ces fichiers ne servent plus ensuite). Pensez à sauvegarder ce dossier `storage/`.

## Outils du label

- **Planificateur de sorties** (`/admin/planning`, admin et artistes) : un rétroplanning par sortie, étapes J-60 → J+14 calculées depuis la date de sortie, étapes en retard signalées, export des échéances vers un agenda (`.ics`), ajout possible de la sortie à la fiche artiste. Code : `app/planner.php`, vues `app/views/admin/plans.php` et `plan_form.php`. Les étapes proposées par défaut sont dans `PLAN_TEMPLATE`.
- Les outils intégrés apparaissent en tête de la page publique `/outils` (`builtin_tools()` dans `app/bootstrap.php`), avant ceux saisis dans « Label & outils ».

## Adresses des artistes automatiques (admin › Hébergement)

- Une fois pour toutes : entrée DNS `A * → 147.93.93.251` chez Infomaniak, et une clé d'API Hostinger collée dans **admin › Hébergement** (rangée dans `storage/hostinger.json`, hors du site, jamais réaffichée).
- Ensuite, à la création d'un artiste, le site déclare `<slug>.sevencom.tech` sur Hostinger (même dossier) puis demande le certificat SSL dès que l'adresse répond au DNS. Les SSL en attente sont relancés automatiquement (au plus toutes les 10 min) à chaque visite de l'admin. Code : `app/hosting.php`.

## Ajouter un nouvel artiste (ancienne méthode, remplacée par l'admin)

1. Copier `data/artists/christiankayembe.json` en `data/artists/<slug>.json` et modifier `slug`, nom, bio, sorties, réseaux.
2. Déposer ses photos dans `assets/img/artists/`.
3. Ajouter le sous-domaine `<slug>.sevencom.tech` sur Hostinger (même dossier que le site principal) et l'enregistrement DNS `A <slug> → 147.93.93.251` chez Infomaniak.

Pour masquer un artiste : `"published": false`.

## Tester en local

```
SEVENCOM_DOMAIN=localhost SEVENCOM_STORAGE=/tmp/sevencom-storage php -S 127.0.0.1:8000 index.php
```
Puis http://localhost:8000 et http://christiankayembe.localhost:8000

## Hébergement

- Site hébergé chez **Hostinger** (site `sevencom.tech`, sous-domaines servis depuis le même `public_html`).
- Domaine et messagerie (`infos@sevencom.tech`) chez **Infomaniak**. Seuls les enregistrements `A` (`@`, `www`, sous-domaines artistes) pointent vers Hostinger (`147.93.93.251`). Les enregistrements MX/TXT restent ceux d'Infomaniak.
- SSL Let's Encrypt géré par Hostinger.

## Évolutions prévues
- Espace d'administration (éditer les fiches artistes sans toucher au JSON).
- Outils du label : distribution, promotion (smart links, pré-saves), gestion (booking, royalties).
- Statistiques de visites par artiste.
