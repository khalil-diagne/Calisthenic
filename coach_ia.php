<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/check_session.php';
require_once __DIR__ . '/includes/auth.php';

$user = $_SESSION['user_data'];
$user_id = $_SESSION['user_id'];
$niveau = $user['niveau'];

$ai_response = '';
$error = '';
$saved_id = null;
$selected_body_parts = [];
$selected_objectif = '';
$selected_jours = 3;
$adaptation_message = '';

// --- F: IA Adaptative — analyse la progression de l'utilisateur ---
function get_progression_context($user_id, $niveau) {
    $mysqli = connecter_db();
    $stmt = $mysqli->prepare("SELECT COUNT(*) as nb FROM workout_logs WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $nb_seances = (int)$result['nb'];
    $stmt->close();
    $mysqli->close();

    $niveaux_ordre = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert', 'Élite'];
    $idx = array_search($niveau, $niveaux_ordre);
    $niveau_adapte = $niveau;
    $adaptation_msg = '';

    if ($nb_seances >= 20 && $idx !== false && $idx < count($niveaux_ordre) - 1) {
        $niveau_adapte = $niveaux_ordre[$idx + 1];
        $adaptation_msg = "📈 +{$nb_seances} séances détectées — programme ajusté au niveau {$niveau_adapte} !";
    } elseif ($nb_seances >= 8) {
        $adaptation_msg = "🔥 {$nb_seances} séances complétées — intensité renforcée !";
    }

    return ['niveau_adapte' => $niveau_adapte, 'nb_seances' => $nb_seances, 'message' => $adaptation_msg];
}

$progression = get_progression_context($user_id, $niveau);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body_parts'])) {
    require_valid_csrf();

    $selected_body_parts = array_values(array_filter($_POST['body_parts'] ?? [], 'is_string'));
    $selected_objectif   = trim($_POST['objectif'] ?? 'Force générale');
    $selected_jours      = max(1, min(7, (int)($_POST['jours_semaine'] ?? 3)));

    if (empty($selected_body_parts)) {
        $error = 'Veuillez sélectionner au moins une partie du corps à travailler.';
    } else {
        $api_key = trim(GEMINI_API_KEY);
        if ($api_key === '') {
            $error = 'Clé API Gemini manquante. Ajoutez GEMINI_API_KEY dans le fichier .env.';
        } else {
            $body_parts     = implode(', ', $selected_body_parts);
            $niveau_prompt  = $progression['niveau_adapte'];

            // --- B: Prompt structuré et précis ---
            $prompt = "Tu es un coach expert en Calisthenics (entraînement au poids du corps) pour RAKH Pulse, Sénégal.

PROFIL ATHLÈTE :
- Niveau : {$niveau_prompt}
- Objectif principal : {$selected_objectif}
- Muscles ciblés : {$body_parts}
- Fréquence : {$selected_jours} jours par semaine

RÈGLES STRICTES :
1. Réponds UNIQUEMENT en français avec des emojis motivants.
2. Structure le programme EXACTEMENT ainsi pour chaque jour :

## 🗓️ Jour X — [Nom du jour]
**Focus :** [muscle principal]
| Exercice | Séries | Reps/Durée | Repos |
|---|---|---|---|
| [exercice] | [X] | [Y] | [Zs] |

**🧘 Récupération :** [conseil récup du jour]

3. Termine par une section :
## 💡 Conseils du Coach
- [2-3 conseils adaptés au niveau et à l'objectif]

4. Adapte STRICTEMENT la difficulté au niveau {$niveau_prompt}.
5. Uniquement des exercices sans matériel (au poids du corps).
6. Sois précis : nombre exact de séries, reps, temps de repos.";

            $url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . rawurlencode($api_key);
            // Tokens dynamiques selon le nb de jours (≈400 tokens/jour minimum)
            $max_tokens = max(2000, $selected_jours * 500 + 600);

            $data = [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature'     => 0.7,
                    'maxOutputTokens' => $max_tokens
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = "Erreur de connexion : " . curl_error($ch);
            } else {
                $result = json_decode($response, true);
                if ($httpcode == 200 && isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $ai_response = $result['candidates'][0]['content']['parts'][0]['text'];

                    // --- C: Sauvegarder le programme en BDD ---
                    $mysqli = connecter_db();
                    $stmt = $mysqli->prepare(
                        "INSERT INTO ia_programmes (user_id, niveau, objectif, parties_corps, jours_semaine, contenu)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param(
                        "isssis",
                        $user_id,
                        $niveau_prompt,
                        $selected_objectif,
                        $body_parts,
                        $selected_jours,
                        $ai_response
                    );
                    $stmt->execute();
                    $saved_id = $mysqli->insert_id;
                    $stmt->close();
                    $mysqli->close();

                    $adaptation_message = $progression['message'];
                } else {
                    $error = "Erreur de l'API IA : " . ($result['error']['message'] ?? 'Erreur inconnue');
                }
            }
            curl_close($ch);
        }
    }
}

// Convertir markdown en HTML
function parse_md_to_html($text) {
    // Titres
    $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);
    // Gras
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    // Italique
    $text = preg_replace('/\_(.*?)\_/', '<em>$1</em>', $text);
    // Tableau Markdown
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
    // Listes
    $text = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    // Sauts de ligne
    $text = nl2br($text);
    return $text;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Coach IA — <?php echo SITE_NAME; ?></title>
<meta name="description" content="Génère ton programme calisthenics personnalisé grâce à l'IA de RAKH Pulse.">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .coach-container {
    min-height: 100vh;
    background: var(--noir);
    padding: 2rem;
  }
  .coach-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,215,0,0.1);
  }
  .coach-title h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    color: var(--blanc);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .coach-title p { color: var(--gris-clair); margin: 0.5rem 0 0 0; }
  .coach-nav a {
    color: var(--gris-clair);
    text-decoration: none;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
    border: 1px solid rgba(255,215,0,0.2);
    margin-left: 0.5rem;
    font-size: 0.85rem;
  }
  .coach-nav a:hover, .coach-nav a.active { color: var(--jaune); border-color: var(--jaune); }

  .coach-content {
    max-width: 1100px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 2rem;
  }

  .coach-form-card {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 2rem;
    clip-path: polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%);
    height: fit-content;
    position: sticky;
    top: 5rem;
  }

  .card-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.5rem;
    color: var(--blanc);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,215,0,0.1);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .form-group { margin-bottom: 1.5rem; }
  .form-group label {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--jaune);
    margin-bottom: 0.8rem;
  }

  /* Niveau badge */
  .niveau-badge {
    background: rgba(255,215,0,0.08);
    border: 1px solid rgba(255,215,0,0.3);
    color: var(--jaune);
    padding: 0.6rem 1rem;
    font-weight: bold;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  /* Objectif select */
  .form-select {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: var(--blanc);
    padding: 0.8rem 1rem;
    font-family: 'Syne', sans-serif;
    font-size: 0.9rem;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23FFD700' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
  }
  .form-select:focus { outline: none; border-color: var(--jaune); }
  .form-select option { background: #1a1a1a; }

  /* Jours slider */
  .jours-display {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
  }
  .jours-count {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    color: var(--jaune);
    line-height: 1;
    min-width: 2rem;
    text-align: center;
  }
  .jours-label { color: var(--gris-clair); font-size: 0.85rem; }
  input[type="range"] {
    width: 100%;
    accent-color: var(--jaune);
    cursor: pointer;
  }
  .jours-scale {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
    color: var(--gris-clair);
    margin-top: 0.3rem;
    font-family: 'Space Mono', monospace;
  }

  /* Checkboxes */
  .checkbox-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
  }
  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--blanc);
    font-size: 0.85rem;
    cursor: pointer;
    background: rgba(255,255,255,0.04);
    padding: 0.7rem;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 4px;
    transition: all 0.2s;
    user-select: none;
  }
  .checkbox-label:hover { background: rgba(255,215,0,0.08); border-color: rgba(255,215,0,0.3); }
  .checkbox-label input { display: none; }
  .checkbox-label .cb-icon {
    width: 16px; height: 16px; min-width: 16px;
    border: 2px solid rgba(255,215,0,0.4);
    border-radius: 3px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem;
    color: transparent;
    transition: all 0.15s;
  }
  .checkbox-label input:checked ~ .cb-icon { background: var(--jaune); border-color: var(--jaune); color: var(--noir); }
  .checkbox-label input:checked ~ span { color: var(--jaune); font-weight: bold; }

  /* Submit */
  .btn-submit {
    width: 100%;
    padding: 1rem 2rem;
    background: var(--jaune);
    color: var(--noir);
    border: none;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    cursor: pointer;
    clip-path: polygon(8px 0%, 100% 0%, calc(100% - 8px) 100%, 0% 100%);
    transition: background 0.2s, transform 0.1s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
  }
  .btn-submit:hover { background: var(--vert); transform: translateY(-2px); }
  .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

  /* Result card */
  .coach-result-card {
    background: linear-gradient(145deg, var(--noir3), #1a1a1a);
    border: 1px solid rgba(255,215,0,0.15);
    padding: 2.5rem;
    clip-path: polygon(0 0, 100% 0%, 100% calc(100% - 24px), calc(100% - 24px) 100%, 0 100%);
    box-shadow: 0 15px 35px rgba(0,0,0,0.4);
    position: relative;
    overflow: hidden;
    min-height: 400px;
  }
  .result-watermark {
    position: absolute; top: 50%; right: -10%;
    transform: translateY(-50%);
    font-size: 15rem; opacity: 0.02; pointer-events: none;
  }

  /* Adaptation banner */
  .adaptation-banner {
    background: linear-gradient(90deg, rgba(0,168,79,0.12), rgba(0,168,79,0.05));
    border-left: 3px solid var(--vert);
    padding: 0.8rem 1.2rem;
    color: #51cf66;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: slideIn 0.4s ease-out;
  }

  /* Profile badge */
  .profile-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
  }
  .badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255,215,0,0.08);
    color: var(--jaune);
    padding: 0.35rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: bold;
    border: 1px solid rgba(255,215,0,0.2);
  }
  .badge.green {
    background: rgba(0,168,79,0.1);
    color: #51cf66;
    border-color: rgba(0,168,79,0.3);
  }

  /* AI Response */
  .ai-response { color: var(--blanc); line-height: 1.8; font-size: 1rem; animation: fadeIn 0.5s ease-out; }
  .ai-response h1, .ai-response h2 {
    color: var(--jaune);
    font-family: 'Bebas Neue', sans-serif;
    margin-top: 2rem; margin-bottom: 1rem;
    letter-spacing: 1px; font-size: 1.6rem;
  }
  .ai-response h3 {
    color: var(--vert);
    font-family: 'Syne', sans-serif;
    font-size: 1rem; margin-top: 1.5rem; margin-bottom: 0.8rem;
  }
  .ai-response ul { padding-left: 1.5rem; margin-bottom: 1.5rem; }
  .ai-response li { margin-bottom: 0.6rem; color: #e0e0e0; }
  .ai-response strong { color: var(--jaune); }

  /* AI Table */
  .table-wrap { overflow-x: auto; margin: 1rem 0; }
  .ai-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    font-family: 'Space Mono', monospace;
  }
  .ai-table th {
    background: rgba(255,215,0,0.1);
    color: var(--jaune);
    padding: 0.6rem 1rem;
    text-align: left;
    font-size: 0.8rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    border-bottom: 2px solid rgba(255,215,0,0.2);
  }
  .ai-table td {
    padding: 0.6rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    color: #e0e0e0;
  }
  .ai-table tr:hover td { background: rgba(255,215,0,0.03); }

  /* Action buttons après génération */
  .result-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255,215,0,0.1);
  }
  .btn-action {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.4rem;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    clip-path: polygon(6px 0%, 100% 0%, calc(100% - 6px) 100%, 0% 100%);
  }
  .btn-action.print {
    background: rgba(255,215,0,0.1);
    color: var(--jaune);
    border: 1px solid rgba(255,215,0,0.3);
  }
  .btn-action.print:hover { background: var(--jaune); color: var(--noir); }
  .btn-action.history {
    background: rgba(255,255,255,0.05);
    color: var(--gris-clair);
    border: 1px solid rgba(255,255,255,0.1);
  }
  .btn-action.history:hover { color: var(--blanc); border-color: rgba(255,255,255,0.3); }

  /* Loading */
  .loader-wrap {
    display: none;
    text-align: center;
    padding: 5rem 0;
  }
  .loader {
    width: 50px; height: 50px;
    border: 4px solid rgba(255,215,0,0.1);
    border-left-color: var(--jaune);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 1.5rem;
  }
  .loader-text {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.5rem;
    color: var(--jaune);
    letter-spacing: 2px;
    margin-bottom: 0.5rem;
  }
  .loader-dots::after {
    content: '';
    animation: dots 1.5s steps(4, end) infinite;
  }
  @keyframes dots {
    0%,20%  { content: ''; }
    40%     { content: '.'; }
    60%     { content: '..'; }
    80%,100%{ content: '...'; }
  }

  /* Empty state */
  .empty-state {
    text-align: center; color: var(--gris-clair);
    padding: 4rem 2rem;
    display: flex; flex-direction: column; align-items: center; gap: 1rem;
  }
  .empty-icon { font-size: 4rem; opacity: 0.4; animation: float 3s ease-in-out infinite; }
  @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

  /* Error */
  .error-box {
    background: rgba(255,69,0,0.08);
    color: #ff6b6b;
    padding: 1rem 1.2rem;
    border-left: 4px solid #ff6b6b;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
  }

  /* Animations */
  @keyframes spin     { 0%   { transform: rotate(0deg); }  100% { transform: rotate(360deg); } }
  @keyframes fadeIn   { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
  @keyframes slideIn  { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }

  /* Responsive */
  @media (max-width: 900px) {
    .coach-content { grid-template-columns: 1fr; }
    .coach-form-card { position: static; }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<div class="coach-container">
  <div class="coach-header">
    <div class="coach-title">
      <h1><span style="color:var(--jaune)">✨</span> COACH IA</h1>
      <p>Ton programme calisthenics sur-mesure, généré par intelligence artificielle</p>
    </div>
    <div class="coach-nav">
      <a href="coach_historique.php">📋 Historique</a>
      <a href="profile.php">Mon Profil</a>
      <a href="index.php">← Retour</a>
    </div>
  </div>

  <div class="coach-content">

    <!-- Sidebar Form -->
    <div class="coach-form-card">
      <div class="card-title">⚙️ Configuration</div>

      <form method="POST" action="coach_ia.php" id="coachForm">
        <?php echo csrf_input(); ?>

        <!-- Niveau -->
        <div class="form-group">
          <label>🎯 Niveau détecté</label>
          <div class="niveau-badge">
            🏅 <?php echo h($niveau); ?>
            <?php if ($progression['nb_seances'] > 0): ?>
              <span style="margin-left:auto; font-size:0.75rem; color:var(--gris-clair);">
                <?php echo $progression['nb_seances']; ?> séances
              </span>
            <?php endif; ?>
          </div>
          <?php if ($progression['message']): ?>
            <small style="color:#51cf66; display:block; margin-top:0.5rem; font-size:0.75rem;">
              <?php echo h($progression['message']); ?>
            </small>
          <?php endif; ?>
          <small style="color:var(--gris-clair); display:block; margin-top:0.3rem; font-size:0.72rem;">
            Modifiable dans <a href="profile.php" style="color:var(--jaune);">ton profil</a>.
          </small>
        </div>

        <!-- Objectif -->
        <div class="form-group">
          <label>🏆 Objectif principal</label>
          <select name="objectif" id="objectif" class="form-select">
            <?php
            $objectifs = [
              'Force générale'     => '💪 Force générale',
              'Endurance'          => '🏃 Endurance',
              'Prise de muscle'    => '🦾 Prise de muscle',
              'Perte de poids'     => '🔥 Perte de poids',
              'Souplesse & Skill'  => '🤸 Souplesse & Skill',
              'Full Body équilibré'=> '⚖️ Full Body équilibré',
            ];
            foreach ($objectifs as $val => $label):
              $sel = ($selected_objectif === $val || ($selected_objectif === '' && $val === 'Force générale')) ? 'selected' : '';
            ?>
              <option value="<?php echo h($val); ?>" <?php echo $sel; ?>><?php echo h($label); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Jours par semaine -->
        <div class="form-group">
          <label>📅 Jours par semaine</label>
          <div class="jours-display">
            <div class="jours-count" id="joursDisplay"><?php echo $selected_jours; ?></div>
            <div class="jours-label">jours<br>d'entraînement</div>
          </div>
          <input type="range" name="jours_semaine" id="joursRange"
                 min="1" max="7" value="<?php echo $selected_jours; ?>"
                 oninput="document.getElementById('joursDisplay').textContent = this.value">
          <div class="jours-scale">
            <span>1j</span><span>2j</span><span>3j</span><span>4j</span><span>5j</span><span>6j</span><span>7j</span>
          </div>
        </div>

        <!-- Parties du corps -->
        <div class="form-group">
          <label>💪 Muscles à cibler</label>
          <div class="checkbox-grid">
            <?php
            $muscles = [
              'Pectoraux'            => '🫁 Pectoraux',
              'Dos'                  => '🔙 Dos',
              'Bras (Biceps/Triceps)'=> '💪 Bras',
              'Épaules'              => '🏹 Épaules',
              'Jambes'               => '🦵 Jambes',
              'Abdominaux (Core)'    => '🎯 Abdominaux',
              'Full Body (Tout le corps)' => '🌐 Full Body',
              'Fessiers'             => '🍑 Fessiers',
            ];
            foreach ($muscles as $val => $label):
              $checked = in_array($val, $selected_body_parts, true) ? 'checked' : '';
            ?>
              <label class="checkbox-label">
                <input type="checkbox" name="body_parts[]" value="<?php echo h($val); ?>" <?php echo $checked; ?>>
                <span class="cb-icon">✓</span>
                <span><?php echo h($label); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <span>⚡</span> Générer mon programme
        </button>
      </form>
    </div>

    <!-- Results Board -->
    <div class="coach-result-card" id="resultCard">
      <div class="result-watermark">🦾</div>

      <?php if ($error): ?>
        <div class="error-box">⚠️ <?php echo h($error); ?></div>
      <?php endif; ?>

      <div class="loader-wrap" id="loading">
        <div class="loader"></div>
        <div class="loader-text">L'IA forge ton programme<span class="loader-dots"></span></div>
        <p style="color:var(--gris-clair); font-size:0.9rem;">Analyse de ton profil en cours...</p>
      </div>

      <div id="results">
        <?php if ($ai_response): ?>
          <?php if ($adaptation_message): ?>
            <div class="adaptation-banner">
              <?php echo h($adaptation_message); ?>
            </div>
          <?php endif; ?>

          <div class="profile-badges">
            <span class="badge">👤 <?php echo h($user['nom_complet']); ?></span>
            <span class="badge">🏅 <?php echo h($progression['niveau_adapte'] ?? $niveau); ?></span>
            <span class="badge">🏆 <?php echo h($selected_objectif); ?></span>
            <span class="badge">📅 <?php echo $selected_jours; ?> jours/sem</span>
            <span class="badge green">✅ Sauvegardé</span>
          </div>

          <div class="ai-response" id="aiContent">
            <?php echo parse_md_to_html(htmlspecialchars($ai_response, ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
          </div>

          <!-- E: Boutons d'action -->
          <div class="result-actions">
            <button class="btn-action print" onclick="printProgramme()">
              📄 Imprimer / PDF
            </button>
            <a href="coach_historique.php" class="btn-action history">
              📋 Voir l'historique
            </a>
          </div>

        <?php else: ?>
          <div class="empty-state">
            <div class="empty-icon">🤖</div>
            <h3 style="font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--blanc); margin:0;">
              En attente d'ordres
            </h3>
            <p style="max-width:380px; text-align:center;">
              Configure ton profil d'entraînement à gauche et laisse l'IA de RAKH Pulse créer ton prochain défi personnalisé.
            </p>
            <?php if ($progression['nb_seances'] > 0): ?>
              <div style="margin-top:1rem; background:rgba(255,215,0,0.05); border:1px solid rgba(255,215,0,0.1); padding:1rem 1.5rem; text-align:center;">
                <div style="font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--jaune);">
                  <?php echo $progression['nb_seances']; ?>
                </div>
                <div style="color:var(--gris-clair); font-size:0.8rem; text-transform:uppercase; letter-spacing:1px;">
                  séances complétées
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- Print page -->
<div id="printArea" style="display:none;">
  <style media="print">
    body > *:not(#printArea) { display: none !important; }
    #printArea { display: block !important; font-family: Arial, sans-serif; color: #000; padding: 2rem; }
    #printArea h1 { font-size: 2rem; }
    #printArea table { border-collapse: collapse; width: 100%; }
    #printArea th, #printArea td { border: 1px solid #ccc; padding: 0.5rem; }
    #printArea th { background: #f0f0f0; }
  </style>
  <div id="printContent"></div>
</div>

<script src="assets/js/script.js"></script>
<script>
  // Validation checkbox + loading
  document.getElementById('coachForm').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const checkedOne = Array.from(checkboxes).some(x => x.checked);
    if (!checkedOne) {
      e.preventDefault();
      alert('Veuillez sélectionner au moins un groupe musculaire.');
      return;
    }
    document.getElementById('loading').style.display = 'block';
    document.getElementById('results').style.display = 'none';
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<span>⏳</span> Génération en cours...';
  });

  // E: Impression / PDF
  function printProgramme() {
    const content = document.getElementById('aiContent');
    if (!content) return;
    const badges = document.querySelector('.profile-badges');
    const printDiv = document.getElementById('printContent');
    printDiv.innerHTML = `
      <h1 style="color:#000; border-bottom:3px solid #FFD700; padding-bottom:1rem;">
        🏋️ Mon Programme Calisthenics — RAKH Pulse
      </h1>
      <div style="margin-bottom:1.5rem; font-size:0.9rem; color:#555;">
        ${badges ? badges.innerText.replace(/\n/g, ' | ') : ''}
      </div>
      ${content.innerHTML}
      <p style="margin-top:3rem; font-size:0.75rem; color:#888; text-align:center;">
        Généré par RAKH Pulse Coach IA — ${new Date().toLocaleDateString('fr-FR')}
      </p>
    `;
    document.getElementById('printArea').style.display = 'block';
    window.print();
    setTimeout(() => { document.getElementById('printArea').style.display = 'none'; }, 1000);
  }
</script>
</body>
</html>
