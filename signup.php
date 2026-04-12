<?php
// ==============================================
// Gestion de l'inscription utilisateur
// Traitement direct du formulaire (sans JSON)
// ==============================================

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Récupérer et valider les données
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $niveau = trim($_POST['niveau'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validations
    if (empty($nom)) {
        $error_message = "Le nom est requis";
    } elseif (empty($email)) {
        $error_message = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email invalide";
    } elseif (empty($password)) {
        $error_message = "Le mot de passe est requis";
    } elseif (strlen($password) < 6) {
        $error_message = "Le mot de passe doit avoir au moins 6 caractères";
    } elseif (empty($region)) {
        $error_message = "Veuillez sélectionner une région";
    } elseif (empty($niveau)) {
        $error_message = "Veuillez sélectionner un niveau";
    } else {
        // Appeler la fonction d'enregistrement
        $result = register_user($nom, $email, $telephone, $region, $niveau, $password);
        
        if ($result['success']) {
            $success_message = "✅ Inscription réussie! Redirection...";
            // Redirection après 2 secondes
            header("refresh:2;url=index.php");
        } else {
            $error_message = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Créer un compte — Calisthenics Senegal</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .signup-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: var(--noir);
  }
  .signup-card {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 3rem;
    max-width: 500px;
    width: 100%;
    clip-path: polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%);
  }
  .signup-card h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: var(--blanc);
  }
  .signup-card p {
    color: var(--gris-clair);
    margin-bottom: 2rem;
  }
  .form-group {
    margin-bottom: 1.5rem;
  }
  .form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--jaune);
    margin-bottom: 0.5rem;
  }
  .form-group input,
  .form-group select {
    width: 100%;
    padding: 0.8rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,215,0,0.2);
    color: var(--blanc);
    font-family: 'Syne', sans-serif;
    font-size: 0.95rem;
    transition: border-color 0.2s;
    box-sizing: border-box;
  }
  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: var(--jaune);
    background: rgba(255,215,0,0.05);
  }
  .form-group input::placeholder {
    color: var(--gris-clair);
  }
  .form-group select {
    cursor: pointer;
  }
  .signup-btn {
    width: 100%;
    padding: 1rem;
    background: var(--jaune);
    color: var(--noir);
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 0.9rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    clip-path: polygon(12px 0%, 100% 0%, calc(100% - 12px) 100%, 0% 100%);
    transition: background 0.2s;
    margin-bottom: 1rem;
  }
  .signup-btn:hover {
    background: var(--vert);
  }
  .login-link {
    text-align: center;
    color: var(--gris-clair);
    font-size: 0.9rem;
  }
  .login-link a {
    color: var(--jaune);
    text-decoration: none;
    transition: color 0.2s;
  }
  .login-link a:hover {
    color: var(--blanc);
  }
  .back-to-home {
    display: inline-block;
    margin-bottom: 2rem;
    color: var(--gris-clair);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s;
  }
  .back-to-home:hover {
    color: var(--jaune);
  }
  .message {
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 4px;
    font-size: 0.9rem;
  }
  .error-message {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.3);
    color: #ff6b6b;
  }
  .success-message {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid rgba(40, 167, 69, 0.3);
    color: #51cf66;
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<div class="signup-container">
  <div>
    <a href="index.php" class="back-to-home">← Retour à l'accueil</a>
    <div class="signup-card">
      <h1>CRÉE TON COMPTE</h1>
      <p>Rejoins la communauté Calisthenics du Sénégal et commence ton voyage.</p>
      
      <?php if ($error_message): ?>
        <div class="message error-message">
          ❌ <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="message success-message">
          ✅ <?php echo htmlspecialchars($success_message); ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="signup.php" novalidate>
        <div class="form-group">
          <label for="nom">Nom Complet</label>
          <input type="text" id="nom" name="nom" placeholder="Ex: Ibrahima Diallo" required value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="ton@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        
        <div class="form-group">
          <label for="telephone">Téléphone</label>
          <input type="tel" id="telephone" name="telephone" placeholder="+221 77 123 45 67" value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
        </div>
        
        <div class="form-group">
          <label for="region">Région</label>
          <select id="region" name="region" required>
            <option value="">-- Sélectionne ta région --</option>
            <option value="Dakar" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Dakar') ? 'selected' : ''; ?>>Dakar</option>
            <option value="Thiès" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Thiès') ? 'selected' : ''; ?>>Thiès</option>
            <option value="Saint-Louis" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Saint-Louis') ? 'selected' : ''; ?>>Saint-Louis</option>
            <option value="Kaolack" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Kaolack') ? 'selected' : ''; ?>>Kaolack</option>
            <option value="Ziguinchor" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Ziguinchor') ? 'selected' : ''; ?>>Ziguinchor</option>
            <option value="Tambacounda" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Tambacounda') ? 'selected' : ''; ?>>Tambacounda</option>
            <option value="Kolda" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Kolda') ? 'selected' : ''; ?>>Kolda</option>
            <option value="Matam" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Matam') ? 'selected' : ''; ?>>Matam</option>
            <option value="Louga" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Louga') ? 'selected' : ''; ?>>Louga</option>
            <option value="Fatick" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Fatick') ? 'selected' : ''; ?>>Fatick</option>
            <option value="Autre" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Autre') ? 'selected' : ''; ?>>Autre</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="niveau">Niveau d'expérience</label>
          <select id="niveau" name="niveau" required>
            <option value="">-- Ton niveau --</option>
            <option value="Débutant" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] === 'Débutant') ? 'selected' : ''; ?>>Débutant</option>
            <option value="Intermédiaire" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] === 'Intermédiaire') ? 'selected' : ''; ?>>Intermédiaire</option>
            <option value="Avancé" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] === 'Avancé') ? 'selected' : ''; ?>>Avancé</option>
            <option value="Expert" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] === 'Expert') ? 'selected' : ''; ?>>Expert</option>
            <option value="Élite" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] === 'Élite') ? 'selected' : ''; ?>>Élite</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        
        <button type="submit" class="signup-btn">Créer le compte</button>
        
        <div class="login-link">
          Tu as déjà un compte? <a href="login.php">Connecte-toi</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/script.js"></script>
<script src="assets/js/validation.js"></script>
</body>
</html>
