<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/check_session.php'; // Protège la page

$user_id = $_SESSION['user_id'];
$prog_id = $_GET['prog'] ?? 'street-power';

$programLanding = [
    'street-power' => 'programme_street_power.php',
    'skill-builder' => 'programme_skill_builder.php',
    'endurance-tropicale' => 'programme_endurance.php',
];

// Vérifier si le programme est actif pour cet utilisateur
$prog_data = get_specific_active_program($user_id, $prog_id);
if (!$prog_data) {
    $landing = $programLanding[$prog_id] ?? 'index.php';
    header('Location: ' . $landing);
    exit;
}

// Logique pour sauvegarder une séance
$flash = get_flash_message();
$message = $flash && $flash['type'] === 'success' ? $flash['text'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_workout') {
    require_valid_csrf();
    $semaine = (int)$_POST['semaine'];
    $jour = (int)$_POST['jour'];
    $notes = trim($_POST['notes'] ?? '');
    
    $res = log_workout($user_id, $prog_id, $semaine, $jour, $notes);
    if ($res['success']) {
        $message = "Séance enregistrée avec succès ! Beau travail 💪";
    }
}

// Récupérer les séances complétées
$completed = get_completed_workouts($user_id, $prog_id);
$progress_percent = calculate_progress($completed, 16); // Suppose 16 séances

// Trouver la prochaine séance à faire
$current_week = 1;
$current_day = 1;

for ($w = 1; $w <= 4; $w++) {
    for ($d = 1; $d <= 4; $d++) {
        if (!isset($completed[$w][$d])) {
            $current_week = $w;
            $current_day = $d;
            break 2; // Sort des deux boucles
        }
    }
}

// Structure simulée d'un workout (adaptée selon le niveau)
$niveau = $_SESSION['user_data']['niveau'] ?? 'Débutant';

