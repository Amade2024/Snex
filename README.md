# Site web S-NEX SARL

Site institutionnel de S-NEX SARL (télécommunications, énergie, photovoltaïque), construit sur WordPress.

| Élément | Emplacement | Rôle |
| --- | --- | --- |
| Thème `snex` | `wp-content/themes/snex` | Charte (theme.json), en-tête, pied de page, gabarits, motifs de sections verrouillés en mode « contenu » |
| Extension `snex-core` | `wp-content/plugins/snex-core` | Réalisations et domaines, lieu et année, coordonnées centralisées (menu **Coordonnées**) |
| Données de départ | `bin/seed.php` | Pages, menu, logo et médias créés à partir des motifs du thème |
| Contrôles et tests | `bin/lint.sh`, `bin/test.sh`, `tests/` | Normes WordPress (PHPCS), syntaxe, tests des coordonnées, validité des blocs dans l'éditeur |

## Démarrer en local

Prérequis : PHP 8.1 ou plus (extensions `pdo_sqlite`, `gd`), Git, `curl`. Ni MySQL ni Docker.

```bash
bin/setup-local.sh   # télécharge WordPress, les outils, installe et remplit le site
bin/serve.sh         # http://127.0.0.1:8080
```

Les accès administrateur sont écrits dans `.local/admin.txt` (jamais versionné). Pour régénérer les pages
à partir des motifs : `SNEX_SEED_FORCE=1 php tools/wp-cli.phar --path=.local/wordpress eval-file bin/seed.php`
(ou `SNEX_SEED_FORCE=telecommunications,photovoltaique` pour ne régénérer que ces pages).

En local, WordPress utilise SQLite et l'interface reste en anglais : les paquets de langue viennent de
wordpress.org, bloqué dans cet environnement. La production utilise MySQL ou MariaDB et la langue française.

En local, les e-mails (demandes de devis) ne partent pas : ils sont écrits dans
`.local/wordpress/wp-content/snex-mails/` par `bin/mail-local.php`, installé en extension obligatoire par `bin/setup-local.sh`.

## Formulaire de devis

Bloc **Formulaire de devis** (`snex/devis`, `wp-content/plugins/snex-core/includes/devis.php`), placé sur la page Demande de devis.

