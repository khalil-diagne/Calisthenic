<?php
// ==============================================
// Configuration globale & Gestion des sessions
// ==============================================

if (!defined('CALIS_ROOT')) {
    define('CALIS_ROOT', dirname(__DIR__));
}

$envFile = CALIS_ROOT . '/.env';
if (is_readable($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($envLines !== false) {
        foreach ($envLines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            $eq = strpos($line, '=');
            if ($eq === false) {
                continue;
            }
            $name = trim(substr($line, 0, $eq));
            $value = trim(substr($line, $eq + 1));
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $q = $value[0];
                if (substr($value, -1) === $q) {
                    $value = substr($value, 1, -1);
                }
            }
            if ($name !== '') {
                putenv($name . '=' . $value);
            }
        }
    }
}

function env_var($key, $default = '') {
    $v = getenv($key);
    if ($v === false || $v === '') {
        return $default;
    }
    return $v;
}

// Configuration MySQL
define('DB_HOST', env_var('DB_HOST', 'localhost'));
define('DB_USER', env_var('DB_USER', 'root'));
define('DB_PASS', env_var('DB_PASS', ''));
define('DB_NAME', env_var('DB_NAME', 'calisthenics_senegal'));
define('DB_PORT', (int) env_var('DB_PORT', '3306'));

// Configuration Sessions
define('SESSION_NAME', 'calis_session');
define('SESSION_TIMEOUT', 3600); // 1 heure

// Configuration IA (obligatoire dans .env pour le coach IA)
define('GEMINI_API_KEY', env_var('GEMINI_API_KEY', ''));

// Configuration Site
define('SITE_NAME', 'Calisthenics Senegal');
define('SITE_URL', rtrim(env_var('SITE_URL', 'http://localhost/img/calis/'), '/') . '/');

/**
 * Cible de redirection après login : chemins relatifs au dossier du site ou absolus sous SITE_URL.
 */
function safe_redirect_target($raw) {
    if (!is_string($raw) || $raw === '') {
        return 'index.php';
    }
    $raw = trim($raw);
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $raw)) {
        return 'index.php';
    }
    if (strlen($raw) >= 2 && substr($raw, 0, 2) === '//') {
        return 'index.php';
    }
    $parts = parse_url($raw);
    if ($parts === false) {
        return 'index.php';
    }
    if (!empty($parts['host'])) {
        return 'index.php';
    }
    $path = isset($parts['path']) ? $parts['path'] : '';
    if (strpos($path, '..') !== false) {
        return 'index.php';
    }
    $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
    $frag = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';

    $sitePath = parse_url(SITE_URL, PHP_URL_PATH);
    if ($sitePath === null || $sitePath === '') {
        $sitePath = '/';
    }
    $sitePath = rtrim($sitePath, '/') . '/';

    if ($path !== '' && $path[0] === '/') {
        $normPath = '/' . ltrim(str_replace('\\', '/', $path), '/');
        $prefix = rtrim($sitePath, '/') . '/';
        if (strpos($normPath, $prefix) !== 0) {
            return 'index.php';
        }
        $rel = substr($normPath, strlen($prefix));
        if ($rel === '' || $rel === false) {
            return 'index.php' . $query . $frag;
        }
        return $rel . $query . $frag;
    }

    $out = ltrim(str_replace('\\', '/', $path), '/');
    if ($out === '' && $query === '' && $frag === '') {
        return 'index.php';
    }
    return $out . $query . $frag;
}

// ==============================================
// Initialiser les sessions
// ==============================================
session_name(SESSION_NAME);
session_start();

// Vérifier timeout de session
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_destroy();
    header('Location: ' . SITE_URL . 'index.php?expired=1');
    exit;
}

// Mettre à jour l'heure d'activité
$_SESSION['last_activity'] = time();

// ==============================================
// Fonction de connexion à la base de données
// ==============================================
function connecter_db() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($mysqli->connect_error) {
        error_log("Erreur de connexion MySQL: " . $mysqli->connect_error);
        die("❌ Erreur de connexion à la base de données");
    }
    
    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

// ==============================================
// Fonction pour récupérer l'utilisateur en session
// ==============================================
function get_user_session() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// ==============================================
// Fonction pour vérifier si utilisateur connecté
// ==============================================
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ==============================================
// Fonction pour récupérer les données utilisateur
// ==============================================
function get_user_data($user_id) {
    $mysqli = connecter_db();
    
    $sql = "SELECT u.id, u.nom_complet, u.email, u.telephone, 
                   r.nom AS region, n.nom AS niveau, u.date_creation, u.actif
            FROM utilisateurs u
            LEFT JOIN regions r ON u.region_id = r.id
            LEFT JOIN niveaux n ON u.niveau_id = n.id
            WHERE u.id = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $user = null;
    }
    
    $stmt->close();
    $mysqli->close();
    
    return $user;
}

// ==============================================
// Fonction pour mettre en cache les données utilisateur
// ==============================================
function cache_user_data() {
    if (is_logged_in() && !isset($_SESSION['user_data'])) {
        $_SESSION['user_data'] = get_user_data($_SESSION['user_id']);
    }
}

// Mettre en cache au chargement
cache_user_data();

// ==============================================
// Gestion des erreurs
// ==============================================
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Log les erreurs
ini_set('log_errors', 1);
ini_set('error_log', CALIS_ROOT . '/logs/error.log');

// Créer le dossier logs s'il n'existe pas
if (!is_dir(CALIS_ROOT . '/logs')) {
    mkdir(CALIS_ROOT . '/logs', 0755, true);
}

// Charger les fonctions de workout
require_once __DIR__ . '/workout_functions.php';

?>