if ($niveau === 'Débutant') {
    $workout_today = [
        ['nom' => 'Échauffement Articulaire', 'series' => 1, 'reps' => '5 min', 'repos' => '0s'],
        ['nom' => 'Pompes Inclinées ou sur les Genoux', 'series' => 3, 'reps' => '8-10', 'repos' => '60s'],
        ['nom' => 'Tractions Australiennes (Sous la barre)', 'series' => 3, 'reps' => '6-8', 'repos' => '90s'],
        ['nom' => 'Gainage Ventral (Planche)', 'series' => 3, 'reps' => '30s', 'repos' => '45s']
    ];
    $encouragement = "🌱 <strong>Astuce :</strong> La constance bat l'intensité. Concentrez-vous sur la forme parfaite plutôt que sur le nombre de répétitions !";
} else {
    $workout_today = [
        ['nom' => 'Pull-Ups', 'series' => 4, 'reps' => 'Max', 'repos' => '90s'],
        ['nom' => 'Dips', 'series' => 4, 'reps' => '10-12', 'repos' => '90s'],
        ['nom' => 'Handstand Hold', 'series' => 3, 'reps' => '30s', 'repos' => '60s'],
        ['nom' => 'L-Sit', 'series' => 3, 'reps' => '15s', 'repos' => '60s']
    ];
    $encouragement = "🔥 Repousse tes limites. C'est l'heure d'écraser cet entraînement.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Session d'entraînement — <?php echo SITE_NAME; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  body { background: var(--noir); color: var(--blanc); }
  .session-container { max-width: 800px; margin: 0 auto; padding: 6rem 2rem 4rem; }
  
  .session-header {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 2rem;
    clip-path: polygon(20px 0%, 100% 0%, calc(100% - 20px) 100%, 0% 100%);
    margin-bottom: 2rem;
    text-align: center;
  }
  
  .s-title { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; color: var(--jaune); margin-bottom: 0.5rem; }
  .s-subtitle { color: var(--gris-clair); text-transform: uppercase; letter-spacing: 2px; font-size: 0.85rem; font-weight: bold; }
  
  .progress-wrap { margin-top: 1.5rem; }
  .progress-bar { background: rgba(255,255,255,0.1); height: 8px; border-radius: 4px; overflow: hidden; margin-top: 0.5rem; }
  .progress-fill { background: var(--vert); height: 100%; transition: width 1s ease; }
  
  .exercise-list { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 3rem; }
  
  .ex-card {
    background: var(--noir2);
    border: 1px solid rgba(255,255,255,0.05);
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
    cursor: pointer;
  }
  .ex-card:hover { border-color: rgba(255,215,0,0.3); background: rgba(255,215,0,0.02); }
  .ex-card.done { border-color: var(--vert); background: rgba(0,168,79,0.05); opacity: 0.6; }
  
  .ex-info h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.5rem; margin-bottom: 0.3rem; }
  .ex-meta { color: var(--gris-clair); font-family: 'Space Mono', monospace; font-size: 0.85rem; }
  
  .ex-check {
    width: 30px; height: 30px;
    border: 2px solid var(--jaune);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: transparent;
    transition: all 0.2s;
  }
  .ex-card.done .ex-check { background: var(--vert); border-color: var(--vert); color: var(--noir); }
  
  .finish-form { background: var(--noir3); padding: 2rem; border-top: 3px solid var(--vert); }
  .finish-form h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.8rem; margin-bottom: 1rem; }
  .form-group textarea { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 1rem; font-family: 'Syne', sans-serif; min-height: 100px; margin-bottom: 1.5rem; }
  .form-group textarea:focus { outline: none; border-color: var(--jaune); }
  
  .btn-save {
    width: 100%; padding: 1.2rem; background: var(--vert); color: var(--noir);
    font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1rem; text-transform: uppercase;
    border: none; cursor: pointer; clip-path: polygon(12px 0%, 100% 0%, calc(100% - 12px) 100%, 0% 100%); transition: background 0.2s;
  }
  .btn-save:hover { background: var(--jaune); }
  .btn-save:disabled { background: var(--gris); cursor: not-allowed; opacity: 0.5; }
  
  .alert-success { background: rgba(0,168,79,0.1); border: 1px solid var(--vert); color: #51cf66; padding: 1rem; margin-bottom: 2rem; text-align: center; font-weight: bold; }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<nav>
  <div class="nav-logo"><span class="v">CALI</span><span class="j">THEN</span><span class="r">ICS</span>&nbsp;SN</div>
  <ul class="nav-links">
    <li><a href="profile.php">← Mon Profil</a></li>
  </ul>
</nav>

<div class="session-container">
  
  <?php if($message): ?>
    <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <div class="session-header">
    <div class="s-subtitle">Progression globale : <?php echo $progress_percent; ?>%</div>
    <div class="s-title"><?php echo strtoupper(str_replace('-', ' ', $prog_id)); ?></div>
    <div class="s-subtitle" style="color:var(--vert); font-size:1.1rem; margin-top:1rem;">
      📍 Semaine <?php echo $current_week; ?> - Jour <?php echo $current_day; ?>
    </div>
    <div class="progress-wrap">
      <div class="progress-bar"><div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div></div>
    </div>
  </div>

  <div style="background:rgba(255,215,0,0.05); border-left:3px solid var(--jaune); padding:1rem; margin-bottom:2rem; font-size:0.95rem; color:var(--gris-clair);">
    <?php echo $encouragement; ?>
  </div>

  <div class="exercise-list">
    <?php foreach($workout_today as $index => $ex): ?>
      <div class="ex-card" onclick="toggleEx(this)">
        <div class="ex-info">
          <h3><?php echo htmlspecialchars($ex['nom']); ?></h3>
          <div class="ex-meta">
            <span><?php echo $ex['series']; ?> Séries</span> × 
            <span style="color:var(--jaune); font-weight:bold;"><?php echo $ex['reps']; ?></span>
            | ⏱ Repos: <?php echo $ex['repos']; ?>
          </div>
        </div>
        <div class="ex-check">✓</div>
      </div>
    <?php endforeach; ?>
  </div>
  
  <form method="POST" class="finish-form">
    <?php echo csrf_input(); ?>
    <h3>Fin de session</h3>
    <input type="hidden" name="action" value="log_workout">
    <input type="hidden" name="semaine" value="<?php echo $current_week; ?>">
    <input type="hidden" name="jour" value="<?php echo $current_day; ?>">
    
    <div class="form-group">
      <textarea name="notes" placeholder="Notes optionnelles (ex: 'Reps max à 8 aujourd'hui', 'Bon feeling')"></textarea>
    </div>
    
    <button type="submit" class="btn-save" id="saveBtn">Enregistrer la séance</button>
  </form>

</div>

<script>
  function toggleEx(el) {
    el.classList.toggle('done');
  }
</script>
<script src="assets/js/script.js"></script>
</body>
</html>
