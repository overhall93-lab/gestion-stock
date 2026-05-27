<?php
// ============================================================
//  views/layout/header.php — En-tête et navigation commune
//  À inclure EN PREMIER dans chaque vue (après define + requires)
//  Suppose que verifierConnexion() a déjà été appelé
// ============================================================

// Déterminer la page active pour surligner le bon lien de nav
$pageActuelle = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titrePage) ? htmlspecialchars($titrePage) . ' — ' : '' ?>GestionStock Pro</title>
    <link rel="stylesheet" href="<?= getBaseUrl() ?>assets/css/style.css">
</head>
<body>

<!-- ── Barre de navigation supérieure ── -->
<nav class="navbar">
    <div class="nav-marque">
        <span class="nav-logo">📦</span>
        <span class="nav-titre">GestionStock Pro</span>
    </div>

    <ul class="nav-liens">
        <li>
            <a href="<?= getBaseUrl() ?>views/dashboard.php"
               class="<?= $pageActuelle === 'dashboard' ? 'actif' : '' ?>">
               Tableau de bord
            </a>
        </li>
        <li>
            <a href="<?= getBaseUrl() ?>views/articles/liste.php"
               class="<?= in_array($pageActuelle, ['liste','formulaire','fiche']) ? 'actif' : '' ?>">
               Articles
            </a>
        </li>
        <li>
            <a href="<?= getBaseUrl() ?>views/mouvements/historique.php"
               class="<?= in_array($pageActuelle, ['historique','entree','sortie']) ? 'actif' : '' ?>">
               Mouvements
            </a>
        </li>
        <li>
            <a href="<?= getBaseUrl() ?>views/statistiques.php"
               class="<?= $pageActuelle === 'statistiques' ? 'actif' : '' ?>">
               Statistiques
            </a>
        </li>
        <!-- Panneau Admin : visible uniquement pour le rôle admin -->
        <?php if (estAdmin()): ?>
        <li>
            <a href="<?= getBaseUrl() ?>views/admin/utilisateurs.php"
               class="nav-admin <?= in_array($pageActuelle, ['utilisateurs','formulaire_user']) ? 'actif' : '' ?>">
               ⚙ Admin
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Infos utilisateur connecté + déconnexion -->
    <div class="nav-user">
        <span class="nav-user-info">
            <span class="nav-user-nom"><?= htmlspecialchars($_SESSION['nom'] ?? '') ?></span>
            <span class="nav-user-role badge-role <?= htmlspecialchars($_SESSION['role'] ?? '') ?>">
                <?= htmlspecialchars($_SESSION['role'] ?? '') ?>
            </span>
        </span>
        <a href="<?= getBaseUrl() ?>logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>

<!-- ── Contenu principal ── -->
<main class="contenu-principal">

    <!-- Zone de notification globale (alimentée par JS) -->
    <div id="notification-globale" class="notification" role="alert" aria-live="polite"></div>