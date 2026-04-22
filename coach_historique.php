<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/check_session.php';
require_once __DIR__ . '/includes/auth.php';

$user = $_SESSION['user_data'];
$user_id = $_SESSION['user_id'];

// Suppression d'un programme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    require_valid_csrf();
    $id = (int)($_POST['programme_id'] ?? 0);
    if ($id > 0) {
        $mysqli = connecter_db();
        $stmt = $mysqli->prepare("DELETE FROM ia_programmes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
        set_flash_message('Programme supprimé.', 'success');
        header('Location: coach_historique.php');
        exit;
    }
}

// Récupérer les programmes de l'utilisateur
$mysqli = connecter_db();
$stmt = $mysqli->prepare(
    "SELECT id, niveau, objectif, parties_corps, jours_semaine, contenu, created_at
     FROM ia_programmes WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$programmes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$mysqli->close();

$flash = get_flash_message();

// Convertir markdown en HTML (même fonction que coach_ia.php)
function parse_md_to_html_hist($text) {
    $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\_(.*?)\_/', '<em>$1</em>', $text);
    $text = preg_replace_callback('/(\|.+\|)\n(\|[-| :]+\|)\n((?:\|.+\|\n?)+)/', function($m) {
        $headers = array_map('trim', explode('|', trim($m[1], '|')));
        $rows_raw = explode("\n", trim($m[3]));
        $thead = '<thead><tr>' . implode('', array_map(fn($h) => "<th>{$h}</th>", $headers)) . '</tr></thead>';
        $tbody = '<tbody>';
        foreach ($rows_raw as $row) {
            if (trim($row) === '') continue;
            $cells = array_map('trim', explode('|', trim($row, '|')));
            $tbody .= '<tr>' . implode('', array_map(fn($c) => "<td>{$c}</td>", $cells)) . '</tr>';
        }
        $tbody .= '</tbody>';
        return '<div class="table-wrap"><table class="ai-table">' . $thead . $tbody . '</table></div>';
    }, $text);
    $text = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    $text = nl2br($text);
    return $text;
}

$objectifs_icons = [
    'Force générale'     => '💪',
    'Endurance'          => '🏃',
    'Prise de muscle'    => '🦾',
    'Perte de poids'     => '🔥',
    'Souplesse & Skill'  => '🤸',
    'Full Body équilibré'=> '⚖️',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historique Coach IA — <?php echo SITE_NAME; ?></title>
<meta name="description" content="Retrouve tous tes programmes calisthenics générés par l'IA.">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .hist-container { min-height: 100vh; background: var(--noir); padding: 2rem; }

  .hist-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 2.5rem; padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,215,0,0.1);
  }
  .hist-title h1 {
    font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem;
    color: var(--blanc); margin: 0;
  }
  .hist-title p { color: var(--gris-clair); margin: 0.3rem 0 0 0; }
  .hist-nav a {
    color: var(--gris-clair); text-decoration: none; padding: 0.5rem 1rem;
    border: 1px solid rgba(255,215,0,0.2); margin-left: 0.5rem; font-size: 0.85rem;
    transition: all 0.2s;
  }
  .hist-nav a:hover { color: var(--jaune); border-color: var(--jaune); }

  .hist-stats {
    max-width: 1100px; margin: 0 auto 2rem;
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;
  }
  .stat-card {
    background: var(--noir3); border: 1px solid rgba(255,215,0,0.08);
    padding: 1.2rem 1.5rem;
    clip-path: polygon(12px 0%, 100% 0%, calc(100% - 12px) 100%, 0% 100%);
    text-align: center;
  }
  .stat-num { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; color: var(--jaune); }
  .stat-label { color: var(--gris-clair); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }

  .programmes-grid {
    max-width: 1100px; margin: 0 auto;
    display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem;
  }

  .prog-card {
    background: var(--noir3); border: 1px solid rgba(255,215,0,0.08);
    clip-path: polygon(14px 0%, 100% 0%, calc(100% - 14px) 100%, 0% 100%);
    overflow: hidden; transition: all 0.3s;
    display: flex; flex-direction: column;
  }
  .prog-card:hover { border-color: rgba(255,215,0,0.25); transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }

  .prog-card-header {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    display: flex; justify-content: space-between; align-items: flex-start;
  }
  .prog-objectif-icon { font-size: 2rem; }
  .prog-date {
    font-family: 'Space Mono', monospace; font-size: 0.72rem;
    color: var(--gris-clair); text-align: right;
  }

  .prog-card-body { padding: 1.2rem 1.5rem; flex: 1; }
  .prog-objectif {
    font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem;
    color: var(--jaune); margin-bottom: 0.5rem; letter-spacing: 1px;
  }
  .prog-tags { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 1rem; }
  .prog-tag {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
    color: var(--gris-clair); padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.72rem;
  }
  .prog-tag.highlight { background: rgba(255,215,0,0.08); border-color: rgba(255,215,0,0.2); color: var(--jaune); }

  .prog-preview {
    color: #a0a0a0; font-size: 0.85rem; line-height: 1.6;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
  }

  .prog-card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.05);
    display: flex; gap: 0.8rem;
  }
  .btn-voir {
    flex: 1; padding: 0.6rem; background: rgba(255,215,0,0.1);
    color: var(--jaune); border: 1px solid rgba(255,215,0,0.3);
    font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.8rem;
    letter-spacing: 0.5px; cursor: pointer; text-transform: uppercase; transition: all 0.2s;
  }
  .btn-voir:hover { background: var(--jaune); color: var(--noir); }
  .btn-print {
    padding: 0.6rem 0.8rem; background: rgba(255,255,255,0.04);
    color: var(--gris-clair); border: 1px solid rgba(255,255,255,0.08);
    cursor: pointer; font-size: 1rem; transition: all 0.2s;
  }
  .btn-print:hover { color: var(--blanc); border-color: rgba(255,255,255,0.3); }
  .btn-delete {
    padding: 0.6rem 0.8rem; background: rgba(255,69,0,0.08);
    color: #ff6b6b; border: 1px solid rgba(255,69,0,0.2);
    cursor: pointer; font-size: 1rem; transition: all 0.2s;
  }
  .btn-delete:hover { background: rgba(255,69,0,0.2); }

  /* Modal */
  .modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.85); z-index: 9000;
    align-items: center; justify-content: center;
    padding: 2rem;
  }
  .modal-overlay.active { display: flex; animation: fadeIn 0.2s ease; }
  .modal-box {
    background: #121212; border: 1px solid rgba(255,215,0,0.2);
    max-width: 800px; width: 100%; max-height: 85vh;
    display: flex; flex-direction: column;
    clip-path: polygon(20px 0%, 100% 0%, calc(100% - 20px) 100%, 0% 100%);
  }
  .modal-header {
    padding: 1.5rem 2rem; border-bottom: 1px solid rgba(255,215,0,0.1);
    display: flex; justify-content: space-between; align-items: center;
    flex-shrink: 0;
  }
  .modal-header h2 { font-family: 'Bebas Neue', sans-serif; font-size: 1.8rem; color: var(--jaune); margin: 0; }
  .modal-close {
    background: none; border: none; color: var(--gris-clair); font-size: 1.8rem;
    cursor: pointer; line-height: 1; padding: 0;
  }
  .modal-close:hover { color: var(--blanc); }
  .modal-body { padding: 2rem; overflow-y: auto; flex: 1; }

  .modal-body .ai-response h1, .modal-body .ai-response h2 {
    color: var(--jaune); font-family: 'Bebas Neue', sans-serif; font-size: 1.5rem;
    margin-top: 1.5rem; margin-bottom: 0.8rem;
  }
  .modal-body .ai-response h3 { color: var(--vert); font-size: 1rem; margin-top: 1.2rem; }
  .modal-body .ai-response ul { padding-left: 1.5rem; }
  .modal-body .ai-response li { margin-bottom: 0.5rem; color: #e0e0e0; }
  .modal-body .ai-response strong { color: var(--jaune); }
  .table-wrap { overflow-x: auto; margin: 1rem 0; }
  .ai-table { width: 100%; border-collapse: collapse; font-family: 'Space Mono', monospace; font-size: 0.85rem; }
  .ai-table th { background: rgba(255,215,0,0.1); color: var(--jaune); padding: 0.5rem 0.8rem; text-align: left; border-bottom: 2px solid rgba(255,215,0,0.2); font-size: 0.75rem; text-transform: uppercase; }
  .ai-table td { padding: 0.5rem 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); color: #e0e0e0; }
  .ai-table tr:hover td { background: rgba(255,215,0,0.03); }

  .modal-footer {
    padding: 1rem 2rem; border-top: 1px solid rgba(255,255,255,0.05);
    display: flex; gap: 1rem; flex-shrink: 0;
  }
  .btn-modal-action {
    padding: 0.7rem 1.5rem; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.85rem;
    cursor: pointer; border: none; text-transform: uppercase; letter-spacing: 0.5px;
    clip-path: polygon(6px 0%, 100% 0%, calc(100% - 6px) 100%, 0% 100%);
    transition: all 0.2s;
  }
  .btn-modal-action.print { background: var(--jaune); color: var(--noir); }
  .btn-modal-action.print:hover { background: var(--vert); }
  .btn-modal-action.close { background: rgba(255,255,255,0.08); color: var(--blanc); }
  .btn-modal-action.close:hover { background: rgba(255,255,255,0.15); }

  /* Empty */
  .empty-hist {
    max-width: 1100px; margin: 4rem auto; text-align: center;
    color: var(--gris-clair);
  }
  .empty-hist .icon { font-size: 5rem; opacity: 0.3; margin-bottom: 1rem; display: block; }

  /* Flash */
  .flash-success {
    max-width: 1100px; margin: 0 auto 1.5rem;
    background: rgba(0,168,79,0.1); border-left: 4px solid var(--vert);
    color: #51cf66; padding: 0.8rem 1.2rem; font-size: 0.9rem;
  }

  /* Print hidden content */
  #printArea { display: none; }

  @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

  @media (max-width: 768px) {
    .hist-stats { grid-template-columns: 1fr; }
    .programmes-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<div class="hist-container">
  <div class="hist-header">
    <div class="hist-title">
      <h1>📋 Historique IA</h1>
      <p>Tous tes programmes générés par le Coach IA</p>
    </div>
    <div class="hist-nav">
      <a href="coach_ia.php" class="active">✨ Nouveau programme</a>
      <a href="index.php">← Retour</a>
    </div>
  </div>

  <?php if ($flash && $flash['type'] === 'success'): ?>
    <div class="flash-success">✅ <?php echo h($flash['text']); ?></div>
  <?php endif; ?>

  <?php if (empty($programmes)): ?>
    <div class="empty-hist">
      <span class="icon">🤖</span>
      <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--blanc);">Aucun programme généré</h2>
      <p>Tu n'as pas encore utilisé le Coach IA. Lance ta première génération !</p>
      <a href="coach_ia.php" style="display:inline-block; margin-top:1.5rem; padding:0.8rem 2rem; background:var(--jaune); color:var(--noir); font-family:'Syne',sans-serif; font-weight:800; text-decoration:none; text-transform:uppercase; letter-spacing:1px;">
        ⚡ Générer mon premier programme
      </a>
    </div>
  <?php else: ?>

    <!-- Stats -->
    <?php
      $objectifs_uniques = array_unique(array_column($programmes, 'objectif'));
      $total_jours = array_sum(array_column($programmes, 'jours_semaine'));
    ?>
    <div class="hist-stats">
      <div class="stat-card">
        <div class="stat-num"><?php echo count($programmes); ?></div>
        <div class="stat-label">Programmes générés</div>
      </div>
      <div class="stat-card">
        <div class="stat-num"><?php echo count($objectifs_uniques); ?></div>
        <div class="stat-label">Objectifs explorés</div>
      </div>
      <div class="stat-card">
        <div class="stat-num"><?php echo $total_jours; ?></div>
        <div class="stat-label">Jours d'entraînement planifiés</div>
      </div>
    </div>

    <!-- Grid programmes -->
    <div class="programmes-grid">
      <?php foreach ($programmes as $prog): ?>
        <?php
          $icon = $objectifs_icons[$prog['objectif']] ?? '🏋️';
          $date = new DateTime($prog['created_at']);
          $preview = strip_tags(preg_replace('/\*\*(.*?)\*\*/', '$1', $prog['contenu']));
          $preview = preg_replace('/#+\s/', '', $preview);
          $safe_id = (int)$prog['id'];
        ?>
        <div class="prog-card">
          <div class="prog-card-header">
            <div class="prog-objectif-icon"><?php echo $icon; ?></div>
            <div class="prog-date">
              📅 <?php echo $date->format('d/m/Y'); ?><br>
              🕐 <?php echo $date->format('H:i'); ?>
            </div>
          </div>
          <div class="prog-card-body">
            <div class="prog-objectif"><?php echo h($prog['objectif']); ?></div>
            <div class="prog-tags">
              <span class="prog-tag highlight">🏅 <?php echo h($prog['niveau']); ?></span>
              <span class="prog-tag highlight">📅 <?php echo $prog['jours_semaine']; ?> j/sem</span>
              <?php foreach (explode(', ', $prog['parties_corps']) as $muscle): ?>
                <span class="prog-tag"><?php echo h(trim($muscle)); ?></span>
              <?php endforeach; ?>
            </div>
            <div class="prog-preview"><?php echo h(substr($preview, 0, 200)) . '...'; ?></div>
          </div>
          <div class="prog-card-footer">
            <button class="btn-voir" onclick="openModal(<?php echo $safe_id; ?>)">
              👁 Voir
            </button>
            <button class="btn-print" onclick="printProg(<?php echo $safe_id; ?>)" title="Imprimer">🖨️</button>

            <!-- Suppression -->
            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce programme ?');">
              <?php echo csrf_input(); ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="programme_id" value="<?php echo $safe_id; ?>">
              <button type="submit" class="btn-delete" title="Supprimer">🗑️</button>
            </form>
          </div>
        </div>

        <!-- Données pour la modal (JSON encodé) -->
        <script>
          window.programmes = window.programmes || {};
          window.programmes[<?php echo $safe_id; ?>] = {
            objectif: <?php echo json_encode($prog['objectif']); ?>,
            niveau: <?php echo json_encode($prog['niveau']); ?>,
            parties: <?php echo json_encode($prog['parties_corps']); ?>,
            jours: <?php echo (int)$prog['jours_semaine']; ?>,
            date: <?php echo json_encode($date->format('d/m/Y à H:i')); ?>,
            contenu: <?php echo json_encode(parse_md_to_html_hist(htmlspecialchars($prog['contenu'], ENT_QUOTES | ENT_HTML5, 'UTF-8'))); ?>
          };
        </script>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModalOnBg(event)">
  <div class="modal-box">
    <div class="modal-header">
      <h2 id="modalTitle">Programme</h2>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div id="modalBadges" style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-bottom:1.5rem;"></div>
      <div class="ai-response" id="modalContent"></div>
    </div>
    <div class="modal-footer">
      <button class="btn-modal-action print" onclick="printCurrent()">📄 Imprimer / PDF</button>
      <button class="btn-modal-action close" onclick="closeModal()">Fermer</button>
    </div>
  </div>
</div>

<!-- Zone impression -->
<div id="printArea">
  <style media="print">
    body > *:not(#printArea) { display: none !important; }
    #printArea { display: block !important; font-family: Arial, sans-serif; color: #000; padding: 2rem; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 0.5rem; }
    th { background: #f0f0f0; }
  </style>
  <div id="printContent"></div>
</div>

<script src="assets/js/script.js"></script>
<script>
  let currentModalId = null;

  function openModal(id) {
    const prog = window.programmes[id];
    if (!prog) return;
    currentModalId = id;
    document.getElementById('modalTitle').textContent = prog.objectif;
    document.getElementById('modalContent').innerHTML = prog.contenu;
    document.getElementById('modalBadges').innerHTML = `
      <span style="background:rgba(255,215,0,0.1);color:#FFD700;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:bold;border:1px solid rgba(255,215,0,0.2);">🏅 ${prog.niveau}</span>
      <span style="background:rgba(255,215,0,0.1);color:#FFD700;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:bold;border:1px solid rgba(255,215,0,0.2);">📅 ${prog.jours} j/sem</span>
      <span style="background:rgba(255,255,255,0.05);color:#aaa;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;">🕐 ${prog.date}</span>
    `;
    document.getElementById('modalOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    document.getElementById('modalOverlay').classList.remove('active');
    document.body.style.overflow = '';
    currentModalId = null;
  }

  function closeModalOnBg(e) {
    if (e.target === document.getElementById('modalOverlay')) closeModal();
  }

  function printCurrent() {
    if (!currentModalId) return;
    printProg(currentModalId);
  }

  function printProg(id) {
    const prog = window.programmes[id];
    if (!prog) return;
    document.getElementById('printContent').innerHTML = `
      <h1>🏋️ Programme Calisthenics — RAKH Pulse</h1>
      <p><strong>Objectif :</strong> ${prog.objectif} | <strong>Niveau :</strong> ${prog.niveau} | <strong>Fréquence :</strong> ${prog.jours} j/sem</p>
      <p style="color:#888; font-size:0.85rem;">Généré le ${prog.date}</p>
      <hr>
      ${prog.contenu}
      <p style="margin-top:3rem; font-size:0.75rem; color:#888; text-align:center;">
        RAKH Pulse Coach IA — calisthénique sans matériel
      </p>
    `;
    document.getElementById('printArea').style.display = 'block';
    window.print();
    setTimeout(() => { document.getElementById('printArea').style.display = 'none'; }, 1000);
  }

  // Keyboard close
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
  });
</script>
</body>
</html>
