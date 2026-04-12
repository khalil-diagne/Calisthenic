# 🔄 Fichiers HTML à Convertir en PHP (Optionnel)

## Fichiers HTML Existants

Dans le dossier [calis/](.), il y a plusieurs fichiers HTML qui peuvent être convertis en PHP pour bénéficier du système de session et afficher des infos personnalisées selon la connexion.

---

## 📋 Liste de Conversion

### À Convertir (Priorité)

| Fichier HTML | Pourquoi | Bénéfice |
|--------------|---------|----------|
| `calis.html` | Page splash | Afficher navbar personnalisée |
| `index.html` | Ancienne accueil | ☑️ DÉJÀ CONVERTI EN index.php |
| `download.html` | Téléchargement | Vérifier connexion avant DL |
| `login.html` | Ancien login | ☑️ REMPLACÉ PAR login.php |
| `signup.html` | Ancien signup | ☑️ REMPLACÉ PAR signup.php |

### Programmes (Optionnel)

| Fichier | Description |
|---------|-------------|
| `program-street-power.html` | Convertible pour afficher le statut utilisateur |
| `program-skill-builder.html` | Idem |
| `program-endurance.html` | Idem |

---

## 🔀 Comment Convertir un Fichier HTML en PHP

### Étape 1: Renommer le fichier
```bash
# De:
nom_fichier.html

# À:
nom_fichier.php
```

### Étape 2: Ajouter au début du fichier
```php
<?php require_once 'config.php'; ?>
```

### Étape 3: Adapter la navbar

**Avant (HTML statique):**
```html
<nav>
  <a href="index.html">Accueil</a>
  <a href="signup.html">Inscription</a>
</nav>
```

**Après (PHP dinamique):**
```php
<nav>
  <a href="index.php">Accueil</a>
  <?php if (is_logged_in()): ?>
    <a href="profile.php">Mon Profil</a>
    <a href="logout.php">Déconnexion</a>
  <?php else: ?>
    <a href="signup.php">Inscription</a>
    <a href="login.php">Connexion</a>
  <?php endif; ?>
</nav>
```

### Étape 4: Mettre à jour les liens
```php
// Remplacer tous:
href="file.html"     → href="file.php"
src="js/file.js"     → src="js/file.js"  (pas besoin si dans même dossier)
```

---

## 📝 Exemples de Conversion

### Exemple 1: calis.html → calis.php

**Avant:**
```html
<!DOCTYPE html>
<html>
  <head>
    <title>Calisthenics</title>
  </head>
  <body>
    <nav>
      <a href="index.html">Accueil</a>
    </nav>
    <!-- ... contenu ... -->
  </body>
</html>
```

**Après:**
```php
<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Calisthenics</title>
  </head>
  <body>
    <nav>
      <a href="index.php">Accueil</a>
      <?php if (is_logged_in()): ?>
        <a href="profile.php">Profil</a>
        <a href="logout.php">Déconnexion</a>
      <?php else: ?>
        <a href="login.php">Connexion</a>
      <?php endif; ?>
    </nav>
    <!-- ... contenu ... -->
  </body>
</html>
```

### Exemple 2: program-*.html → program-*.php

**Avant:**
```html
<a href="signup.html" class="btn">S'inscrire</a>
```

**Après:**
```php
<?php if (is_logged_in()): ?>
  <p>Vous êtes inscrit! <a href="profile.php">Ver mon profil</a></p>
<?php else: ?>
  <a href="signup.php" class="btn">S'inscrire</a>
<?php endif; ?>
```

---

## ✅ Conversion Complète

### Fichiers Déjà Convertis
- ✅ `index.html` → `index.php`
- ✅ `login.html` → `login.php`
- ✅ `signup.html` → `signup.php`

### À Convertir (Ordre Priorité)

1. **calis.html → calis.php**
   - Simple conversion
   - Importance: Moyenne
   
2. **download.html → download.php**
   - Ajouter vérification connexion
   - Importance: Haute (données sensibles)
   
3. **program-*.html → program-*.php**
   - À faire par lot
   - Importance: Moyenne

4. **Autres fichiers HTML**
   - Selon les besoins
   - Importance: Basse

---

## 🚀 Script de Conversion Rapide

Si tu as beaucoup de fichiers, voici un script pour automatiser:

### Windows (PowerShell)
```powershell
# Renommer tous les .html en .php
Get-ChildItem -Filter "*.html" | ForEach-Object {
    Rename-Item $_ -NewName ($_.Name -replace '\.html$', '.php')
}
```

### Linux/Mac
```bash
# Renommer tous les .html en .php
for file in *.html; do
    mv "$file" "${file%.html}.php"
done
```

---

## 📌 Fichiers HTML à Garder

Ces fichiers peuvent rester en HTML (ne nécessitent pas de session):
- `download.html` (si juste DL sans vérification)
- `calis.html` (s'il n'a pas de CTA d'inscription)
- Images/ressources statiques

---

## 🔗 Mise à Jour des Liens

### Trouver et Remplacer Globalement

**VSCode:**
1. Ouvre la palette (Ctrl+H)
2. Chercher: `\.html"`
3. Remplacer: `.php"`

**Notepad++:**
1. Ctrl+H
2. Chercher: `\.html"`
3. Remplacer: `.php"`
4. "Replace All"

---

## 📋 Checklist de Conversion

Pour chaque fichier:
- [ ] Renommé en `.php`
- [ ] `<?php require_once 'config.php'; ?>` au début
- [ ] Navbar mise à jour
- [ ] Tous les liens `.html` → `.php`
- [ ] Testé dans le navigateur
- [ ] Session marche (navbar change si connecté/non-connecté)

---

## 🎯 Résultat Final

Après conversion, tous tes fichiers PHP aura accès à:

```php
$_SESSION['user_id']           // ID utilisateur
$_SESSION['user_name']         // Nom
$_SESSION['user_data']         // Toutes les infos
is_logged_in()                 // Vérifier connexion
connecter_db()                 // Accès à la base
// ... et plus
```

---

## ❓ Questions Courants

### Q: Dois-je convertir tous les fichiers HTML?
**A:** Non, seulement ceux qui ont besoin d'afficher des infos personnalisées ou de vérifier la connexion.

### Q: Les fichiers HTML cassent après conversion?
**A:** Non, ajouter `<?php require_once 'config.php'; ?>` ne casse rien.

### Q: Comment tester?
**A:** Ouvre le fichier dans le navigateur, la navbar doit changer si tu te connectes/déconnectes.

### Q: Faut-il supprimer les anciens fichiers HTML?
**A:** Non, ils peuvent servir de backup. Mais les .php remplacent les .html.

---

**Création optionnelle - Convertis selon tes besoins!**
