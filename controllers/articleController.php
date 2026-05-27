<?php
// ============================================================
//  articleController.php — CRUD Articles
//  Actions : lister, lire, ajouter, modifier, archiver,
//            supprimer, rechercher
//  Appelé par AJAX depuis articles.js
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/xml-manager.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/validators.php';

header('Content-Type: application/json; charset=UTF-8');

// Toute requête sur ce controller exige d'être connecté
verifierConnexion();

$donnees = lireJsonAjax();
$action  = $donnees['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ────────────────────────────────────────────
    //  LISTER TOUS LES ARTICLES
    //  Accessible à tous les rôles
    // ────────────────────────────────────────────
    case 'lister':
        $articles = articlesListerTous();
        reponseJson(true, 'OK', $articles);
        break;

    // ────────────────────────────────────────────
    //  LIRE UN ARTICLE PAR SON ID
    //  Accessible à tous les rôles
    // ────────────────────────────────────────────
    case 'lire':
        $id      = nettoyerTexte($donnees['id'] ?? $_GET['id'] ?? '');
        $article = articleLireParId($id);
        if (!$article) {
            reponseJson(false, 'Article introuvable.');
        }
        // Enrichir avec l'historique des mouvements
        $mouvements = array_values(mouvementsParArticle($id));
        reponseJson(true, 'OK', $article, [
            'mouvements' => $mouvements
        ]);
        break;

    // ────────────────────────────────────────────
    //  AJOUTER UN ARTICLE
    //  Admin et Gestionnaire seulement
    // ────────────────────────────────────────────
    case 'ajouter':
        verifierRole([ROLE_ADMIN, ROLE_GESTIONNAIRE]);

        $erreurs = validerArticle($donnees);
        if (!empty($erreurs)) {
            reponseJson(false, implode(' | ', $erreurs));
        }

        $newId = articleAjouter($donnees);

        // Enregistrer un mouvement d'entrée initiale si stock > 0
        if ((int)$donnees['quantite_stock'] > 0) {
            mouvementEnregistrer(
                'entree',
                $newId,
                (int)$donnees['quantite_stock'],
                'Stock initial à la création',
                $_SESSION['id_utilisateur']
            );
        }

        reponseJson(true, 'Article ajouté avec succès.', null, [
            'id_article' => $newId
        ]);
        break;

    // ────────────────────────────────────────────
    //  MODIFIER UN ARTICLE
    //  Admin et Gestionnaire seulement
    // ────────────────────────────────────────────
    case 'modifier':
        verifierRole([ROLE_ADMIN, ROLE_GESTIONNAIRE]);

        $id = nettoyerTexte($donnees['id'] ?? '');
        if (empty($id)) {
            reponseJson(false, 'ID article manquant.');
        }

        $erreurs = validerArticle($donnees);
        if (!empty($erreurs)) {
            reponseJson(false, implode(' | ', $erreurs));
        }

        articleModifier($id, $donnees);
        reponseJson(true, 'Article modifié avec succès.');
        break;

    // ────────────────────────────────────────────
    //  ARCHIVER UN ARTICLE
    //  Admin et Gestionnaire seulement
    // ────────────────────────────────────────────
    case 'archiver':
        verifierRole([ROLE_ADMIN, ROLE_GESTIONNAIRE]);

        $id = nettoyerTexte($donnees['id'] ?? '');
        if (empty($id)) {
            reponseJson(false, 'ID article manquant.');
        }

        articleArchiver($id);
        reponseJson(true, 'Article archivé avec succès.');
        break;

    // ────────────────────────────────────────────
    //  SUPPRIMER UN ARTICLE
    //  Admin seulement
    // ────────────────────────────────────────────
    case 'supprimer':
        verifierRole(ROLE_ADMIN);

        $id = nettoyerTexte($donnees['id'] ?? '');
        if (empty($id)) {
            reponseJson(false, 'ID article manquant.');
        }

        articleSupprimer($id);
        reponseJson(true, 'Article supprimé définitivement.');
        break;

    // ────────────────────────────────────────────
    //  RECHERCHER DES ARTICLES
    //  Accessible à tous les rôles
    // ────────────────────────────────────────────
    case 'rechercher':
        $terme     = nettoyerTexte($donnees['terme'] ?? $_GET['terme'] ?? '');
        $resultats = articlesRechercher($terme);
        reponseJson(true, 'OK', $resultats);
        break;

    // ────────────────────────────────────────────
    //  ARTICLES EN ALERTE
    //  Accessible à tous les rôles
    // ────────────────────────────────────────────
    case 'alertes':
        $alertes = articlesEnAlerte();
        reponseJson(true, 'OK', $alertes);
        break;

    default:
        reponseJson(false, 'Action inconnue.');
}