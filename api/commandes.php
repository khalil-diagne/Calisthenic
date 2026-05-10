<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Récupérer la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Créer la table des commandes si elle n'existe pas
function creer_table_commandes() {
    $db = connecter_db();
    $sql = "CREATE TABLE IF NOT EXISTS commandes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        produit VARCHAR(255) NOT NULL,
        prix VARCHAR(50) NOT NULL,
        nom VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        telephone VARCHAR(20) NOT NULL,
        adresse TEXT NOT NULL,
        statut ENUM('en_attente', 'confirmee', 'livree', 'annulee') DEFAULT 'en_attente',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
    )";
    
    if (!$db->query($sql)) {
        error_log("Erreur création table: " . $db->error);
    }
    $db->close();
}

// Créer la table au démarrage
creer_table_commandes();

if ($method === 'POST') {
    // Traiter une nouvelle commande
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    // Récupérer l'ID utilisateur connecté
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Valider les données
    $produit = isset($data['produit']) ? trim($data['produit']) : '';
    $prix = isset($data['prix']) ? trim($data['prix']) : '';
    $nom = isset($data['nom']) ? trim($data['nom']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $telephone = isset($data['tel']) ? trim($data['tel']) : '';
    $adresse = isset($data['adresse']) ? trim($data['adresse']) : '';
    
    if (empty($produit) || empty($nom) || empty($email) || empty($telephone) || empty($adresse)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tous les champs sont obligatoires'
        ]);
        exit;
    }
    
    // Insérer la commande en base
    $db = connecter_db();
    $stmt = $db->prepare("INSERT INTO commandes (user_id, produit, prix, nom, email, telephone, adresse) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $produit, $prix, $nom, $email, $telephone, $adresse);
    
    if ($stmt->execute()) {
        $commande_id = $db->insert_id;
        
        // Envoyer une notification WhatsApp (simulation - à intégrer avec Twilio/autre service)
        // Pour l'instant, on log juste
        error_log("Nouvelle commande #$commande_id: $nom ($email) - $produit");
        
        echo json_encode([
            'success' => true,
            'message' => 'Commande enregistrée avec succès',
            'commande_id' => $commande_id,
            'details' => [
                'produit' => $produit,
                'prix' => $prix,
                'nom' => $nom,
                'email' => $email,
                'telephone' => $telephone
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'enregistrement: ' . $db->error
        ]);
    }
    
    $stmt->close();
    $db->close();
    
} elseif ($method === 'GET') {
    // Récupérer les commandes (admin seulement)
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if ($action === 'list') {
        $db = connecter_db();
        $result = $db->query("SELECT * FROM commandes ORDER BY date_creation DESC LIMIT 100");
        
        $commandes = [];
        while ($row = $result->fetch_assoc()) {
            $commandes[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'total' => count($commandes),
            'commandes' => $commandes
        ]);
        
        $db->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Action non reconnue'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode HTTP non supportée'
    ]);
}
?>
