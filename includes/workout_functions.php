<?php
// Les dépendances de config.php sont supposées déjà chargées.

/**
 * Démarre un nouveau programme pour l'utilisateur
 */
function start_program($user_id, $programme_id) {
    $mysqli = connecter_db();
    
    // Vérifier s'il a déjà un programme en cours de ce type
    $check_sql = "SELECT id FROM user_programmes WHERE user_id = ? AND programme_id = ? AND status = 'en_cours'";
    $stmt = $mysqli->prepare($check_sql);
    $stmt->bind_param("is", $user_id, $programme_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Ce programme est déjà en cours.'];
    }
    $stmt->close();
    
    $insert_sql = "INSERT INTO user_programmes (user_id, programme_id, status) VALUES (?, ?, 'en_cours')";
    $stmt = $mysqli->prepare($insert_sql);
    $stmt->bind_param("is", $user_id, $programme_id);
    $res = $stmt->execute();
    $stmt->close();
    $mysqli->close();
    
    return ['success' => $res, 'message' => $res ? 'Programme démarré avec succès!' : 'Erreur SQL.'];
}

/**
 * Récupère le(s) programme(s) actif(s) de l'utilisateur
 */
function get_active_program($user_id) {
    if (!$user_id) return null;
    $mysqli = connecter_db();
    $sql = "SELECT * FROM user_programmes WHERE user_id = ? AND status = 'en_cours' ORDER BY date_debut DESC LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();
    $mysqli->close();
    return $row;
}

/**
 * Récupère un programme actif spécifique
 */
function get_specific_active_program($user_id, $programme_id) {
    if (!$user_id) return null;
    $mysqli = connecter_db();
    $sql = "SELECT * FROM user_programmes WHERE user_id = ? AND programme_id = ? AND status = 'en_cours' LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $user_id, $programme_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->num_rows > 0 ? $result->fetch_assoc() : null;
    $stmt->close();
    $mysqli->close();
    return $row;
}

/**
 * Log une séance d'entraînement terminée
 */
function log_workout($user_id, $programme_id, $semaine, $jour, $notes = '') {
    $mysqli = connecter_db();
    $sql = "INSERT IGNORE INTO workout_logs (user_id, programme_id, semaine, jour, notes) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("issis", $user_id, $programme_id, $semaine, $jour, $notes);
    $res = $stmt->execute();
    $stmt->close();
    $mysqli->close();
    return ['success' => $res];
}

/**
 * Récupère toutes les séances complétées pour un programme sous forme de tableau multidimensionnel
 */
function get_completed_workouts($user_id, $programme_id) {
    if (!$user_id) return [];
    
    $mysqli = connecter_db();
    $sql = "SELECT semaine, jour, date_completion FROM workout_logs WHERE user_id = ? AND programme_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $user_id, $programme_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed = [];
    while($row = $result->fetch_assoc()) {
        if (!isset($completed[$row['semaine']])) {
            $completed[$row['semaine']] = [];
        }
        $completed[$row['semaine']][$row['jour']] = $row['date_completion'];
    }
    $stmt->close();
    $mysqli->close();
    return $completed;
}

/**
 * Calcule la progression (%) d'un programme
 */
function calculate_progress($completed_workouts, $total_sessions = 16) {
    if (empty($completed_workouts)) return 0;
    
    $count = 0;
    foreach ($completed_workouts as $semaine => $jours) {
        $count += count($jours);
    }
    return min(100, round(($count / $total_sessions) * 100));
}
?>
