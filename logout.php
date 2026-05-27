<?php
// ============================================================
//  logout.php — Déconnexion directe (sans AJAX)
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/session.php';

detruireSession();
header('Location: login.php');
exit;