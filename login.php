<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$flash = get_flash_message();
$error_message = $flash && $flash['type'] === 'error' ? $flash['text'] : '';
$success_message = $flash && $flash['type'] === 'success' ? $flash['text'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_valid_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $error_message = "Veuillez entrer votre email";
    } elseif (empty($password)) {
        $error_message = "Veuillez entrer votre mot de passe";
    } else {
        $result = login_user($email, $password);

        if ($result['success']) {
            set_flash_message($result['message'], 'success');
            $redirect = safe_redirect_target($_GET['redirect'] ?? '');
            header('Location: ' . $redirect);
            exit;
        }

        $error_message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — <?php echo SITE_NAME; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: var(--noir);
  }
  .login-card {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 3rem;
    max-width: 500px;
    width: 100%;
    clip-path: polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%);
  }
  .login-card h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: var(--blanc);
  }
  .login-card p {
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
  .form-group input {
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
  .form-group input:focus {
    outline: none;
    border-color: var(--jaune);
    background: rgba(255,215,0,0.05);
  }
  .login-btn {
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
  .login-btn:hover {
    background: var(--vert);
  }
  .signup-link {
    text-align: center;
    color: var(--gris-clair);
    font-size: 0.9rem;
  }
  .signup-link a {
    color: var(--jaune);
    text-decoration: none;
    transition: color 0.2s;
  }
  .signup-link a:hover {
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

<div class="login-container">
  <div>
    <a href="index.php" class="back-to-home">← Retour à l'accueil</a>
    <div class="login-card">
      <h1>CONNEXION</h1>
      <p>Bienvenue dans la communauté Calisthenics du Sénégal</p>
      
      <?php if ($error_message): ?>
        <div class="message error-message">
          ❌ <?php echo h($error_message); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="message success-message">
          ✅ <?php echo h($success_message); ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="login.php" novalidate>
        <?php echo csrf_input(); ?>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="ton@email.com" required value="<?php echo isset($_POST['email']) ? h($_POST['email']) : ''; ?>">
        </div>
        
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        
        <button type="submit" class="login-btn">Se connecter</button>
        
        <div class="signup-link">
          Pas encore de compte? <a href="signup.php">Rejoins-nous</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
