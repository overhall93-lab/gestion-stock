<?php
// ============================================================
//  logout.php — Déconnexion et destruction de session
// ============================================================
define('GESTION_STOCK', true);

require_once 'includes/config.php';
require_once 'includes/session.php';

detruireSession();

header('Location: login.php');
exit;