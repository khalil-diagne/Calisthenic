<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/check_session.php';
require_once __DIR__ . '/includes/auth.php';

$user = $_SESSION['user_data'];
$message = '';
$error = '';

// Traiter les modifications du profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'update_profile') {
        $nom = trim($_POST['nom'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $region = trim($_POST['region'] ?? '');
        $niveau = trim($_POST['niveau'] ?? '');
        
        if (empty($nom)) {
            $error = "Le nom ne peut pas être vide";
        } else {
            $result = update_profile($_SESSION['user_id'], $nom, $telephone, $region, $niveau);
            if ($result['success']) {
                $message = $result['message'];
                $user = $_SESSION['user_data'];
            } else {
                $error = $result['message'];
            }
        }
    }
    
    elseif ($_POST['action'] === 'change_password') {
        $old_pwd = $_POST['old_password'] ?? '';
        $new_pwd = $_POST['new_password'] ?? '';
        $confirm_pwd = $_POST['confirm_password'] ?? '';
        
        if (empty($old_pwd)) {
            $error = "Veuillez entrer votre ancien mot de passe";
        } elseif (empty($new_pwd)) {
            $error = "Veuillez entrer votre nouveau mot de passe";
        } elseif (strlen($new_pwd) < 6) {
            $error = "Le nouveau mot de passe doit avoir au moins 6 caractères";
        } elseif ($new_pwd !== $confirm_pwd) {
            $error = "Les mots de passe ne correspondent pas";
        } else {
            $result = change_password($_SESSION['user_id'], $old_pwd, $new_pwd);
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon Profil — <?php echo SITE_NAME; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .profile-container {
    min-height: 100vh;
    background: var(--noir);
    padding: 2rem;
  }
  .profile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,215,0,0.1);
  }
  .profile-title h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2rem;
    color: var(--blanc);
    margin: 0;
  }
  .profile-title p {
    color: var(--gris-clair);
    margin: 0.5rem 0 0 0;
  }
  .profile-nav a {
    color: var(--gris-clair);
    text-decoration: none;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
    border: 1px solid rgba(255,215,0,0.2);
    margin-left: 0.5rem;
  }
  .profile-nav a:hover {
    color: var(--jaune);
    border-color: var(--jaune);
  }
  .profile-nav a.logout {
    background: rgba(255,69,0,0.1);
    color: #ff6b6b;
  }
  
  .profile-content {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .profile-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
  }
  
  .profile-sidebar {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 2rem;
    clip-path: polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%);
    height: fit-content;
  }
  
  .profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, var(--jaune), var(--vert));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto 1rem;
    font-weight: bold;
    color: var(--noir);
  }
  
  .profile-name {
    text-align: center;
    color: var(--blanc);
    font-weight: 600;
    margin-bottom: 1rem;
  }
  
  .profile-stats {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .stat {
    background: rgba(255,215,0,0.05);
    border-left: 3px solid var(--jaune);
    padding: 1rem;
    border-radius: 4px;
  }
  
  .stat-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--gris-clair);
  }
  
  .stat-value {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--jaune);
    margin-top: 0.3rem;
  }
  
  .profile-main {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }
  
  .profile-card {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 2rem;
    clip-path: polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%);
  }
  
  .card-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.5rem;
    color: var(--blanc);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,215,0,0.1);
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
    box-sizing: border-box;
    transition: border-color 0.2s;
  }
  
  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: var(--jaune);
    background: rgba(255,215,0,0.05);
  }
  
  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  
  .btn-submit {
    padding: 1rem 2rem;
    background: var(--jaune);
    color: var(--noir);
    border: none;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    clip-path: polygon(8px 0%, 100% 0%, calc(100% - 8px) 100%, 0% 100%);
    transition: background 0.2s;
  }
  
  .btn-submit:hover {
    background: var(--vert);
  }
  
  .message {
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 4px;
    font-size: 0.9rem;
  }
  
  .error {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.3);
    color: #ff6b6b;
  }
  
  .success {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid rgba(40, 167, 69, 0.3);
    color: #51cf66;
  }
  
  .info-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(255,215,0,0.05);
  }
  
  .info-label {
    color: var(--gris-clair);
    font-size: 0.9rem;
  }
  
  .info-value {
    color: var(--blanc);
    font-weight: 600;
  }
  
  @media (max-width: 768px) {
    .profile-grid {
      grid-template-columns: 1fr;
    }
    .form-row {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<div class="profile-container">
  <div class="profile-header">
    <div class="profile-title">
      <h1>MON COMPTE</h1>
      <p>Gère ton profil et tes préférences</p>
    </div>
    <div class="profile-nav">
      <a href="spots.php" style="color:var(--blanc); border-color:rgba(255,255,255,0.2);">📍 Spots</a>
      <a href="coach_ia.php" style="color:var(--jaune); border-color:var(--jaune);">✨ Coach IA</a>
      <a href="index.php">← Retour</a>
      <a href="logout.php" class="logout">Déconnexion</a>
    </div>
  </div>
  
  <div class="profile-content">
    <div class="profile-grid">
      <!-- Sidebar -->
      <div class="profile-sidebar">
        <div class="profile-avatar"><?php echo strtoupper(substr($user['nom_complet'], 0, 1)) . strtoupper(substr(explode(' ', $user['nom_complet'])[1] ?? 'U', 0, 1)); ?></div>
        <div class="profile-name"><?php echo htmlspecialchars($user['nom_complet']); ?></div>
        
        <div class="profile-stats">
          <div class="stat">
            <div class="stat-label">Niveau</div>
            <div class="stat-value"><?php echo htmlspecialchars($user['niveau']); ?></div>
          </div>
          <div class="stat">
            <div class="stat-label">Région</div>
            <div class="stat-value"><?php echo htmlspecialchars($user['region']); ?></div>
          </div>
          <div class="stat">
            <div class="stat-label">Membre depuis</div>
            <div class="stat-value"><?php echo date('d/m/Y', strtotime($user['date_creation'])); ?></div>
          </div>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="profile-main">
        <!-- Messages -->
        <?php if ($message): ?>
          <div class="message success">✅ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
          <div class="message error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Programme Actif -->
        <?php
        if (function_exists('get_active_program')) {
            $active_prog = get_active_program($_SESSION['user_id']);
            if ($active_prog) {
                $completed = get_completed_workouts($_SESSION['user_id'], $active_prog['programme_id']);
                $prog_percent = calculate_progress($completed, 16);
                $prog_name = ucwords(str_replace('-', ' ', $active_prog['programme_id']));
                ?>
                <div class="profile-card" style="margin-bottom:2rem; border-color:var(--vert);">
                  <div class="card-title" style="color:var(--vert); display:flex; justify-content:space-between; align-items:center;">
                    <span>🔥 Entraînement en cours</span>
                    <span style="font-size:1.2rem; color:var(--blanc);"><?php echo $prog_percent; ?>%</span>
                  </div>
                  <h3 style="font-family:'Bebas Neue',sans-serif; font-size:2.5rem; margin-bottom:1rem; letter-spacing:1px; color:var(--blanc);">
                    <?php echo htmlspecialchars($prog_name); ?>
                  </h3>
                  <div style="background:rgba(255,255,255,0.1); height:8px; border-radius:4px; overflow:hidden; margin-bottom:1.5rem;">
                    <div style="background:var(--vert); height:100%; width:<?php echo $prog_percent; ?>%; transition:width 1s;"></div>
                  </div>
                  <a href="workout_session.php?prog=<?php echo htmlspecialchars($active_prog['programme_id']); ?>" class="btn-submit" style="display:inline-block; text-align:center; text-decoration:none; width:100%; box-sizing:border-box;">Reprendre ma séance</a>
                </div>
                <?php
            } else {
                ?>
                <div class="profile-card" style="margin-bottom:2rem;">
                  <div class="card-title">Entraînement en cours</div>
                  <p style="color:var(--gris-clair); line-height:1.6; margin-bottom:1.5rem;">Tu n'as aucun programme actif pour le moment. Choisis une spécialité et commence ta transformation !</p>
                  <a href="index.php#programmes" class="btn-submit" style="display:inline-block; text-align:center; text-decoration:none; background:transparent; border:1px solid var(--jaune); color:var(--jaune); width:100%; box-sizing:border-box;">Découvrir les programmes</a>
                </div>
                <?php
            }
        }
        ?>
        
        <!-- Éditer Profil -->
        <div class="profile-card">
          <div class="card-title">Informations Personnelles</div>
          
          <form method="POST" action="profile.php">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-row">
              <div class="form-group">
                <label for="nom">Nom Complet</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom_complet']); ?>" required>
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
              </div>
              <div class="form-group">
                <label for="region">Région</label>
                <select id="region" name="region" required>
                  <option value="<?php echo htmlspecialchars($user['region']); ?>" selected><?php echo htmlspecialchars($user['region']); ?></option>
                  <option value="Dakar">Dakar</option>
                  <option value="Thiès">Thiès</option>
                  <option value="Saint-Louis">Saint-Louis</option>
                  <option value="Kaolack">Kaolack</option>
                  <option value="Ziguinchor">Ziguinchor</option>
                  <option value="Tambacounda">Tambacounda</option>
                  <option value="Kolda">Kolda</option>
                  <option value="Matam">Matam</option>
                  <option value="Louga">Louga</option>
                  <option value="Fatick">Fatick</option>
                  <option value="Autre">Autre</option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label for="niveau">Niveau d'expérience</label>
              <select id="niveau" name="niveau" required>
                <option value="<?php echo htmlspecialchars($user['niveau']); ?>" selected><?php echo htmlspecialchars($user['niveau']); ?></option>
                <option value="Débutant">Débutant</option>
                <option value="Intermédiaire">Intermédiaire</option>
                <option value="Avancé">Avancé</option>
                <option value="Expert">Expert</option>
                <option value="Élite">Élite</option>
              </select>
            </div>
            
            <button type="submit" class="btn-submit">Mettre à jour</button>
          </form>
        </div>
        
        <!-- Changer Mot de Passe -->
        <div class="profile-card">
          <div class="card-title">Sécurité</div>
          
          <form method="POST" action="profile.php">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
              <label for="old_password">Ancien mot de passe</label>
              <input type="password" id="old_password" name="old_password" required>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" required>
              </div>
              <div class="form-group">
                <label for="confirm_password">Confirmer</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
              </div>
            </div>
            
            <button type="submit" class="btn-submit">Changer le mot de passe</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
