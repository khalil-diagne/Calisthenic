<?php
require_once __DIR__ . '/includes/config.php';

$user_id = get_user_session();
$is_active = false;
$error = '';

if ($user_id && function_exists('get_specific_active_program')) {
    $prog_data = get_specific_active_program($user_id, 'skill-builder');
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
        $res = start_program($user_id, 'skill-builder');
        if ($res['success']) {
            header('Location: workout_session.php?prog=skill-builder');
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
<title>Skill Builder — Calisthenics Senegal</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .program-hero {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8rem 4rem;
    background: linear-gradient(135deg, rgba(204,32,39,0.1), rgba(255,215,0,0.05));
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .program-hero::before {
    content: 'SKILL\ABUILDER';
    position: absolute;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 20vw;
    color: rgba(204,32,39,0.08);
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
    color: var(--rouge);
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
    border-bottom: 3px solid var(--rouge);
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
    border: 1px solid rgba(204,32,39,0.2);
  }
  .stat-value {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    color: var(--rouge);
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
    border-left: 4px solid var(--rouge);
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
  .skills-progression {
    background: var(--noir3);
    padding: 2rem;
    margin-bottom: 2rem;
  }
  .skill-level {
    margin-bottom: 2rem;
  }
  .skill-level h4 {
    color: var(--rouge);
    font-weight: 700;
    margin-bottom: 0.5rem;
  }
  .skill-items {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
  }
  .skill-tag {
    padding: 0.5rem 1rem;
    background: rgba(204,32,39,0.2);
    border: 1px solid var(--rouge);
    color: var(--blanc);
    font-size: 0.9rem;
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
    background: var(--rouge);
    color: var(--blanc);
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
    color: var(--noir);
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
    border-color: var(--rouge);
    color: var(--rouge);
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
    <h1>🔥 <span class="color-text">SKILL BUILDER</span></h1>
    <p>Maîtrise les skills d'élite. Un programme de 12 semaines pour débloquer les mouvements avancés.</p>
  </div>
</div>

<div class="program-container">
  <div class="program-section">
    <h2>📊 Vue d'ensemble</h2>
    <div class="stats-grid">
      <div class="stat-box">
        <div class="stat-value">12</div>
        <div class="stat-label">Semaines</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">3x</div>
        <div class="stat-label">Par Semaine</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">60min</div>
        <div class="stat-label">Par Session</div>
      </div>
      <div class="stat-box">
        <div class="stat-value">92%</div>
        <div class="stat-label">Complé. Type</div>
      </div>
    </div>
  </div>

  <div class="program-section">
    <h2>💪 Qu'est-ce que tu vas apprendre?</h2>
    <div class="features-list">
      <div class="feature-item">
        <div class="feature-icon">🌟</div>
        <div class="feature-text">
          <h3>Muscle-Up complet</h3>
          <p>Maîtriser le movement explosif ultime qui combine traction et poussée.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🤸</div>
        <div class="feature-text">
          <h3>Front Lever & Back Lever</h3>
          <p>Débloquer les isométries les plus difficiles avec une progression structurée.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🏃</div>
        <div class="feature-text">
          <h3>Planche & Handstand</h3>
          <p>Développer la force et l'équilibre pour des skills avancés de stabilité.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🎯</div>
        <div class="feature-text">
          <h3>Progressions intelligentes</h3>
          <p>Suivre des progressions éprouvées pour atteindre chaque skill sans blessure.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="program-section">
    <h2>🏆 Skills que tu vas débloquer</h2>
    <div class="skills-progression">
      <div class="skill-level">
        <h4>Semaines 1-4: Fondations</h4>
        <div class="skill-items">
          <span class="skill-tag">Muscle-Up Progressions</span>
          <span class="skill-tag">Front Lever Hold</span>
          <span class="skill-tag">Handstand Hold 30s+</span>
          <span class="skill-tag">Planche Progressions</span>
        </div>
      </div>
      <div class="skill-level">
        <h4>Semaines 5-8: Développement</h4>
        <div class="skill-items">
          <span class="skill-tag">Muscle-Up Clean</span>
          <span class="skill-tag">Front Lever Progression</span>
          <span class="skill-tag">Handstand Walks</span>
          <span class="skill-tag">Back Lever Progressions</span>
        </div>
      </div>
      <div class="skill-level">
        <h4>Semaines 9-12: Maîtrise</h4>
        <div class="skill-items">
          <span class="skill-tag">Muscle-Up Reps</span>
          <span class="skill-tag">Front Lever Hold</span>
          <span class="skill-tag">Handstand Push-Ups</span>
          <span class="skill-tag">Back Lever Hold</span>
        </div>
      </div>
    </div>
  </div>

  <div class="program-section">
    <h2>✅ Ce qui est inclus</h2>
    <div class="features-list">
      <div class="feature-item">
        <div class="feature-icon">🎥</div>
        <div class="feature-text">
          <h3>Vidéos détaillées</h3>
          <p>Chaque progression avec form parfaite et conseils pour éviter les erreurs.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🧘</div>
        <div class="feature-text">
          <h3>Mobilité & prévention</h3>
          <p>Routines de stretching et de mobilité pour prévenir les blessures.</p>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">👨‍🏫</div>
        <div class="feature-text">
          <h3>Coaching personnel</h3>
          <p>Support communautaire pour chaque progression et feedback sur tes vidéos.</p>
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
      <a href="workout_session.php?prog=skill-builder" class="btn-start" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">✅ Reprendre l'entraînement</a>
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

