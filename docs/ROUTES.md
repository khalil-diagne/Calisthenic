# 🗂️ Map Complète du Système

## Configuration

| Fichier | Description |
|---------|-------------|
| **config.php** | Configuration MySQL + Gestion des sessions |
| **auth.php** | Fonctions d'authentification (register, login, logout) |
| **check_session.php** | Redirection si non-connecté |

## Pages Publiques

| Route | Fichier | Description |
|-------|---------|-------------|
| `/` | **index.php** | Page d'accueil (avec navbar connecté/déconnecté) |
| `/signup.php` | **signup.php** | Inscription (POST: formulaire) |
| `/login.php` | **login.php** | Connexion (POST: formulaire) |

## Pages Protégées (Nécessite session)

| Route | Fichier | Description |
|-------|---------|-------------|
| `/profile.php` | **profile.php** | Dashboard utilisateur (affiche infos + formulaires de modification) |
| `/logout.php` | **logout.php** | Déconnexion (détruit la session) |

## Base de Données

| Script | Description |
|--------|-------------|
| **create_users_table.sql** | Création des tables (regions, niveaux, utilisateurs) |

## Fichiers de Support

| Fichier | Type | Description |
|---------|------|-------------|
| **js/validation.js** | JavaScript | Validation du formulaire côté client |
| **js/script.js** | JavaScript | Animations + Curseur personnalisé |
| **css/styles.css** | CSS | Styles globaux du site |

## Base de Données - Schéma

### Table: regions
```
id (INT) - PK
nom (VARCHAR) - UNIQUE
code (VARCHAR)
```

### Table: niveaux
```
id (INT) - PK
nom (VARCHAR) - UNIQUE
description (TEXT)
```

### Table: utilisateurs
```
id (INT) - PK AUTO_INCREMENT
nom_complet (VARCHAR 100)
email (VARCHAR 100) - UNIQUE
telephone (VARCHAR 20)
region_id (INT) - FK → regions.id
niveau_id (INT) - FK → niveaux.id
password (VARCHAR 255) - BCRYPT
date_creation (TIMESTAMP)
date_modification (TIMESTAMP)
actif (BOOL)
email_confirme (BOOL)
```

## Flow d'Authentification

### Inscription
1. `/signup.php` (GET) → Affiche le formulaire
2. `/signup.php` (POST) 
   - Valide les données
   - Appelle `register_user()` de auth.php
   - Crée la session automatiquement
   - Redirection vers index.php

### Connexion
1. `/login.php` (GET) → Affiche le formulaire
2. `/login.php` (POST)
   - Valide l'email/password
   - Appelle `login_user()` de auth.php
   - Crée la session
   - Redirection vers page précédente ou index.php

### Vérification de Session
- Config.php au chargement:
  - Démarre la session
  - Vérifie le timeout (1h)
  - Met à jour `last_activity`
  - Récupère les données utilisateur si connecté

### Pages Protégées
- Inclure `check_session.php`
- Redirige vers login si non-connecté

### Déconnexion
1. Clique sur `/logout.php`
2. Détruit la session
3. Redirection vers index.php

## Variables de Session

| Variable | Type | Contenu |
|----------|------|---------|
| `user_id` | INT | ID de l'utilisateur |
| `user_name` | STRING | Nom complet |
| `user_email` | STRING | Email |
| `user_data` | ARRAY | Toutes les infos utilisateur |
| `last_activity` | TIMESTAMP | Pour vérifier le timeout |

## Points d'Accès

Le système est accessible via:
- Formulaires HTML (signup.php, login.php)
- Sessions PHP (config.php gère les sessions)
- MySQL (connecter_db() pour les requêtes)

Tous les fichiers HTML peuvent être convertis en PHP en ajoutant:
```php
<?php require_once 'config.php'; ?>
```

## Instructions d'Installation

1. **Créer la base de données**
```sql
CREATE DATABASE calisthenics_senegal;
```

2. **Importer le script SQL**
```bash
mysql -u root calisthenics_senegal < create_users_table.sql
```

3. **Modifier config.php si besoin**
   - Changer DB_HOST, DB_USER, DB_PASS, DB_NAME

4. **Tester**
   - Ouvre `http://localhost/img/calis/index.php`
   - Clique sur "Rejoindre" ou "Connexion"

## Routes Disponibles

```
GET  /index.php                    - Accueil
GET  /signup.php                   - Formulaire inscription
POST /signup.php                   - Traitement inscription
GET  /login.php                    - Formulaire connexion  
POST /login.php                    - Traitement connexion
GET  /profile.php                  - Dashboard (protégé)
POST /profile.php                  - Modification profil (protégé)
GET  /logout.php                   - Déconnexion
```

## Fichiers de Test

- **test_connexion.php** - Diagnostic de la connexion MySQL (À SUPPRIMER après test!)

---

**Status**: ✅ Système complèt synchronisé avec MySQL et sessions PHP
