<?php
// ==============================================
// Gestion de l'inscription utilisateur (API JSON)
// ==============================================

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);

$mysqli = connecter_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['nom']) || !isset($data['email']) || !isset($data['password']) ||
        !isset($data['region']) || !isset($data['niveau'])) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
        $mysqli->close();
        exit;
    }

    $nom = trim($data['nom']);
    $email = trim($data['email']);
    $telephone = trim($data['telephone'] ?? '');
    $region = trim($data['region']);
    $niveau = trim($data['niveau']);
    $password = $data['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        $mysqli->close();
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit avoir au moins 6 caractères']);
        $mysqli->close();
        exit;
    }

    $check_sql = "SELECT id FROM utilisateurs WHERE email = ? LIMIT 1";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        $check_stmt->close();
        $mysqli->close();
        exit;
    }
    $check_stmt->close();

    $region_sql = "SELECT id FROM regions WHERE nom = ? LIMIT 1";
    $region_stmt = $mysqli->prepare($region_sql);
    $region_stmt->bind_param("s", $region);
    $region_stmt->execute();
    $region_result = $region_stmt->get_result();

    if ($region_result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Région non valide']);
        $region_stmt->close();
        $mysqli->close();
        exit;
    }

    $region_row = $region_result->fetch_assoc();
    $region_id = $region_row['id'];
    $region_stmt->close();

    $niveau_sql = "SELECT id FROM niveaux WHERE nom = ? LIMIT 1";
    $niveau_stmt = $mysqli->prepare($niveau_sql);
    $niveau_stmt->bind_param("s", $niveau);
    $niveau_stmt->execute();
    $niveau_result = $niveau_stmt->get_result();

    if ($niveau_result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Niveau non valide']);
        $niveau_stmt->close();
        $mysqli->close();
        exit;
    }

    $niveau_row = $niveau_result->fetch_assoc();
    $niveau_id = $niveau_row['id'];
    $niveau_stmt->close();

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $insert_sql = "INSERT INTO utilisateurs (nom_complet, email, telephone, region_id, niveau_id, password) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $mysqli->prepare($insert_sql);

    if (!$insert_stmt) {
        echo json_encode(['success' => false, 'message' => 'Erreur de préparation: ' . $mysqli->error]);
        $mysqli->close();
        exit;
    }

    $insert_stmt->bind_param("sssiis", $nom, $email, $telephone, $region_id, $niveau_id, $password_hash);

    if ($insert_stmt->execute()) {
        $user_id = $insert_stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Inscription réussie!',
            'user_id' => $user_id,
            'email' => $email
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription: ' . $insert_stmt->error]);
    }

    $insert_stmt->close();

} else if ($method == 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action == 'regions') {
        $regions_sql = "SELECT id, nom FROM regions ORDER BY nom ASC";
        $regions_result = $mysqli->query($regions_sql);
        $regions = [];

        while ($region = $regions_result->fetch_assoc()) {
            $regions[] = $region;
        }

        echo json_encode(['success' => true, 'data' => $regions]);

    } else if ($action == 'niveaux') {
        $niveaux_sql = "SELECT id, nom FROM niveaux ORDER BY id ASC";
        $niveaux_result = $mysqli->query($niveaux_sql);
        $niveaux = [];

        while ($niveau = $niveaux_result->fetch_assoc()) {
            $niveaux[] = $niveau;
        }

        echo json_encode(['success' => true, 'data' => $niveaux]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

$mysqli->close();
