<?php
// ============================================================
//  dashboardController.php — Tableau de bord + Statistiques
//  Actions : indicateurs, statistiques, evolution
//  Appelé par AJAX depuis dashboard.js et statistiques.js
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/xml-manager.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/validators.php';

header('Content-Type: application/json; charset=UTF-8');

verifierConnexion();

$donnees = lireJsonAjax();
$action  = $donnees['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ────────────────────────────────────────────
    //  INDICATEURS DU TABLEAU DE BORD
    //  4 widgets : total articles, valeur stock,
    //  nb alertes, derniers mouvements
    // ────────────────────────────────────────────
    case 'indicateurs':
        $articles      = articlesListerTous();
        $actifs        = array_filter($articles, fn($a) => $a['statut'] === 'actif');
        $alertes       = articlesEnAlerte();
        $mouvements    = mouvementsListerTous();
        $derniers      = array_slice($mouvements, 0, 5);

        // Enrichir les derniers mouvements avec le nom de l'article
        foreach ($derniers as &$m) {
            $art = articleLireParId($m['id_article']);
            $m['nom_article'] = $art ? $art['nom'] : 'Article supprimé';
        }
        unset($m);

        reponseJson(true, 'OK', null, [
            'total_articles'   => count($actifs),
            'valeur_stock'     => statsValeurStockTotal(),
            'nb_alertes'       => count($alertes),
            'derniers_mouvements' => array_values($derniers)
        ]);
        break;

    // ────────────────────────────────────────────
    //  STATISTIQUES COMPLÈTES
    //  Camembert + Top articles
    // ────────────────────────────────────────────
    case 'statistiques':
        $categories = statsRepartitionCategories();
        $top        = statsTopArticles(5);
        $valeur     = statsValeurStockTotal();
        $alertes    = articlesEnAlerte();

        reponseJson(true, 'OK', null, [
            'repartition_categories' => $categories,
            'top_articles'           => $top,
            'valeur_stock_total'     => $valeur,
            'articles_en_alerte'     => array_values($alertes)
        ]);
        break;

    // ────────────────────────────────────────────
    //  ÉVOLUTION DU STOCK D'UN ARTICLE
    //  Pour la courbe d'évolution
    // ────────────────────────────────────────────
    case 'evolution':
        $id = nettoyerTexte($donnees['id_article'] ?? $_GET['id_article'] ?? '');
        if (empty($id)) {
            reponseJson(false, 'ID article manquant.');
        }

        $evolution = statsEvolutionStock($id);
        $article   = articleLireParId($id);

        reponseJson(true, 'OK', null, [
            'evolution'   => $evolution,
            'nom_article' => $article ? $article['nom'] : ''
        ]);
        break;

    default:
        reponseJson(false, 'Action inconnue.');
}