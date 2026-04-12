<?php
// ==============================================
// Vérifier que l'utilisateur est connecté
// Inclure ce fichier au début des pages protégées
// ==============================================

require_once __DIR__ . '/config.php';

if (!is_logged_in()) {
    header('Location: ' . SITE_URL . 'login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

?>
