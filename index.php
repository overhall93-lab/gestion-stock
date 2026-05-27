<?php
define('GESTION_STOCK', true);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/session.php';

// Si pas connecté, aller au login
verifierConnexion();

// Charger la vue demandée via paramètre GET, dashboard par défaut
$vuesAutorisees = [
    'dashboard',
    'articles/liste',
    'articles/formulaire',
    'articles/fiche',
    'mouvements/entree',
    'mouvements/sortie',
    'mouvements/historique',
    'statistiques',
    'alertes',
    'admin/utilisateurs',
    'admin/formulaire_user'
];

$vue = $_GET['vue'] ?? 'dashboard';

// Sécurité : n'autoriser que les vues de la liste blanche
if (!in_array($vue, $vuesAutorisees)) {
    $vue = 'dashboard';
}

// Certaines vues sont réservées à l'admin
$vuesAdmin = ['admin/utilisateurs', 'admin/formulaire_user'];
if (in_array($vue, $vuesAdmin) && !estAdmin()) {
    $vue = 'dashboard';
}

// Certaines vues sont interdites aux consultants
$vuesGestionnaire = ['mouvements/entree', 'mouvements/sortie', 'articles/formulaire'];
if (in_array($vue, $vuesGestionnaire) && !estGestionnaire()) {
    $vue = 'dashboard';
}

$fichierVue = VIEWS_DIR . $vue . '.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NOM ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php require_once VIEWS_DIR . 'layout/header.php'; ?>

<div class="app-wrapper">
    <?php require_once VIEWS_DIR . 'layout/sidebar.php'; ?>

    <main class="main-content">
        <?php
        if (file_exists($fichierVue)) {
            require_once $fichierVue;
        } else {
            echo '<div class="alert alert-erreur">Vue introuvable : ' . htmlspecialchars($vue) . '</div>';
        }
        ?>
    </main>
</div>

<?php require_once VIEWS_DIR . 'layout/footer.php'; ?>

<!-- Scripts JS globaux -->
<script src="assets/js/utils.js"></script>

<!-- Scripts JS selon la vue active -->
<?php if (str_starts_with($vue, 'articles'))     echo '<script src="assets/js/articles.js"></script>'; ?>
<?php if (str_starts_with($vue, 'mouvements'))   echo '<script src="assets/js/mouvements.js"></script>'; ?>
<?php if ($vue === 'statistiques')               echo '<script src="assets/js/statistiques.js"></script>'; ?>
<?php if (str_starts_with($vue, 'admin'))        echo '<script src="assets/js/admin.js"></script>'; ?>

</body>
</html>