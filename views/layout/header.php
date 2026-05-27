<?php
// ============================================================
//  views/layout/header.php — En-tête commun à toutes les pages
//  USAGE : include en début de chaque vue, après verifierConnexion()
//  Variable attendue : $titrePage (string)
// ============================================================

if (!defined('GESTION_STOCK')) {
    define('GESTION_STOCK', true);
    require_once __DIR__ . '/../../includes/config.php';
    require_once __DIR__ . '/../../includes/session.php';
}
verifierConnexion();

$titrePage = $titrePage ?? 'Tableau de bord';

// Calcul du chemin racine relatif selon la profondeur de la vue
$profondeur = substr_count(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']),
              str_replace('\\', '/', realpath(__DIR__ . '/../../')));
$racine = str_repeat('../', substr_count(
    str_replace(realpath(__DIR__ . '/../../'), '', str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'])),
    '/'
));
// Chemin racine fiable
$racineUrl = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
if (strpos($_SERVER['SCRIPT_NAME'], '/views/') !== false) {
    $racineUrl = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
    $racineUrl = rtrim($racineUrl, '/') . '/';
} elseif (strpos($_SERVER['SCRIPT_NAME'], '/controllers/') !== false) {
    $racineUrl = dirname(dirname($_SERVER['SCRIPT_NAME']));
    $racineUrl = rtrim($racineUrl, '/') . '/';
} else {
    $racineUrl = dirname($_SERVER['SCRIPT_NAME']);
    $racineUrl = rtrim($racineUrl, '/') . '/';
}

// Chemin relatif CSS/JS
$assetsPath = $racineUrl . 'assets/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titrePage) ?> — <?= APP_NOM ?></title>
    <link rel="stylesheet" href="<?= $assetsPath ?>css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-brand">
        <a href="<?= $racineUrl ?>index.php"><?= APP_NOM ?></a>
    </div>

    <ul class="navbar-menu">
        <li>
            <a href="<?= $racineUrl ?>views/dashboard.php"
               class="<?= basename($_SERVER['SCRIPT_NAME']) === 'dashboard.php' ? 'active' : '' ?>">
                Tableau de bord
            </a>
        </li>
        <li>
            <a href="<?= $racineUrl ?>views/articles/liste.php"
               class="<?= strpos($_SERVER['SCRIPT_NAME'], '/articles/') !== false ? 'active' : '' ?>">
                Articles
            </a>
        </li>
        <li>
            <a href="<?= $racineUrl ?>views/mouvements/historique.php"
               class="<?= strpos($_SERVER['SCRIPT_NAME'], '/mouvements/') !== false ? 'active' : '' ?>">
                Mouvements
            </a>
        </li>
        <li>
            <a href="<?= $racineUrl ?>views/statistiques.php"
               class="<?= basename($_SERVER['SCRIPT_NAME']) === 'statistiques.php' ? 'active' : '' ?>">
                Statistiques
            </a>
        </li>
        <li>
            <a href="<?= $racineUrl ?>views/alertes.php"
               class="<?= basename($_SERVER['SCRIPT_NAME']) === 'alertes.php' ? 'active' : '' ?>"
               id="nav-alertes">
                Alertes <span class="badge" id="badge-alertes" style="display:none;">0</span>
            </a>
        </li>
        <?php if (estAdmin()): ?>
        <li>
            <a href="<?= $racineUrl ?>views/admin/utilisateurs.php"
               class="<?= strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false ? 'active' : '' ?>">
                Admin
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="navbar-user">
        <span class="user-nom"><?= htmlspecialchars($_SESSION['nom']) ?></span>
        <span class="user-role badge-role badge-role-<?= $_SESSION['role'] ?>">
            <?= ucfirst($_SESSION['role']) ?>
        </span>
        <a href="<?= $racineUrl ?>logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>

<div class="page-wrapper">
    <main class="main-content">
        <div class="page-header">
            <h2><?= htmlspecialchars($titrePage) ?></h2>
        </div>

<script>
// Chemin racine pour les appels AJAX dans les vues
const RACINE_URL = '<?= $racineUrl ?>';
</script>