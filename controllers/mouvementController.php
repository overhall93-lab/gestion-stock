<?php
// ============================================================
//  mouvementController.php — Mouvements de stock
//  Actions : entree, sortie, historique, parArticle, parPeriode
//  Appelé par AJAX depuis mouvements.js
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
    //  ENTRÉE DE STOCK
    //  Admin et Gestionnaire seulement
    // ────────────────────────────────────────────
    case 'entree':
        verifierRole([ROLE_ADMIN, ROLE_GESTIONNAIRE]);

        $donnees['type'] = 'entree';
        $erreurs = validerMouvement($donnees);
        if (!empty($erreurs)) {
            reponseJson(false, implode(' | ', $erreurs));
        }

        $resultat = mouvementEnregistrer(
            'entree',
            nettoyerTexte($donnees['id_article']),
            (int)$donnees['quantite'],
            nettoyerTexte($donnees['motif']),
            $_SESSION['id_utilisateur']
        );

        reponseJson(true, 'Entrée de stock enregistrée.', null, [
            'nouveau_stock' => $resultat['nouveau_stock'],
            'alerte'        => $resultat['alerte'],
            'id_mouvement'  => $resultat['id_mouvement']
        ]);
        break;

    // ────────────────────────────────────────────
    //  SORTIE DE STOCK
    //  Admin et Gestionnaire seulement
    // ────────────────────────────────────────────
    case 'sortie':
        verifierRole([ROLE_ADMIN, ROLE_GESTIONNAIRE]);

        $donnees['type'] = 'sortie';
        $erreurs = validerMouvement($donnees);
        if (!empty($erreurs)) {
            reponseJson(false, implode(' | ', $erreurs));
        }

        try {
            $resultat = mouvementEnregistrer(
                'sortie',
                nettoyerTexte($donnees['id_article']),
                (int)$donnees['quantite'],
                nettoyerTexte($donnees['motif']),
                $_SESSION['id_utilisateur']
            );
        } catch (Exception $e) {
            // Stock insuffisant ou article introuvable
            reponseJson(false, $e->getMessage());
        }

        reponseJson(true, 'Sortie de stock enregistrée.', null, [
            'nouveau_stock' => $resultat['nouveau_stock'],
            'alerte'        => $resultat['alerte'],
            'id_mouvement'  => $resultat['id_mouvement']
        ]);
        break;

    // ────────────────────────────────────────────
    //  HISTORIQUE COMPLET
    //  Accessible à tous les rôles
    // ────────────────────────────────────────────
    case 'historique':
        $mouvements = mouvementsListerTous();

        // Enrichir chaque mouvement avec le nom de l'article
        foreach ($mouvements as &$m) {
            $article  = articleLireParId($m['id_article']);
            $m['nom_article'] = $article ? $article['nom'] : 'Article supprimé';
        }
        unset($m);

        reponseJson(true, 'OK', array_values($mouvements));
        break;

    // ────────────────────────────────────────────
    //  HISTORIQUE PAR ARTICLE
    //  Accessible à tous les rôles
    // ────────────────────────────────────────────
    case 'parArticle':
        $id = nettoyerTexte($donnees['id_article'] ?? $_GET['id_article'] ?? '');
        if (empty($id)) {
            reponseJson(false, 'ID article manquant.');
        }

        $mouvements = array_values(mouvementsParArticle($id));
        reponseJson(true, 'OK', $mouvements);
        break;

    // ────────────────────────────────────────────
    //  HISTORIQUE PAR PÉRIODE
    //  Accessible à tous les rôles
    // ────────────────────────────────────────────
    case 'parPeriode':
        $debut = nettoyerTexte($donnees['date_debut'] ?? $_GET['date_debut'] ?? '');
        $fin   = nettoyerTexte($donnees['date_fin']   ?? $_GET['date_fin']   ?? '');

        if (empty($debut) || empty($fin)) {
            reponseJson(false, 'Dates de début et de fin obligatoires.');
        }
        if ($debut > $fin) {
            reponseJson(false, 'La date de début doit être antérieure à la date de fin.');
        }

        $mouvements = array_values(mouvementsParPeriode($debut, $fin));

        // Enrichir avec le nom de l'article
        foreach ($mouvements as &$m) {
            $article  = articleLireParId($m['id_article']);
            $m['nom_article'] = $article ? $article['nom'] : 'Article supprimé';
        }
        unset($m);

        reponseJson(true, 'OK', array_values($mouvements));
        break;

    default:
        reponseJson(false, 'Action inconnue.');
}