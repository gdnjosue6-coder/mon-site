# Mimi Rewards — installation locale

Cette version transforme la maquette HTML d’origine en une application PHP/MySQL complète. Elle contient 300 sondages, un espace utilisateur, un système de points, des récompenses quotidiennes, des échanges, des retraits et une page d’administration.

## Configuration requise

- PHP 8.1 ou plus récent avec l’extension `pdo_mysql`
- MySQL 5.7+/MariaDB 10.4+
- Apache (XAMPP, WAMP ou hébergement compatible)

## Installation avec XAMPP

1. Copiez le dossier `mimi` dans `C:\xampp\htdocs\`.
2. Démarrez **Apache** et **MySQL** dans XAMPP.
3. Ouvrez `http://localhost/phpmyadmin`.
4. Cliquez sur **Importer**, choisissez `mimi/database/database.sql`, puis validez.
5. Ouvrez `http://localhost/mimi/index.php`.

Par défaut, la connexion MySQL utilisée dans `config/database.php` est :

```text
Hôte : 127.0.0.1
Port : 3306
Base : mimi_rewards
Utilisateur : root
Mot de passe : vide
```

Si votre hébergeur utilise d’autres identifiants, modifiez uniquement ces valeurs dans `config/database.php` ou définissez les variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` et `DB_PASS`.

## Créer le compte administrateur

1. Créez normalement votre compte sur le site.
2. Ouvrez `database/make_admin.sql`.
3. Remplacez `votre-email@example.com` par l’adresse utilisée lors de votre inscription.
4. Exécutez la requête dans la base **mimi_rewards**, pas dans la base `phpmyadmin`.
5. Déconnectez-vous puis reconnectez-vous. Le bouton **Admin** apparaîtra.

## Fonctionnalités incluses

- Inscription avec mot de passe chiffré et bonus de bienvenue de 300 points
- Connexion/déconnexion avec sessions PHP et protection CSRF
- 300 sondages répartis en 10 catégories
- Une seule réponse possible par utilisateur et par sondage
- Crédit des points effectué côté serveur avec historique complet
- Cycle de connexion de 10 jours totalisant 4 500 points
- Catalogue de récompenses et contrôle du solde
- Retraits Mobile Money, PayPal et Bitcoin à partir de 30 000 points
- Déduction immédiate des points lors de la demande de retrait
- Remboursement automatique si l’administrateur refuse le retrait
- Administration : statistiques, ajout/masquage de sondages et traitement des retraits
- Bouton **Profil** ouvrant une page personnelle avec Paiements, Récompense quotidienne et Activités
- Page **Contrôle du site** avec gestion des utilisateurs, activation/désactivation des comptes, sondages et retraits
- Interface responsive de 320 px aux très grands écrans : petits téléphones, Android/iPhone, mode paysage, tablettes, ordinateurs et écrans larges
- Boutons tactiles d’au moins 44 px, tableaux administratifs défilables et formulaires protégés contre le zoom automatique sur mobile

## Fichiers principaux

- `index.php` : accueil et espace utilisateur
- `profil.php` : profil avec paiements, bonus quotidien et activités
- `admin.php` : tableau de bord administrateur
- `api.php` : traitements PHP sécurisés
- `assets/css/style.css` : design complet et responsive
- `assets/js/app.js` : interactions de l’espace utilisateur
- `assets/js/profile.js` : interactions de la page Profil
- `assets/js/admin.js` : interactions de l’administration
- `database/database.sql` : tables, récompenses et 300 sondages
- `original-index.html` : copie de la maquette reçue

## Important avant la mise en ligne

- Utilisez un certificat HTTPS.
- Configurez un mot de passe MySQL fort.
- Remplacez les valeurs de connexion par des variables d’environnement.
- Précisez vos conditions d’utilisation et votre politique de confidentialité.
- Connectez les paiements à des comptes professionnels vérifiés. Les demandes sont enregistrées, mais le virement réel doit être effectué ou relié à l’API officielle du fournisseur choisi.