| Étape | Champs |
| --- | --- |
| 1. Vos coordonnées | Nom et prénom, entreprise (facultatif), téléphone, e-mail, moyen de contact préféré |
| 2. Votre projet | Ville (champ libre, villes ivoiriennes suggérées), commune ou quartier (communes d'Abidjan suggérées), service, budget estimé en FCFA saisi librement (facultatif), description |
| 3. Documents et envoi | Pièces jointes (3 fichiers, 5 Mo chacun, facultatif), récapitulatif, consentement |

- La demande est envoyée à l'e-mail saisi dans **Coordonnées**, avec l'adresse du demandeur en « Répondre à ».
- Si le demandeur choisit **WhatsApp** comme moyen d'échange, il est ensuite conduit vers la conversation WhatsApp
  de S-NEX (numéro confirmé dans **Coordonnées**), avec sa demande déjà rédigée : il n'a plus qu'à appuyer sur Envoyer.
  La demande part aussi par e-mail, avec les pièces jointes (WhatsApp ne permet pas de les transmettre par lien).
  Sans numéro WhatsApp confirmé, la demande part seulement par e-mail.
- Un lien `/demande-de-devis/?service=photovoltaique` (ou `telecommunications`, `energie`) présélectionne le service.
- Protection contre le spam sans service externe : nonce, champ piège, délai minimum de 3 s, 5 demandes par heure et par adresse IP.
- Sans JavaScript, les trois étapes s'affichent l'une sous l'autre et le serveur vérifie la saisie.
- La taille des fichiers est plafonnée par l'hébergeur (`upload_max_filesize`, `post_max_size`) : prévoir au moins 8 Mo et 16 Mo.

## SEO, sécurité, performance

Fait dans `snex-core` (`includes/seo.php`, `securite.php`, `performance.php`), sans extension tierce :

- **Référencement** : description de chaque page reprise de son **Extrait** (panneau de l'éditeur, 160 caractères
  au plus), balises de partage (Open Graph), données structurées de l'entreprise sur l'accueil, alimentées par
  **Coordonnées** (téléphone publié seulement une fois confirmé). Pages exclues des moteurs : recherche, confirmation
  d'envoi du devis, archives vides ou héritées du blog. Plan du site : `/wp-sitemap.xml`, sans les comptes.
- **Sécurité** : en-têtes HTTP (nosniff, cadre, référent, permissions, isolement de fenêtre), XML-RPC refusé,
  identifiants des comptes non exposés (`?author=`, API REST, oEmbed), 5 échecs de connexion par IP puis 15 minutes
  de blocage, message identique pour un identifiant ou un mot de passe faux, version de WordPress masquée, éditeur de
  fichiers et mots de passe d'application désactivés, commentaires fermés.
- **Attaques XSS** : politique de sécurité du contenu (CSP). Seuls les fichiers du site et les scripts en ligne
  écrits par WordPress (autorisés un par un par leur empreinte SHA-256) s'exécutent : un script injecté est bloqué
  par le navigateur. Aucun HTML libre dans les contenus, même pour les administrateurs (`DISALLOW_UNFILTERED_HTML`) :
  scripts et attributs `onclick`, `onerror`… sont retirés à l'enregistrement. Toutes les sorties sont échappées et
  le formulaire de devis est protégé par jeton (nonce). Une extension ajoutée plus tard qui écrit ses propres
  `<script>` sans passer par WordPress sera bloquée : `tests/navigateur.py` le signale.
- **Copie du contenu** : sélection du texte, copier/couper (Ctrl+C, Ctrl+X), clic droit, glisser-déposer et appui
  long (mobile) désactivés sur tout le site (`assets/protection-contenu.js` et `style.css`). Les champs du formulaire
  de devis restent utilisables (copier-coller compris) et les comptes connectés ne sont pas concernés. C'est une
  dissuasion : une capture d'écran, le code source de la page ou les outils du navigateur restent possibles, aucun
  site ne peut l'empêcher.
- **Performance** : images reliées à leur média à l'affichage (dimensions, tailles réduites, chargement différé
  sous la ligne de flottaison, priorité à l'image principale), deux polices préchargées, en-tête allégé.

À faire chez l'hébergeur (phase 10) :

- HTTPS partout ; l'en-tête HSTS est envoyé automatiquement avec `WP_ENVIRONMENT_TYPE` à `production`.
- Interdire l'exécution de PHP dans `wp-content/uploads/`, le listage des dossiers, et l'accès à `readme.html`,
  `license.txt` et `wp-config.php`.
- Protection contre le « hotlink » (images du site affichées par d'autres sites), par exemple sous Apache :

  ```apache
  RewriteEngine On
  RewriteCond %{HTTP_REFERER} !^$
  RewriteCond %{HTTP_REFERER} !^https://(www\.)?snexsarl\.com/ [NC]
  RewriteRule \.(jpe?g|png|webp|gif|svg)$ - [F,NC]
  ```
- Cache des pages, et cache navigateur long (1 an) pour `wp-content/themes/snex/assets/` et `wp-content/uploads/`.
- Compression gzip ou Brotli, sauvegardes automatiques de la base et des médias, mises à jour de sécurité de WordPress.
- Envoi des e-mails par la messagerie de S-NEX (`includes/courriel.php`), pour qu'ils n'arrivent pas en spam : ajouter
  dans `wp-config.php` le mot de passe de direction@snexsarl.com, jamais dans le dépôt.

  ```php
  define( 'SNEX_SMTP_PASSWORD', '…' );
  // Facultatif (valeurs par défaut) : SNEX_SMTP_HOST 'mail.snexsarl.com', SNEX_SMTP_PORT 465,
  // SNEX_SMTP_SECURE 'ssl' (ou 587 avec 'tls'), SNEX_SMTP_USER 'direction@snexsarl.com'.
  ```

  Puis envoyer une demande de devis de test et vérifier sa réception sur https://mail.snexsarl.com.
- Derrière un proxy ou un CDN, vérifier que `REMOTE_ADDR` contient l'IP du visiteur (limites de connexion et de devis).
- Déclarer le site dans Google Search Console avec l'adresse du plan du site.

## Vérifier

```bash
bin/lint.sh          # syntaxe PHP et JS, JSON, normes WordPress
bin/test.sh          # lint + tests des coordonnées, du devis, du SEO et de la sécurité + validité des blocs (site local lancé)
```

Le test de l'éditeur demande Playwright : `pip install playwright && python3 -m playwright install chromium`.

## Règles du projet

- Aucune coordonnée, référence client, certification, statistique ou mention légale inventée.
- Les boutons **Appeler** et **WhatsApp** n'apparaissent qu'après saisie et confirmation du numéro dans **Coordonnées**.
  Numéros confirmés par S-NEX :
  - appels : +225 07 13 27 87 59 (format international 2250713278759, puis cocher « Numéro vérifié ») ;
    il s'affiche sous chaque bouton **Appeler** ;
  - WhatsApp : +225 05 66 19 27 57 (saisir 2250566192757 et cocher « Compte WhatsApp vérifié », lien
    wa.me/2250566192757), affiché aussi comme second téléphone.
- Aucune réalisation fictive. Les Réalisations sont retirées du menu, du pied de page et de l'accueil ; le type de contenu
  reste dans l'administration pour les publier plus tard.
- Formulaires (phase 6) : envoi par e-mail (et WhatsApp si le demandeur le choisit), aucune demande enregistrée en base, pièces jointes supprimées après envoi.
- Aucun secret dans le dépôt (`.local/`, `tools/` et `wp-config.php` sont ignorés).

## Avancement

| Phase | État |
| --- | --- |
| 1-2. Cadrage | Validé le 18/10/2026 |
| 3. Maquettes | Validées (v1.1, menu mobile corrigé) |
| 4. Mise en place | Fait : dépôt, environnement local reproductible, contrôles de code |
| 5. Pages et composants | En cours : accueil et Énergie terminées ; Télécommunications, Photovoltaïque, À propos en version de travail |
| 6. Formulaires | En cours : formulaire de devis fait ; formulaire de contact à faire |
| 7. SEO, sécurité, performance | Fait côté site ; réglages serveur à faire avec l'hébergeur |
| 8-10. Tests, recette, déploiement | À faire (hébergeur à choisir) |
