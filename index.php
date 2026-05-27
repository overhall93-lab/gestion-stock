<?php
// ============================================================
//  index.php — Point d'entrée du projet
//  Redirige vers login si non connecté, sinon vers dashboard
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header('Location: login.php');
    exit;
}

// Mettre à jour l'activité
$_SESSION['derniere_activite'] = time();

// Rediriger vers le dashboard
header('Location: views/dashboard.php');
exit;