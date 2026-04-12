<?php
// ==============================================
// Fonctions d'authentification
// ==============================================

/**
 * Enregistrer un nouvel utilisateur
 */
function register_user($nom, $email, $telephone, $region, $niveau, $password) {
    $mysqli = connecter_db();
    
    // Vérifier si email existe
    $check_sql = "SELECT id FROM utilisateurs WHERE email = ? LIMIT 1";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $check_stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
    }
    $check_stmt->close();
    
    // Récupérer IDs région et niveau
    $region_sql = "SELECT id FROM regions WHERE nom = ? LIMIT 1";
    $region_stmt = $mysqli->prepare($region_sql);
    $region_stmt->bind_param("s", $region);
    $region_stmt->execute();
    $region_result = $region_stmt->get_result();
    
    if ($region_result->num_rows === 0) {
        $region_stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Région invalide'];
    }
    
    $region_row = $region_result->fetch_assoc();
    $region_id = $region_row['id'];
    $region_stmt->close();
    
    // Récupérer ID niveau
    $niveau_sql = "SELECT id FROM niveaux WHERE nom = ? LIMIT 1";
    $niveau_stmt = $mysqli->prepare($niveau_sql);
    $niveau_stmt->bind_param("s", $niveau);
    $niveau_stmt->execute();
    $niveau_result = $niveau_stmt->get_result();
    
    if ($niveau_result->num_rows === 0) {
        $niveau_stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Niveau invalide'];
    }
    
    $niveau_row = $niveau_result->fetch_assoc();
    $niveau_id = $niveau_row['id'];
    $niveau_stmt->close();
    
    // Hasher le mot de passe
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insérer l'utilisateur
    $insert_sql = "INSERT INTO utilisateurs (nom_complet, email, telephone, region_id, niveau_id, password) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $mysqli->prepare($insert_sql);
    
    if (!$insert_stmt) {
        $mysqli->close();
        return ['success' => false, 'message' => 'Erreur de base de données'];
    }
    
    $insert_stmt->bind_param("sssiis", $nom, $email, $telephone, $region_id, $niveau_id, $password_hash);
    
    if ($insert_stmt->execute()) {
        $user_id = $insert_stmt->insert_id;
        $insert_stmt->close();
        $mysqli->close();
        
        // Connecter automatiquement après inscription
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_data'] = get_user_data($user_id);
        
        return ['success' => true, 'message' => 'Inscription réussie!', 'user_id' => $user_id];
    } else {
        $insert_stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
    }
}

/**
 * Connecter un utilisateur
 */
function login_user($email, $password) {
    $mysqli = connecter_db();
    
    $sql = "SELECT id, email, password, nom_complet, actif 
            FROM utilisateurs 
            WHERE email = ? LIMIT 1";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Vérifier si compte actif
    if (!$user['actif']) {
        $mysqli->close();
        return ['success' => false, 'message' => 'Votre compte a été désactivé'];
    }
    
    // Vérifier mot de passe
    if (!password_verify($password, $user['password'])) {
        $mysqli->close();
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }
    
    // Créer la session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nom_complet'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_data'] = get_user_data($user['id']);
    
    $mysqli->close();
    return ['success' => true, 'message' => 'Connexion réussie!'];
}

/**
 * Déconnecter l'utilisateur
 */
function logout_user() {
    $_SESSION = [];
    session_destroy();
    return true;
}

/**
 * Changer le mot de passe
 */
function change_password($user_id, $old_password, $new_password) {
    $mysqli = connecter_db();
    
    // Récupérer le mot de passe actuel
    $sql = "SELECT password FROM utilisateurs WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Utilisateur non trouvé'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Vérifier ancien mot de passe
    if (!password_verify($old_password, $user['password'])) {
        $mysqli->close();
        return ['success' => false, 'message' => 'Ancien mot de passe incorrect'];
    }
    
    // Hasher le nouveau mot de passe
    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Mettre à jour
    $update_sql = "UPDATE utilisateurs SET password = ? WHERE id = ?";
    $update_stmt = $mysqli->prepare($update_sql);
    $update_stmt->bind_param("si", $new_hash, $user_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        $mysqli->close();
        return ['success' => true, 'message' => 'Mot de passe changé avec succès'];
    } else {
        $update_stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Erreur lors du changement'];
    }
}

/**
 * Mettre à jour le profil utilisateur
 */
function update_profile($user_id, $nom, $telephone, $region, $niveau) {
    $mysqli = connecter_db();
    
    // Récupérer IDs
    $region_sql = "SELECT id FROM regions WHERE nom = ? LIMIT 1";
    $region_stmt = $mysqli->prepare($region_sql);
    $region_stmt->bind_param("s", $region);
    $region_stmt->execute();
    $region_result = $region_stmt->get_result();
    
    if ($region_result->num_rows === 0) {
        $region_stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Région invalide'];
    }
    
    $region_row = $region_result->fetch_assoc();
    $region_id = $region_row['id'];
    $region_stmt->close();
    
    $niveau_sql = "SELECT id FROM niveaux WHERE nom = ? LIMIT 1";
    $niveau_stmt = $mysqli->prepare($niveau_sql);
    $niveau_stmt->bind_param("s", $niveau);
    $niveau_stmt->execute();
    $niveau_result = $niveau_stmt->get_result();
    
    if ($niveau_result->num_rows === 0) {
        $niveau_stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Niveau invalide'];
    }
    
    $niveau_row = $niveau_result->fetch_assoc();
    $niveau_id = $niveau_row['id'];
    $niveau_stmt->close();
    
    // Mettre à jour
    $update_sql = "UPDATE utilisateurs SET nom_complet = ?, telephone = ?, region_id = ?, niveau_id = ? WHERE id = ?";
    $update_stmt = $mysqli->prepare($update_sql);
    $update_stmt->bind_param("sssii", $nom, $telephone, $region_id, $niveau_id, $user_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        
        // Mettre à jour la session
        $_SESSION['user_name'] = $nom;
        $_SESSION['user_data'] = get_user_data($user_id);
        
        $mysqli->close();
        return ['success' => true, 'message' => 'Profil mis à jour'];
    } else {
        $update_stmt->close();
        $mysqli->close();
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

?>
