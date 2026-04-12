<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/check_session.php';
require_once __DIR__ . '/includes/auth.php';

$user = $_SESSION['user_data'];
$niveau = $user['niveau']; // 'Débutant', 'Intermédiaire', etc.

$ai_response = '';
$is_loading = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body_parts'])) {
    $body_parts = implode(', ', $_POST['body_parts']);
    $is_loading = true;

    $api_key = trim(GEMINI_API_KEY);
    if ($api_key === '') {
        $error = 'Clé API Gemini manquante : ajoutez GEMINI_API_KEY dans le fichier .env (voir .env.example).';
        $is_loading = false;
    } else {

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . rawurlencode($api_key);

    $prompt_system = "Tu es un coach sportif expert en Calisthenics au Sénégal (RAKH Pulse). Ton but est de créer des entraînements sans matériel (au poids du corps) pour les athlètes. Réponds toujours avec un ton motivant, structuré (par puces ou étapes), en ajoutant des emojis adaptés, et sois très clair sur le nombre de séries et de répétitions.";
    
    $prompt_user = "Je suis d'un niveau $niveau en Calisthenics. Je veux travailler spécifiquement : $body_parts. Génère-moi un petit circuit d'entraînement adapté à mon niveau de 3 à 5 exercices. Précise le temps de repos.";

    $full_prompt = $prompt_system . "\n\nDemande de l'athlète :\n" . $prompt_user;

    $data = [
        'contents' => [
            [
                'parts' => [['text' => $full_prompt]]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 800
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error = "Erreur de connexion : " . curl_error($ch);
    } else {
        $result = json_decode($response, true);
        if ($httpcode == 200 && isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $ai_response = $result['candidates'][0]['content']['parts'][0]['text'];
        } else {
            $error = "Erreur de l'API IA : " . ($result['error']['message'] ?? 'Erreur inconnue');
        }
    }
    curl_close($ch);
    $is_loading = false;
    }
}

// Convert markdown to HTML for AI response
function parse_md_to_html($text) {
    // Bold
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    // Italic
    $text = preg_replace('/\_(.*?)\_/', '<em>$1</em>', $text);
    // Lists
    $text = preg_replace('/^\- (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    // Line breaks
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
  .coach-title p {
    color: var(--gris-clair);
    margin: 0.5rem 0 0 0;
  }
  .coach-nav a {
    color: var(--gris-clair);
    text-decoration: none;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
    border: 1px solid rgba(255,215,0,0.2);
    margin-left: 0.5rem;
  }
  .coach-nav a:hover, .coach-nav a.active {
    color: var(--jaune);
    border-color: var(--jaune);
  }
  
  .coach-content {
    max-width: 1000px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 2rem;
  }
  
  .coach-form-card {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 2rem;
    clip-path: polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%);
    height: fit-content;
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
    margin-bottom: 0.8rem;
  }
  
  .checkbox-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.8rem;
  }
  
  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--blanc);
    font-size: 0.9rem;
    cursor: pointer;
    background: rgba(255,255,255,0.05);
    padding: 0.8rem;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 4px;
    transition: all 0.2s;
  }
  
  .checkbox-label:hover {
    background: rgba(255,215,0,0.1);
    border-color: var(--jaune);
  }
  
  .checkbox-label input:checked + span {
    color: var(--jaune);
    font-weight: bold;
  }
  
  .btn-submit {
    width: 100%;
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
    transition: background 0.2s, transform 0.1s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }
  
  .btn-submit:hover {
    background: var(--vert);
    transform: translateY(-2px);
  }
  
  .coach-result-card {
    background: linear-gradient(145deg, var(--noir3), #1a1a1a);
    border: 1px solid rgba(255,215,0,0.2);
    padding: 2.5rem;
    clip-path: polygon(0 0, 100% 0%, 100% calc(100% - 24px), calc(100% - 24px) 100%, 0 100%);
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
    position: relative;
    overflow: hidden;
  }
  
  .result-watermark {
    position: absolute;
    top: 50%;
    right: -10%;
    transform: translateY(-50%);
    font-size: 15rem;
    opacity: 0.02;
    pointer-events: none;
  }
  
  .profile-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,215,0,0.1);
    color: var(--jaune);
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: bold;
    margin-bottom: 2rem;
  }
  
  .ai-response {
    color: var(--blanc);
    line-height: 1.8;
    font-size: 1.05rem;
    animation: fadeIn 0.5s ease-out;
  }
  
  .ai-response h1, .ai-response h2, .ai-response h3 {
    color: var(--jaune);
    font-family: 'Bebas Neue', sans-serif;
    margin-top: 2rem;
    margin-bottom: 1rem;
    letter-spacing: 1px;
  }
  
  .ai-response ul {
    padding-left: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .ai-response li {
    margin-bottom: 0.8rem;
    color: #e0e0e0;
  }
  
  .ai-response strong {
    color: var(--jaune);
  }
  
  .empty-state {
    text-align: center;
    color: var(--gris-clair);
    padding: 4rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }
  
  .empty-icon {
    font-size: 4rem;
    opacity: 0.5;
  }
  
  .loader {
    border: 4px solid rgba(255, 215, 0, 0.1);
    border-left-color: var(--jaune);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin: 0 auto;
  }
  
  @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
  
  .error-box {
    background: rgba(255, 69, 0, 0.1);
    color: #ff6b6b;
    padding: 1rem;
    border-left: 4px solid #ff6b6b;
    margin-bottom: 1.5rem;
  }

  @media (max-width: 900px) {
    .coach-content {
      grid-template-columns: 1fr;
    }
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
      <p>Ton programme d'entraînement généré sur-mesure</p>
    </div>
    <div class="coach-nav">
      <a href="profile.php">Mon Profil</a>
      <a href="index.php">← Retour</a>
    </div>
  </div>
  
  <div class="coach-content">
    
    <!-- Sidebar Form -->
    <div class="coach-form-card">
      <div class="card-title">Configuration</div>
      
      <form method="POST" action="coach_ia.php" onsubmit="document.getElementById('loading').style.display='block'; document.getElementById('results').style.display='none';">
        <div class="form-group">
          <label>Niveau Actuel</label>
          <div style="background:rgba(255,215,0,0.1); padding:0.8rem; color:var(--jaune); border:1px solid rgba(255,215,0,0.3); font-weight:bold;">
            <?php echo htmlspecialchars($niveau); ?>
          </div>
          <small style="color:var(--gris-clair); display:block; margin-top:0.5rem; font-size:0.75rem;">Modifiable dans ton <a href="profile.php" style="color:var(--jaune);">profil</a>.</small>
        </div>
        
        <div class="form-group">
          <label>Parties du corps à cibler</label>
          <div class="checkbox-grid">
            <label class="checkbox-label">
              <input type="checkbox" name="body_parts[]" value="Pectoraux">
              <span>Pectoraux</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" name="body_parts[]" value="Dos">
              <span>Dos</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" name="body_parts[]" value="Bras (Biceps/Triceps)">
              <span>Bras</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" name="body_parts[]" value="Jambes">
              <span>Jambes</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" name="body_parts[]" value="Abdominaux (Core)">
              <span>Abdominaux</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" name="body_parts[]" value="Full Body (Tout le corps)">
              <span>Full Body</span>
            </label>
          </div>
        </div>
        
        <button type="submit" class="btn-submit">
          Générer mon programme ⚡
        </button>
      </form>
    </div>
    
    <!-- Results Board -->
    <div class="coach-result-card">
      <div class="result-watermark">🦾</div>
      
      <?php if ($error): ?>
        <div class="error-box">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      
      <div id="loading" style="display:none; text-align:center; padding:5rem 0;">
        <div class="loader"></div>
        <h3 style="color:var(--jaune); margin-top:2rem; font-family:'Bebas Neue', sans-serif; letter-spacing:1px; font-size:1.5rem;">Analyse & Génération...</h3>
        <p style="color:var(--gris-clair);">L'IA forge ton circuit sur-mesure !</p>
      </div>

      <div id="results">
        <?php if ($ai_response): ?>
          <div class="profile-badge">
            👤 Athlète : <?php echo htmlspecialchars($user['nom_complet']); ?> | 🎯 Objectif ciblé
          </div>
          <div class="ai-response">
            <?php echo parse_md_to_html(htmlspecialchars($ai_response, ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <div class="empty-icon">🤖</div>
            <h3 style="font-family:'Bebas Neue', sans-serif; font-size:2rem; color:var(--blanc); margin:0;">En attente d'ordres</h3>
            <p>Sélectionne les muscles que tu souhaites travailler et laisse l'IA de RAKH Pulse concevoir ton prochain défi.</p>
          </div>
        <?php endif; ?>
      </div>
      
    </div>
  </div>
</div>

<script src="assets/js/script.js"></script>
<script>
  // Require at least one checkbox
  document.querySelector('form').addEventListener('submit', function(e) {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);
    if (!checkedOne) {
      e.preventDefault();
      alert("Veuillez sélectionner au moins une partie du corps à travailler.");
      document.getElementById('loading').style.display='none';
      document.getElementById('results').style.display='block';
    }
  });
</script>
</body>
</html>
