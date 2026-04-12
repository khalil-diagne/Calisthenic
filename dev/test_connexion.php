<?php
// ==============================================
// TEST DE CONNEXION À LA BASE DE DONNÉES
// Fichier de diagnostic - À SUPPRIMER APRÈS TEST
// ==============================================

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/../includes/config.php';

echo "<h1>🔧 Test de Connexion - Calisthenics Senegal</h1>";
echo "<hr>";

$DB_HOST = DB_HOST;
$DB_USER = DB_USER;
$DB_PASS = DB_PASS;
$DB_NAME = DB_NAME;

echo "<h2>Configuration actuelle:</h2>";
echo "<pre>";
echo "Host: " . $DB_HOST . "\n";
echo "User: " . $DB_USER . "\n";
echo "Password: " . ($DB_PASS ? "****" : "VIDE") . "\n";
echo "Database: " . $DB_NAME . "\n";
echo "</pre>";

echo "<h2>Test 1: Connexion à MySQL (sans base de données)</h2>";
$mysqli_test = new mysqli($DB_HOST, $DB_USER, $DB_PASS);

if ($mysqli_test->connect_error) {
    echo "<p style='color: red;'>❌ ERREUR: " . $mysqli_test->connect_error . "</p>";
    echo "<p><strong>Solutions possibles:</strong></p>";
    echo "<ul>";
    echo "<li>MySQL n'est pas en cours d'exécution (démarrez XAMPP/MySQL)</li>";
    echo "<li>Mauvais mot de passe (vérifiez 'root')</li>";
    echo "<li>MySQL n'écoute pas sur localhost:3306</li>";
    echo "</ul>";
    exit;
} else {
    echo "<p style='color: green;'>✅ Connexion à MySQL réussie!</p>";
}

echo "<h2>Test 2: Vérification de la base de données</h2>";
$result = $mysqli_test->query("SHOW DATABASES LIKE '" . $DB_NAME . "'");

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Base de données '" . $DB_NAME . "' existe!</p>";
} else {
    echo "<p style='color: red;'>❌ Base de données '" . $DB_NAME . "' N'EXISTE PAS</p>";
    echo "<p><strong>Solution:</strong> Exécutez ces commandes dans phpMyAdmin:</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo "CREATE DATABASE IF NOT EXISTS " . $DB_NAME . ";\n";
    echo "USE " . $DB_NAME . ";\n";
    echo "// Puis importez le fichier: create_users_table.sql\n";
    echo "</pre>";
    $mysqli_test->close();
    exit;
}

echo "<h2>Test 3: Connexion complète à la base de données</h2>";
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    echo "<p style='color: red;'>❌ ERREUR: " . $mysqli->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Connexion à la base complète réussie!</p>";
}

echo "<h2>Test 4: Vérification des tables</h2>";
$tables_needed = array('regions', 'niveaux', 'utilisateurs');
$all_exist = true;

foreach ($tables_needed as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '" . $table . "'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '" . $table . "' existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Table '" . $table . "' N'EXISTE PAS</p>";
        $all_exist = false;
    }
}

if (!$all_exist) {
    echo "<p><strong>Solution:</strong> Importez le fichier <code>create_users_table.sql</code> dans phpMyAdmin</p>";
}

echo "<h2>Test 5: Données dans les tables</h2>";
$count_regions = $mysqli->query("SELECT COUNT(*) as cnt FROM regions");
$row = $count_regions->fetch_assoc();
echo "<p>Régions: " . $row['cnt'] . " enregistrement(s)</p>";

$count_niveaux = $mysqli->query("SELECT COUNT(*) as cnt FROM niveaux");
$row = $count_niveaux->fetch_assoc();
echo "<p>Niveaux: " . $row['cnt'] . " enregistrement(s)</p>";

$count_users = $mysqli->query("SELECT COUNT(*) as cnt FROM utilisateurs");
$row = $count_users->fetch_assoc();
echo "<p>Utilisateurs: " . $row['cnt'] . " enregistrement(s)</p>";

echo "<hr>";
echo "<p style='color: green;'><strong>✅ Si tu vois tous les tests VERTS, tout fonctionne!</strong></p>";
echo "<p><strong>N'oublie pas de SUPPRIMER ce fichier (test_connexion.php) en production!</strong></p>";

$mysqli->close();
$mysqli_test->close();
?>
