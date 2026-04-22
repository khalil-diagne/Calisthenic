<?php
/**
 * api/coach_stream.php — Streaming SSE de la réponse IA Gemini
 * Reçoit les paramètres en POST JSON, streame la réponse token par token.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/check_session.php';

// Headers SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

// Désactiver le buffering PHP
if (ob_get_level()) ob_end_clean();

function send_event($data, $event = 'message') {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

$user_id  = $_SESSION['user_id'];
$user     = $_SESSION['user_data'];
$niveau   = $user['niveau'];

// Récupérer les paramètres POST
$raw         = file_get_contents('php://input');
$params      = json_decode($raw, true) ?? [];
$body_parts  = array_values(array_filter($params['body_parts'] ?? [], 'is_string'));
$objectif    = trim($params['objectif'] ?? 'Force générale');
$jours       = max(1, min(7, (int)($params['jours_semaine'] ?? 3)));

if (empty($body_parts)) {
    send_event(['error' => 'Aucun groupe musculaire sélectionné.'], 'error');
    exit;
}

$api_key = trim(GEMINI_API_KEY);
if ($api_key === '') {
    send_event(['error' => 'Clé API Gemini manquante.'], 'error');
    exit;
}

// F: Adaptation basée sur la progression
$mysqli  = connecter_db();
$stmt    = $mysqli->prepare("SELECT COUNT(*) as nb FROM workout_logs WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$nb_seances = (int)$stmt->get_result()->fetch_assoc()['nb'];
$stmt->close();
$mysqli->close();

$niveaux_ordre = ['Débutant', 'Intermédiaire', 'Avancé', 'Expert', 'Élite'];
$idx = array_search($niveau, $niveaux_ordre);
$niveau_adapte = ($nb_seances >= 20 && $idx !== false && $idx < count($niveaux_ordre) - 1)
    ? $niveaux_ordre[$idx + 1]
    : $niveau;

$parts_str = implode(', ', $body_parts);

$prompt = "Tu es un coach expert en Calisthenics (entraînement au poids du corps) pour RAKH Pulse, Sénégal.

PROFIL ATHLÈTE :
- Niveau : {$niveau_adapte}
- Objectif principal : {$objectif}
- Muscles ciblés : {$parts_str}
- Fréquence : {$jours} jours par semaine

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

4. Adapte STRICTEMENT la difficulté au niveau {$niveau_adapte}.
5. Uniquement des exercices sans matériel (au poids du corps).
6. Sois précis : nombre exact de séries, reps, temps de repos.";

// Appel API Gemini avec streamGenerateContent
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:streamGenerateContent?alt=sse&key=' . rawurlencode($api_key);
$post_data = json_encode([
    'contents'         => [['parts' => [['text' => $prompt]]]],
    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 1500]
]);

$full_text = '';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

// Traitement des chunks SSE reçus de Gemini
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use (&$full_text) {
    // Chaque chunk peut contenir plusieurs lignes SSE
    $lines = explode("\n", $chunk);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'data: ') === 0) {
            $json_str = substr($line, 6);
            if ($json_str === '[DONE]') break;
            $data = json_decode($json_str, true);
            $token = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($token !== '') {
                $full_text .= $token;
                send_event(['token' => $token], 'token');
            }
        }
    }
    return strlen($chunk);
});

curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    send_event(['error' => 'Erreur réseau : ' . $curl_error], 'error');
    exit;
}

// Sauvegarder en BDD si la génération a réussi
if ($full_text !== '') {
    $mysqli = connecter_db();
    $stmt = $mysqli->prepare(
        "INSERT INTO ia_programmes (user_id, niveau, objectif, parties_corps, jours_semaine, contenu)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isssis", $user_id, $niveau_adapte, $objectif, $parts_str, $jours, $full_text);
    $stmt->execute();
    $saved_id = $mysqli->insert_id;
    $stmt->close();
    $mysqli->close();

    send_event([
        'done'     => true,
        'saved_id' => $saved_id,
        'full'     => $full_text,
        'adaptation_msg' => $nb_seances >= 8 ? "🔥 {$nb_seances} séances — programme adapté au niveau {$niveau_adapte} !" : ''
    ], 'done');
} else {
    send_event(['error' => 'Réponse vide de l\'IA. Réessaie dans quelques secondes.'], 'error');
}
