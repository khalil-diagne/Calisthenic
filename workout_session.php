<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/check_session.php';
require_once __DIR__ . '/includes/auth.php';

$user    = $_SESSION['user_data'];
$user_id = $_SESSION['user_id'];

// ─── Récupérer le programme IA à utiliser ──────────────────────────────────
// Par défaut : le plus récent. On peut aussi passer ?prog_id=X
$prog_id_param = isset($_GET['prog_id']) ? (int)$_GET['prog_id'] : 0;

$mysqli = connecter_db();
if ($prog_id_param > 0) {
    $stmt = $mysqli->prepare(
        "SELECT * FROM ia_programmes WHERE id = ? AND user_id = ? LIMIT 1"
    );
    $stmt->bind_param("ii", $prog_id_param, $user_id);
} else {
    $stmt = $mysqli->prepare(
        "SELECT * FROM ia_programmes WHERE user_id = ? ORDER BY created_at DESC LIMIT 1"
    );
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$programme = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Aucun programme IA → rediriger vers Coach IA
if (!$programme) {
    $mysqli->close();
    header('Location: coach_ia.php?no_prog=1');
    exit;
}

$ia_prog_id = (int)$programme['id'];

// ─── Séances déjà loggées pour CE programme IA ────────────────────────────
// On utilise programme_id = 'ia_' + id pour différencier des progs statiques
$ia_prog_slug = 'ia_' . $ia_prog_id;

$stmt = $mysqli->prepare(
    "SELECT jour, semaine, date_completion, notes FROM workout_logs
     WHERE user_id = ? AND programme_id = ? ORDER BY jour ASC"
);
$stmt->bind_param("is", $user_id, $ia_prog_slug);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$completed_jours = [];
foreach ($rows as $row) {
    $completed_jours[$row['jour']] = $row['date_completion'];
}

$total_jours   = (int)$programme['jours_semaine'];
$nb_completes  = count($completed_jours);
$progress_pct  = $total_jours > 0 ? min(100, round($nb_completes / $total_jours * 100)) : 0;

// ─── Parsing du contenu IA pour extraire les exercices par jour ───────────
function parse_ia_jours($contenu, $total_jours) {
    $jours = [];
    // Match chaque bloc "## Jour X"
    $pattern = '/##\s+[🗓️📅]*\s*Jour\s+(\d+)\s*[—\-–]?\s*(.*?)(?=\n##\s+[🗓️📅]*\s*Jour\s+\d|\n##\s+💡|\z)/su';
    preg_match_all($pattern, $contenu, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $num   = (int)$m[1];
        $titre = trim(strip_tags($m[2]));
        $bloc  = trim($m[0]);

        // Extraire les exercices depuis le tableau markdown
        $exercices = [];
        $table_pattern = '/\|\s*([^|]+)\s*\|\s*([^|]+)\s*\|\s*([^|]+)\s*\|\s*([^|]+)\s*\|/';
        preg_match_all($table_pattern, $bloc, $tmatch, PREG_SET_ORDER);
        foreach ($tmatch as $tr) {
            $nom = trim($tr[1]);
            if (preg_match('/^[-:\s]+$/', $nom) || strtolower($nom) === 'exercice') continue;
            $exercices[] = [
                'nom'    => $nom,
                'series' => trim($tr[2]),
                'reps'   => trim($tr[3]),
                'repos'  => trim($tr[4]),
            ];
        }

        // Extraire le focus
        $focus = '';
        if (preg_match('/\*\*Focus\s*:\*\*\s*(.+)/u', $bloc, $fm)) {
            $focus = trim($fm[1]);
        }

        // Extraire la récup
        $recup = '';
        if (preg_match('/\*\*[🧘]*\s*R[eé]cup[eé]ration\s*:\*\*\s*(.+)/u', $bloc, $rm)) {
            $recup = trim($rm[1]);
        }

        $jours[$num] = [
            'titre'     => $titre ?: "Jour {$num}",
            'focus'     => $focus,
            'exercices' => $exercices,
            'recup'     => $recup,
        ];
    }

    // Fallback: si le parsing échoue, créer des jours vides
    for ($i = 1; $i <= $total_jours; $i++) {
        if (!isset($jours[$i])) {
            $jours[$i] = ['titre' => "Jour {$i}", 'focus' => '', 'exercices' => [], 'recup' => ''];
        }
    }
    ksort($jours);
    return $jours;
}

$jours_parsed = parse_ia_jours($programme['contenu'], $total_jours);

// Trouver le prochain jour à faire
$jour_actif = 1;
for ($j = 1; $j <= $total_jours; $j++) {
    if (!isset($completed_jours[$j])) {
        $jour_actif = $j;
        break;
    }
}
// Si tout est fait → revenir au 1er
if ($nb_completes >= $total_jours) $jour_actif = 1;

// Jour sélectionné via GET
if (isset($_GET['jour']) && (int)$_GET['jour'] >= 1 && (int)$_GET['jour'] <= $total_jours) {
    $jour_actif = (int)$_GET['jour'];
}

// ─── Enregistrement d'une séance ──────────────────────────────────────────
$flash   = get_flash_message();
$message = $flash && $flash['type'] === 'success' ? $flash['text'] : '';
$msg_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_ia_workout') {
    require_valid_csrf();
    $j_log   = max(1, min($total_jours, (int)($_POST['jour_num'] ?? 1)));
    $notes   = trim($_POST['notes'] ?? '');

    $stmt = $mysqli->prepare(
        "INSERT IGNORE INTO workout_logs (user_id, programme_id, semaine, jour, notes)
         VALUES (?, ?, 1, ?, ?)"
    );
    $stmt->bind_param("isis", $user_id, $ia_prog_slug, $j_log, $notes);
    $ok = $stmt->execute();
    $stmt->close();
    $mysqli->close();

    if ($ok) {
        set_flash_message("✅ Séance Jour {$j_log} enregistrée ! Beau travail 💪", 'success');
    }
    header("Location: workout_session.php?prog_id={$ia_prog_id}&jour=" . min($j_log + 1, $total_jours));
    exit;
}

