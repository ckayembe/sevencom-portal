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

## Ajouter un nouvel artiste

1. Copier `data/artists/christiankayembe.json` en `data/artists/<slug>.json` et modifier `slug`, nom, bio, sorties, réseaux.
2. Déposer ses photos dans `assets/img/artists/`.
3. Ajouter le sous-domaine `<slug>.sevencom.tech` sur Hostinger (même dossier que le site principal) et l'enregistrement DNS `A <slug> → 147.93.93.251` chez Infomaniak.

Pour masquer un artiste : `"published": false`.

## Tester en local

```
SEVENCOM_DOMAIN=localhost php -S 127.0.0.1:8000 index.php
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
