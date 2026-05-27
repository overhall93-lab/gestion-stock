<?php
// ============================================================
//  index.php — Point d'entrée unique de l'application
//  Redirige vers login si pas connecté, dashboard sinon
// ============================================================
define('GESTION_STOCK', true);

require_once 'includes/config.php';
require_once 'includes/session.php';

// Si pas connecté → login
if (empty($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: login.php');
    exit;
}

// Si connecté → dashboard
header('Location: views/dashboard.php');
exit;