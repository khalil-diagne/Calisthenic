<?php
require_once __DIR__ . '/includes/config.php';

$user_id = get_user_session();
$is_active = false;
$error = '';

if ($user_id && function_exists('get_specific_active_program')) {
    $prog_data = get_specific_active_program($user_id, 'street-power');
    if ($prog_data) {
        $is_active = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'start_program') {
    require_valid_csrf();
    if (!$user_id) {
        header('Location: login.php');
        exit;
    }
    if (function_exists('start_program')) {
        $res = start_program($user_id, 'street-power');
        if ($res['success']) {
            header('Location: workout_session.php?prog=street-power');
            exit;
        } else {
            $error = $res['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Street Power — Calisthenics Senegal</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .program-hero {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8rem 4rem;
    background: linear-gradient(135deg, rgba(0,168,79,0.1), rgba(255,215,0,0.05));
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .program-hero::before {
    content: 'STREET\APOWER';
    position: absolute;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 20vw;
    color: rgba(0,168,79,0.08);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    white-space: pre;
    pointer-events: none;
  }
  .program-hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
  }
  .program-hero h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(3rem, 8vw, 5rem);
    margin-bottom: 1rem;
    letter-spacing: 2px;
  }
  .program-hero .color-text {
    color: var(--vert);
  }
  .program-hero p {
    font-size: 1.2rem;
    color: var(--gris-clair);
    line-height: 1.8;
  }
  .program-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 4rem;
  }
  .program-section {
    margin-bottom: 4rem;
  }
  .program-section h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2rem;
    margin-bottom: 2rem;
    letter-spacing: 1px;
    border-bottom: 3px solid var(--vert);
    padding-bottom: 1rem;
  }
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 3rem;
  }
  .stat-box {
    background: var(--noir3);
    padding: 1.5rem;
    text-align: center;
    border: 1px solid rgba(0,168,79,0.2);
  }
  .stat-value {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    color: var(--vert);
    margin-bottom: 0.5rem;
  }
  .stat-label {
    font-size: 0.85rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--gris-clair);
  }
  .features-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }
  .feature-item {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    background: var(--noir3);
    border-left: 4px solid var(--vert);
  }
  .feature-icon {
    font-size: 2rem;
    flex-shrink: 0;
  }
  .feature-text h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: var(--blanc);
  }
  .feature-text p {
    color: var(--gris-clair);
    line-height: 1.6;
  }
  .week-schedule {
    background: var(--noir3);
    padding: 2rem;
    margin-bottom: 2rem;
  }
  .week-day {
    display: grid;
    grid-template-columns: 100px 1fr;
    gap: 2rem;
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
  }
  .day-label {
    font-weight: 700;
    color: var(--vert);
    font-size: 0.9rem;
  }
  .day-content p {
    color: var(--gris-clair);
    line-height: 1.6;
  }
  .cta-buttons {
    display: flex;
    gap: 1.5rem;
    margin-top: 3rem;
    flex-wrap: wrap;
  }
  .btn-start {
    flex: 1;
    min-width: 200px;
    padding: 1.2rem;
    background: var(--vert);
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
  }
  .btn-start:hover {
    background: var(--jaune);
  }
  .btn-back {
    flex: 1;
    min-width: 200px;
    padding: 1.2rem;
    background: transparent;
    color: var(--blanc);
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.9rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    border: 1px solid rgba(255,255,255,0.2);
    cursor: pointer;
    transition: border-color 0.2s, color 0.2s;
    text-decoration: none;
  }
  .btn-back:hover {
    border-color: var(--vert);
    color: var(--vert);
  }
  @media (max-width: 768px) {
    .program-hero {
      padding: 5rem 1.5rem;
    }
    .program-container {
      padding: 2rem 1.5rem;
    }
    .stats-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .cta-buttons {
      flex-direction: column;
    }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<div class="program-hero">
  <div class="program-hero-content">
    <h1>⚡ <span class="color-text">STREET POWER</span></h1>
    <p>Force brute depuis la rue. Un programme de 8 semaines pour développer ta puissance musculaire.</p>
  </div>
</div>

<div class="program-container">
  <div class="program-section">
    <h2>📊 Vue d'ensemble</h2>
    <div class="stats-grid">
      <div class="stat-box">
        <div class="stat-value">8</div>
        <div class="stat-label">Semaines</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">4x</div>
        <div class="stat-label">Par Semaine</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">45min</div>
        <div class="stat-label">Par Session</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">78%</div>
        <div class="stat-label">Complé. Type</div>
      </div>
    </div>
  </div>

  <div class="program-section">
    <h2>💪 Qu'est-ce que tu vas apprendre?</h2>
    <div class="features-list">
      <div class="feature-item">
        <div class="feature-icon">🏋️</div>
        <div class="feature-text">
          <h3>Augmenter ta force</h3>
          <p>Progresser sur les pull-ups, dips et autres exercices de base avec des méthodes éprouvées.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">💥</div>
        <div class="feature-text">
          <h3>Gagner du muscle</h3>
          <p>Construire une masse musculaire solide avec un volume d'entraînement progressif.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🔥</div>
        <div class="feature-text">
          <h3>Dominer les basics</h3>
          <p>Maîtriser les mouvements fondamentaux avant de passer aux skills avancés.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">📈</div>
        <div class="feature-text">
          <h3>Suivre ta progression</h3>
          <p>Tracker tes séries, tes reps et tes PR pour rester motivé et progresser vite.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="program-section">
    <h2>📅 Structure de la semaine type</h2>
    <div class="week-schedule">
      <div class="week-day">
        <div class="day-label">Jour 1</div>
        <div class="day-content">
          <p><strong>Push Day</strong> - Dips, Push-ups, Handstand Hold, Tricep Extensions</p>
        </div>
      </div>
      <div class="week-day">
        <div class="day-label">Jour 2</div>
        <div class="day-content">
          <p><strong>Pull Day</strong> - Pull-ups, Chin-ups, Rows, Lat Pulldowns</p>
        </div>
      </div>
      <div class="week-day">
        <div class="day-label">Jour 3</div>
        <div class="day-content">
          <p><strong>Repos</strong> - Récupération active ou stretching léger</p>
        </div>
      </div>
      <div class="week-day">
        <div class="day-label">Jour 4</div>
        <div class="day-content">
          <p><strong>Full Body</strong> - Combinaison de Push et Pull avec travail de core</p>
        </div>
      </div>
    </div>
  </div>

  <div class="program-section">
    <h2>✅ Ce qui est inclus</h2>
    <div class="features-list">
      <div class="feature-item">
        <div class="feature-icon">📱</div>
        <div class="feature-text">
          <h3>Suivi numérique</h3>
          <p>Enregistre tes workouts, suivis tes PRs et visualise ta progression en graphiques.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🎥</div>
        <div class="feature-text">
          <h3>Vidéos explicatives</h3>
          <p>Chaque exercice a des vidéos d'exécution pour éviter les blessures.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">👥</div>
        <div class="feature-text">
          <h3>Communauté</h3>
          <p>Partage tes progrès, reçois des encouragements et inspire d'autres athlètes.</p>
        </div>
      </div>
    </div>
  </div>

  <?php if($error): ?>
    <div style="background:rgba(255,69,0,0.1); color:#ff6b6b; padding:1rem; border:1px solid rgba(255,69,0,0.3); margin-top:2rem;">
      <?php echo h($error); ?>
    </div>
  <?php endif; ?>

  <div class="cta-buttons">
    <?php if ($is_active): ?>
      <a href="workout_session.php?prog=street-power" class="btn-start" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">✅ Reprendre l'entraînement</a>
    <?php else: ?>
      <form method="POST" style="flex:1; display:flex;">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="action" value="start_program">
        <button type="submit" class="btn-start">🚀 Démarrer le programme</button>
      </form>
    <?php endif; ?>
    <a href="index.php#programmes" class="btn-back" style="display:flex; align-items:center; justify-content:center;">← Voir les autres programmes</a>
  </div>
</div>

<script src="assets/js/script.js"></script>

</body>
</html>