$mysqli->close();

$workout_actif = $jours_parsed[$jour_actif] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Session IA — <?php echo SITE_NAME; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  body { background: var(--noir); color: var(--blanc); }

  .ws-container { max-width: 900px; margin: 0 auto; padding: 6rem 1.5rem 4rem; }

  /* Header */
  .ws-header {
    background: linear-gradient(135deg, var(--noir3), #1a1a1a);
    border: 1px solid rgba(255,215,0,0.12);
    padding: 2rem;
    clip-path: polygon(20px 0%, 100% 0%, calc(100% - 20px) 100%, 0% 100%);
    margin-bottom: 2rem;
  }
  .ws-prog-meta {
    display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;
  }
  .ws-badge {
    background: rgba(255,215,0,0.08); border: 1px solid rgba(255,215,0,0.2);
    color: var(--jaune); padding: 0.3rem 0.8rem; border-radius: 20px;
    font-size: 0.75rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;
  }
  .ws-badge.green {
    background: rgba(0,168,79,0.1); border-color: rgba(0,168,79,0.3); color: #51cf66;
  }
  .ws-title {
    font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem;
    color: var(--blanc); margin: 0.5rem 0;
    display: flex; align-items: center; gap: 0.5rem;
  }
  .ws-subtitle { color: var(--gris-clair); font-size: 0.9rem; }

  /* Progress */
  .progress-section { margin-top: 1.2rem; }
  .progress-label {
    display: flex; justify-content: space-between;
    font-size: 0.8rem; color: var(--gris-clair); margin-bottom: 0.4rem;
    text-transform: uppercase; letter-spacing: 1px;
  }
  .progress-bar { background: rgba(255,255,255,0.08); height: 6px; border-radius: 3px; overflow: hidden; }
  .progress-fill { background: linear-gradient(90deg, var(--vert), var(--jaune)); height: 100%; transition: width 1s ease; }

  /* Sélecteur de jours */
  .jours-selector {
    display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 2rem;
  }
  .jour-btn {
    display: flex; flex-direction: column; align-items: center;
    padding: 0.8rem 1rem; min-width: 80px;
    background: var(--noir3); border: 1px solid rgba(255,255,255,0.08);
    color: var(--gris-clair); cursor: pointer; text-decoration: none;
    transition: all 0.2s;
    clip-path: polygon(8px 0%, 100% 0%, calc(100% - 8px) 100%, 0% 100%);
    position: relative;
  }
  .jour-btn:hover { border-color: rgba(255,215,0,0.3); color: var(--blanc); }
  .jour-btn.active { background: rgba(255,215,0,0.1); border-color: var(--jaune); color: var(--jaune); }
  .jour-btn.done { border-color: rgba(0,168,79,0.4); }
  .jour-btn.done .jour-num { color: #51cf66; }
  .jour-num { font-family: 'Bebas Neue', sans-serif; font-size: 1.5rem; line-height: 1; }
  .jour-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.2rem; }
  .jour-tick {
    position: absolute; top: -6px; right: -6px;
    background: var(--vert); color: var(--noir);
    width: 18px; height: 18px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; font-weight: bold;
  }

  /* Workout card */
  .workout-card {
    background: var(--noir3); border: 1px solid rgba(255,215,0,0.1);
    padding: 2rem;
    clip-path: polygon(0 0, 100% 0, 100% calc(100% - 20px), calc(100% - 20px) 100%, 0 100%);
    margin-bottom: 1.5rem;
  }
  .wc-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.5rem; padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,215,0,0.1);
  }
  .wc-title { font-family: 'Bebas Neue', sans-serif; font-size: 1.8rem; color: var(--jaune); }
  .wc-focus {
    background: rgba(255,215,0,0.06); border: 1px solid rgba(255,215,0,0.15);
    color: var(--jaune); padding: 0.3rem 0.8rem; font-size: 0.8rem;
    font-weight: bold; border-radius: 20px;
  }
  .wc-done-badge {
    background: rgba(0,168,79,0.15); border: 1px solid var(--vert);
    color: #51cf66; padding: 0.4rem 1rem; font-family: 'Syne', sans-serif;
    font-weight: bold; font-size: 0.85rem; border-radius: 4px;
  }

  /* Exercice cards */
  .ex-list { display: flex; flex-direction: column; gap: 0.8rem; margin-bottom: 1.5rem; }
  .ex-item {
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
    padding: 1.2rem 1.5rem;
    display: grid; grid-template-columns: 1fr auto;
    align-items: center; gap: 1rem;
    cursor: pointer; transition: all 0.25s; user-select: none;
  }
  .ex-item:hover { border-color: rgba(255,215,0,0.2); background: rgba(255,215,0,0.03); }
  .ex-item.done { border-color: rgba(0,168,79,0.3); background: rgba(0,168,79,0.04); }
  .ex-item.done .ex-name { text-decoration: line-through; opacity: 0.6; }

  .ex-name {
    font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1rem;
    margin-bottom: 0.4rem; transition: all 0.2s;
  }
  .ex-stats {
    display: flex; gap: 1rem; flex-wrap: wrap;
  }
  .ex-stat {
    font-family: 'Space Mono', monospace; font-size: 0.8rem; color: var(--gris-clair);
    display: flex; align-items: center; gap: 0.3rem;
  }
  .ex-stat span { color: var(--jaune); font-weight: bold; }

  .ex-check {
    width: 32px; height: 32px; border: 2px solid rgba(255,215,0,0.3);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 0; transition: all 0.2s; flex-shrink: 0;
  }
  .ex-item.done .ex-check {
    background: var(--vert); border-color: var(--vert);
    font-size: 1rem; color: var(--noir);
  }

  /* Pas d'exercices parsés → affichage brut */
  .raw-content {
    color: #c0c0c0; line-height: 1.8; font-size: 0.95rem;
  }
  .raw-content strong { color: var(--jaune); }

  /* Récup */
  .recup-box {
    background: rgba(0,168,79,0.06); border-left: 3px solid var(--vert);
    padding: 0.8rem 1.2rem; color: #a0e4b0; font-size: 0.9rem;
    margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;
  }

  /* Form log */
  .log-form {
    background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);
    padding: 1.5rem;
  }
  .log-form h3 {
    font-family: 'Bebas Neue', sans-serif; font-size: 1.5rem;
    margin-bottom: 1rem; color: var(--blanc);
    display: flex; align-items: center; gap: 0.5rem;
  }
  .log-textarea {
    width: 100%; background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1); color: var(--blanc);
    padding: 0.8rem 1rem; font-family: 'Syne', sans-serif; font-size: 0.9rem;
    min-height: 90px; resize: vertical; margin-bottom: 1rem; box-sizing: border-box;
  }
  .log-textarea:focus { outline: none; border-color: var(--jaune); }
  .log-textarea::placeholder { color: var(--gris-clair); }

  .btn-log {
    width: 100%; padding: 1rem; background: var(--vert); color: var(--noir);
    font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1rem;
    text-transform: uppercase; letter-spacing: 1px; border: none; cursor: pointer;
    clip-path: polygon(10px 0%, 100% 0%, calc(100% - 10px) 100%, 0% 100%);
    transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  }
  .btn-log:hover { background: var(--jaune); }
  .btn-log:disabled { opacity: 0.4; cursor: not-allowed; }

  /* Alert */
  .alert-success {
    background: rgba(0,168,79,0.1); border: 1px solid var(--vert);
    color: #51cf66; padding: 1rem 1.2rem; margin-bottom: 1.5rem;
    font-weight: bold; text-align: center; font-size: 0.95rem;
    animation: slideIn 0.4s ease;
  }
  .alert-info {
    background: rgba(255,215,0,0.06); border-left: 3px solid var(--jaune);
    color: var(--jaune); padding: 0.8rem 1.2rem; margin-bottom: 1.5rem;
    font-size: 0.85rem;
  }

  /* Programme terminé */
  .prog-complete {
    text-align: center; padding: 3rem 2rem;
    background: linear-gradient(135deg, rgba(0,168,79,0.08), rgba(255,215,0,0.05));
    border: 1px solid rgba(0,168,79,0.2);
    clip-path: polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%);
  }
  .prog-complete .icon { font-size: 4rem; display: block; margin-bottom: 1rem; }
  .prog-complete h2 { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; color: var(--jaune); margin-bottom: 0.5rem; }

  /* Actions bas */
  .ws-actions {
    display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;
  }
  .btn-secondary {
    padding: 0.7rem 1.5rem; color: var(--gris-clair);
    border: 1px solid rgba(255,255,255,0.1); text-decoration: none;
    font-family: 'Syne', sans-serif; font-weight: 600; font-size: 0.85rem;
    transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.4rem;
  }
  .btn-secondary:hover { color: var(--blanc); border-color: rgba(255,255,255,0.3); }

  @keyframes slideIn { from { opacity:0; transform: translateY(-8px); } to { opacity:1; transform: translateY(0); } }

  @media (max-width: 600px) {
    .jours-selector { gap: 0.4rem; }
    .jour-btn { min-width: 60px; padding: 0.6rem 0.6rem; }
    .wc-header { flex-direction: column; gap: 0.8rem; }
    .ex-item { grid-template-columns: 1fr; }
    .ex-check { display: none; }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<nav>
  <div class="nav-logo"><span class="v">CALI</span><span class="j">THEN</span><span class="r">ICS</span>&nbsp;SN</div>
  <ul class="nav-links">
    <li><a href="coach_ia.php">✨ Coach IA</a></li>
    <li><a href="coach_historique.php">📋 Historique</a></li>
    <li><a href="index.php">← Accueil</a></li>
  </ul>
</nav>

<div class="ws-container">

  <?php if ($message): ?>
    <div class="alert-success"><?php echo h($message); ?></div>
  <?php endif; ?>

  <!-- Header du programme -->
  <div class="ws-header">
    <div class="ws-prog-meta">
      <span class="ws-badge">🏅 <?php echo h($programme['niveau']); ?></span>
      <span class="ws-badge">🏆 <?php echo h($programme['objectif']); ?></span>
      <span class="ws-badge">📅 <?php echo $total_jours; ?> jours/sem</span>
      <span class="ws-badge">💪 <?php echo h($programme['parties_corps']); ?></span>
      <?php if ($nb_completes >= $total_jours): ?>
        <span class="ws-badge green">✅ Programme terminé !</span>
      <?php endif; ?>
    </div>
    <div class="ws-title">
      🏋️ SESSION D'ENTRAÎNEMENT IA
    </div>
    <div class="ws-subtitle">
      Programme généré le <?php echo (new DateTime($programme['created_at']))->format('d/m/Y à H:i'); ?>
    </div>

    <div class="progress-section">
      <div class="progress-label">
        <span>Progression du programme</span>
        <span style="color:var(--vert); font-weight:bold;"><?php echo $nb_completes; ?>/<?php echo $total_jours; ?> jours · <?php echo $progress_pct; ?>%</span>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" style="width:<?php echo $progress_pct; ?>%;"></div>
      </div>
    </div>
  </div>

  <!-- Sélecteur de jours -->
  <div class="jours-selector">
    <?php for ($j = 1; $j <= $total_jours; $j++):
      $is_done   = isset($completed_jours[$j]);
      $is_active = ($j === $jour_actif);
      $titre_j   = $jours_parsed[$j]['titre'] ?? "Jour {$j}";
      $short     = strlen($titre_j) > 10 ? substr($titre_j, 0, 10) . '…' : $titre_j;
    ?>
      <a href="?prog_id=<?php echo $ia_prog_id; ?>&jour=<?php echo $j; ?>"
         class="jour-btn <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_done ? 'done' : ''; ?>">
        <?php if ($is_done): ?>
          <span class="jour-tick">✓</span>
        <?php endif; ?>
        <span class="jour-num"><?php echo $j; ?></span>
        <span class="jour-label"><?php echo h($short); ?></span>
      </a>
    <?php endfor; ?>
  </div>

  <!-- Programme complet ? -->
  <?php if ($nb_completes >= $total_jours): ?>
    <div class="prog-complete">
      <span class="icon">🏆</span>
      <h2>Programme Complété !</h2>
      <p style="color:var(--gris-clair); margin-bottom:2rem;">
        Tu as terminé tous les <?php echo $total_jours; ?> jours de ce programme. <br>
        L'IA va adapter ta prochaine session à ta progression !
      </p>
      <a href="coach_ia.php" style="
        display:inline-block; padding:1rem 2.5rem;
        background:var(--jaune); color:var(--noir);
        font-family:'Syne',sans-serif; font-weight:800;
        text-decoration:none; text-transform:uppercase;
        letter-spacing:1px;
        clip-path:polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%);
      ">⚡ Générer un nouveau programme</a>
    </div>

  <?php elseif ($workout_actif): ?>

    <!-- Workout actif -->
    <div class="workout-card">
      <div class="wc-header">
        <div>
          <div class="wc-title">
            🗓️ JOUR <?php echo $jour_actif; ?> — <?php echo h(strtoupper($workout_actif['titre'])); ?>
          </div>
          <?php if ($workout_actif['focus']): ?>
            <div style="color:var(--gris-clair); font-size:0.85rem; margin-top:0.3rem;">
              💪 Focus : <strong style="color:var(--blanc);"><?php echo h($workout_actif['focus']); ?></strong>
            </div>
          <?php endif; ?>
        </div>
        <?php if (isset($completed_jours[$jour_actif])): ?>
          <div class="wc-done-badge">✅ Séance validée</div>
        <?php endif; ?>
      </div>

      <?php if (isset($completed_jours[$jour_actif])): ?>
        <div class="alert-info">
          Ce jour a été validé le <?php echo (new DateTime($completed_jours[$jour_actif]))->format('d/m/Y à H:i'); ?>.
          Tu peux quand même revoir les exercices ci-dessous.
        </div>
      <?php endif; ?>

      <!-- Exercices -->
      <?php if (!empty($workout_actif['exercices'])): ?>
        <div class="ex-list" id="exList">
          <?php foreach ($workout_actif['exercices'] as $i => $ex): ?>
            <div class="ex-item" id="ex-<?php echo $i; ?>" onclick="toggleEx(<?php echo $i; ?>)">
              <div>
                <div class="ex-name"><?php echo h($ex['nom']); ?></div>
                <div class="ex-stats">
                  <div class="ex-stat">📋 <span><?php echo h($ex['series']); ?></span> séries</div>
                  <div class="ex-stat">🔁 <span><?php echo h($ex['reps']); ?></span></div>
                  <div class="ex-stat">⏱ Repos: <span><?php echo h($ex['repos']); ?></span></div>
                </div>
              </div>
              <div class="ex-check" id="check-<?php echo $i; ?>">✓</div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Barre de complétion exercices -->
        <div style="margin-bottom:1.5rem;">
          <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:var(--gris-clair); margin-bottom:0.4rem;">
            <span>Exercices complétés</span>
            <span id="exCount">0</span>/<span><?php echo count($workout_actif['exercices']); ?></span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" id="exProgress" style="width:0%; background:linear-gradient(90deg,var(--jaune),var(--vert));"></div>
          </div>
        </div>

      <?php else: ?>
        <!-- Fallback : affichage du texte brut pour ce jour -->
        <div class="raw-content">
          <p style="color:var(--gris-clair); font-size:0.85rem; margin-bottom:1rem;">
            ℹ️ Les exercices de ce jour sont affichés depuis le programme complet :
          </p>
          <?php
            // Extraire juste le bloc de ce jour depuis le contenu brut
            $raw_escaped = htmlspecialchars($programme['contenu'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            echo '<pre style="white-space:pre-wrap; font-family:\'Syne\',sans-serif; font-size:0.85rem; color:#c0c0c0;">' . $raw_escaped . '</pre>';
          ?>
        </div>
      <?php endif; ?>

      <!-- Récupération -->
      <?php if ($workout_actif['recup']): ?>
        <div class="recup-box">
          🧘 <strong>Récupération :</strong> <?php echo h($workout_actif['recup']); ?>
        </div>
      <?php endif; ?>

      <!-- Form validation -->
      <?php if (!isset($completed_jours[$jour_actif])): ?>
        <div class="log-form">
          <h3>✅ Valider la séance</h3>
          <form method="POST" action="workout_session.php?prog_id=<?php echo $ia_prog_id; ?>&jour=<?php echo $jour_actif; ?>">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="log_ia_workout">
            <input type="hidden" name="jour_num" value="<?php echo $jour_actif; ?>">
            <textarea name="notes" class="log-textarea"
              placeholder="Notes (facultatif) — ex: 'Pull-ups à 8 reps, très bon feeling', 'Récup douce demain'..."></textarea>
            <button type="submit" class="btn-log" id="logBtn">
              💪 Enregistrer Jour <?php echo $jour_actif; ?>
            </button>
          </form>
        </div>
      <?php else: ?>
        <div style="text-align:center; padding:1rem;">
          <a href="?prog_id=<?php echo $ia_prog_id; ?>&jour=<?php echo min($jour_actif + 1, $total_jours); ?>"
             class="btn-log" style="display:inline-flex; max-width:300px; text-decoration:none; justify-content:center;">
            ➡️ Jour suivant
          </a>
        </div>
      <?php endif; ?>

    </div>

  <?php endif; ?>

  <!-- Actions bas -->
  <div class="ws-actions">
    <a href="coach_ia.php" class="btn-secondary">✨ Nouveau programme IA</a>
    <a href="coach_historique.php" class="btn-secondary">📋 Historique</a>
    <a href="profile.php" class="btn-secondary">👤 Mon profil</a>
  </div>

</div>

<script src="assets/js/script.js"></script>
<script>
  const totalEx = <?php echo count($workout_actif['exercices'] ?? []); ?>;
  let doneSet = new Set();

  function toggleEx(i) {
    const item  = document.getElementById('ex-' + i);
    const check = document.getElementById('check-' + i);
    if (!item) return;

    item.classList.toggle('done');
    if (item.classList.contains('done')) {
      doneSet.add(i);
    } else {
      doneSet.delete(i);
    }
    updateProgress();
  }

  function updateProgress() {
    const nb = doneSet.size;
    const pct = totalEx > 0 ? Math.round(nb / totalEx * 100) : 0;
    const countEl = document.getElementById('exCount');
    const barEl   = document.getElementById('exProgress');
    const btn     = document.getElementById('logBtn');

    if (countEl) countEl.textContent = nb;
    if (barEl)   barEl.style.width = pct + '%';

    // Activer le bouton uniquement si tous les exos sont cochés
    if (btn && totalEx > 0) {
      btn.disabled = nb < totalEx;
      btn.textContent = nb < totalEx
        ? `💪 Complète ${totalEx - nb} exercice(s) restant(s)`
        : `✅ Enregistrer la séance — Jour <?php echo $jour_actif; ?>`;
      if (!btn.disabled) {
        btn.style.background = 'var(--jaune)';
      } else {
        btn.style.background = '';
      }
    }
  }

  // Init
  updateProgress();
</script>
</body>
</html>
