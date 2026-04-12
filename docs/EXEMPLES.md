# 📚 Exemples de Code - Utilisation Rapide

## 🔑 Inclusion des Fichiers

Au démarrage de chaque page PHP, ajoute ceci:

```php
<?php
require_once 'config.php';    // Pour les fonctions et sessions
require_once 'auth.php';      // Pour les fonctions d'authentification
require_once 'check_session.php';  // Pour vérifier la connexion (pages protégées)
?>
```

---

## 👤 Afficher les Infos Utilisateur Connecté

### Nom complet
```php
<?php echo htmlspecialchars($_SESSION['user_name']); ?>
```

### Abréviation (pour avatar)
```php
<?php
  $nom = $_SESSION['user_name'];
  $partsNom = explode(' ', $nom);
  $initiale1 = strtoupper($partsNom[0][0]);
  $initiale2 = strtoupper($partsNom[1][0] ?? 'U');
  echo $initiale1 . $initiale2;
?>
```

### Email
```php
<?php echo htmlspecialchars($_SESSION['user_email']); ?>
```

### Région
```php
<?php echo htmlspecialchars($_SESSION['user_data']['region']); ?>
```

### Niveau
```php
<?php echo htmlspecialchars($_SESSION['user_data']['niveau']); ?>
```

### Date d'inscription
```php
<?php
  $date = $_SESSION['user_data']['date_creation'];
  echo date('d/m/Y', strtotime($date));
?>
```

### ID Utilisateur
```php
<?php echo $_SESSION['user_id']; ?>
```

---

## ✓ Vérifier les États de Connexion

### Est-il connecté?
```php
<?php if (is_logged_in()): ?>
  <!-- Affichage si connecté -->
  <p>Bienvenue <?php echo $_SESSION['user_name']; ?></p>
<?php else: ?>
  <!-- Affichage si pas connecté -->
  <p><a href="login.php">Se connecter</a></p>
<?php endif; ?>
```

### Afficher navbar adaptée
```php
<nav>
  <a href="index.php">Accueil</a>
  
  <?php if (is_logged_in()): ?>
    <a href="profile.php">Mon Profil</a>
    <a href="logout.php">Déconnexion</a>
  <?php else: ?>
    <a href="login.php">Connexion</a>
    <a href="signup.php">S'inscrire</a>
  <?php endif; ?>
</nav>
```

---

## 🔐 Vérifier la Protection des Pages

### Rediriger non-connectés vers login
```php
<?php
require_once 'config.php';
require_once 'check_session.php';  // Cela fait la redirection automatiquement

// Si nous arrivons ici, l'utilisateur est connecté
?>
```

### Redirection personnalisée
```php
<?php
require_once 'config.php';

if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}
?>
```

---

## 📝 Opérations d'Authentification

### S'inscrire
```php
<?php
require_once 'auth.php';

$result = register_user(
    'Ibrahima Diallo',      // nom
    'ibrahima@test.com',    // email
    '+221 77 123 45 67',    // telephone
    'Dakar',                // region
    'Débutant',             // niveau
    'TestPass123'           // password
);

if ($result['success']) {
    echo "Inscription réussie! User ID: " . $result['user_id'];
} else {
    echo "Erreur: " . $result['message'];
}
?>
```

### Se connecter
```php
<?php
require_once 'auth.php';

$result = login_user('ibrahima@test.com', 'TestPass123');

if ($result['success']) {
    echo "Connecté!";
    // La session est créée automatiquement
} else {
    echo "Erreur: " . $result['message'];
}
?>
```

### Se déconnecter
```php
<?php
require_once 'auth.php';

logout_user();
header('Location: index.php');
?>
```

### Changer le mot de passe
```php
<?php
require_once 'auth.php';
require_once 'check_session.php';

$result = change_password(
    $_SESSION['user_id'],        // user_id de session
    'AncienPassword123',         // ancien mot de passe
    'NouveauPassword456'         // nouveau mot de passe
);

if ($result['success']) {
    echo "Mot de passe changé!";
} else {
    echo "Erreur: " . $result['message'];
}
?>
```

### Mettre à jour le profil
```php
<?php
require_once 'auth.php';
require_once 'check_session.php';

$result = update_profile(
    $_SESSION['user_id'],        // user_id
    'Ibrahima D.',               // nouveau nom
    '+221 77 999 88 77',         // nouveau telephone
    'Thiès',                     // nouvelle region
    'Intermédiaire'              // nouveau niveau
);

if ($result['success']) {
    echo "Profil mis à jour!";
    // Les données de session sont automatiquement mises à jour
} else {
    echo "Erreur: " . $result['message'];
}
?>
```

---

## 💾 Requêtes à la Base de Données

### Récupérer un utilisateur
```php
<?php
require_once 'config.php';

$user_data = get_user_data(1);  // Remplace 1 par l'ID utilisateur

echo $user_data['nom_complet'];
echo $user_data['region'];
echo $user_data['niveau'];
?>
```

### Connexion directe à la BD
```php
<?php
require_once 'config.php';

$mysqli = connecter_db();

$sql = "SELECT * FROM utilisateurs WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$mysqli->close();

echo $user['nom_complet'];
?>
```

### Compter les utilisateurs
```php
<?php
require_once 'config.php';

$mysqli = connecter_db();
$result = $mysqli->query("SELECT COUNT(*) as total FROM utilisateurs");
$row = $result->fetch_assoc();

echo "Total utilisateurs: " . $row['total'];

$mysqli->close();
?>
```

---

## 📧 Afficher les Régions et Niveaux

### Lister les régions
```php
<?php
require_once 'config.php';

$mysqli = connecter_db();
$result = $mysqli->query("SELECT * FROM regions ORDER BY nom");

while ($region = $result->fetch_assoc()) {
    echo $region['nom'] . "\n";
}

$mysqli->close();
?>
```

### Lister les niveaux
```php
<?php
require_once 'config.php';

$mysqli = connecter_db();
$result = $mysqli->query("SELECT * FROM niveaux ORDER BY id");

while ($niveau = $result->fetch_assoc()) {
    echo $niveau['nom'] . "\n";
}

$mysqli->close();
?>
```

---

## 🎨 Fragments HTML Courants

### Navbar Dynamique
```html
<nav class="navbar">
  <a href="index.php" class="logo">CALISTHENICS</a>
  
  <div class="nav-links">
    <a href="index.php">Accueil</a>
    
    <?php if (is_logged_in()): ?>
      <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a href="profile.php" class="btn-primary">Dashboard</a>
      <a href="logout.php" class="btn-secondary">Déconnexion</a>
    <?php else: ?>
      <a href="login.php" class="btn-primary">Connexion</a>
      <a href="signup.php" class="btn-secondary">S'inscrire</a>
    <?php endif; ?>
  </div>
</nav>
```

### Topbar pour Utilisateur Connecté
```html
<?php if (is_logged_in()): ?>
  <div class="topbar">
    <div>Bienvenue <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></div>
    <div>
      <a href="profile.php">Profil</a>
      <a href="logout.php">Déconnexion</a>
    </div>
  </div>
<?php endif; ?>
```

### Afficher Erreurs/Messages
```html
<?php if (!empty($error_message)): ?>
  <div class="alert alert-error">
    ❌ <?php echo htmlspecialchars($error_message); ?>
  </div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
  <div class="alert alert-success">
    ✅ <?php echo htmlspecialchars($success_message); ?>
  </div>
<?php endif; ?>
```

---

## 🔄 Formulaires Sécurisés

### Formulaire d'inscription
```html
<form method="POST" action="signup.php">
  <input type="text" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
  <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
  <input type="tel" name="telephone" value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
  
  <select name="region" required>
    <option value="">Sélectionne ta région</option>
    <option value="Dakar" <?php echo isset($_POST['region']) && $_POST['region'] === 'Dakar' ? 'selected' : ''; ?>>Dakar</option>
  </select>
  
  <input type="password" name="password" required>
  <button type="submit">S'inscrire</button>
</form>
```

### Traitement du formulaire
```php
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    // ... etc ...
    
    if (empty($nom)) {
        $error = "Le nom est requis";
    } else {
        // Traiter...
    }
}
?>
```

---

## 🧮 Utiles

### Générer un avatar avec initiales
```php
<?php
function generer_initiales($nom_complet) {
    $parts = explode(' ', trim($nom_complet));
    $initiale1 = strtoupper($parts[0][0] ?? 'U');
    $initiale2 = strtoupper($parts[1][0] ?? 'U');
    return $initiale1 . $initiale2;
}

echo generer_initiales($_SESSION['user_name']);  // Ex: "ID"
?>
```

### Formater la date
```php
<?php
$date_inscription = $_SESSION['user_data']['date_creation'];
echo date('d/m/Y à H:i', strtotime($date_inscription));
// OUTPUT: 21/03/2026 à 14:30
?>
```

### Vérifier l'accès (rôles)
```php
<?php
$user_level = $_SESSION['user_data']['niveau'];

if ($user_level === 'Admin') {
    // Montrer page admin
} elseif ($user_level === 'Élite') {
    // Montrer contenu premium
} else {
    // Montrer contenu gratuit
}
?>
```

---

## 📋 Checklist Intégration

- [ ] Ajouter `<?php require_once 'config.php'; ?>` au début de chaque page
- [ ] Utiliser `htmlspecialchars()` pour afficher les données utilisateur
- [ ] Vérifier `is_logged_in()` pour les pages protégées
- [ ] Utiliser `$_SESSION['user_data']` pour accéder aux infos
- [ ] Tester les redirection de session

---

**Ces exemples couvrent 95% des cas d'usage courants!**
