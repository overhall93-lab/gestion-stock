<?php
// ============================================================
//  adminController.php — Gestion des utilisateurs
//  Actions : lister, ajouter, modifierRole, desactiver
//  Admin seulement — toutes les actions sont protégées
//  Appelé par AJAX depuis admin.js
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/xml-manager.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/validators.php';

header('Content-Type: application/json; charset=UTF-8');

// Capturer toutes les erreurs PHP et les renvoyer en JSON
set_exception_handler(function($e) {
    echo json_encode([
        'succes'  => false,
        'message' => 'Erreur serveur : ' . $e->getMessage()
    ]);
    exit;
});
set_error_handler(function($errno, $errstr) {
    throw new ErrorException($errstr, $errno);
});

// Toutes les actions de ce controller sont réservées à l'admin
verifierRole(ROLE_ADMIN);

$donnees = lireJsonAjax();
$action  = $donnees['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ────────────────────────────────────────────
    //  LISTER TOUS LES UTILISATEURS
    // ────────────────────────────────────────────
    case 'lister':
        $utilisateurs = utilisateursListerTous();
        reponseJson(true, 'OK', $utilisateurs);
        break;

    // ────────────────────────────────────────────
    //  AJOUTER UN UTILISATEUR
    // ────────────────────────────────────────────
    case 'ajouter':
        $erreurs = validerUtilisateur($donnees);

        // Vérifier le mot de passe
        $mdp = $donnees['password'] ?? '';
        $erreursMdp = validerMotDePasse($mdp);
        $erreurs = array_merge($erreurs, $erreursMdp);

        if (!empty($erreurs)) {
            reponseJson(false, implode(' | ', $erreurs));
        }

        // Vérifier que le login n'existe pas déjà
        $existant = utilisateurParLogin(nettoyerTexte($donnees['login']));
        if ($existant) {
            reponseJson(false, 'Ce login est déjà utilisé.');
        }

        $newId = utilisateurAjouter($donnees, $mdp);
        reponseJson(true, 'Utilisateur créé avec succès.', null, [
            'id_utilisateur' => $newId
        ]);
        break;

    // ────────────────────────────────────────────
    //  MODIFIER LE RÔLE D'UN UTILISATEUR
    // ────────────────────────────────────────────
    case 'modifierRole':
        $id   = nettoyerTexte($donnees['id_utilisateur'] ?? '');
        $role = nettoyerTexte($donnees['role'] ?? '');

        if (empty($id)) {
            reponseJson(false, 'ID utilisateur manquant.');
        }

        $rolesValides = [ROLE_ADMIN, ROLE_GESTIONNAIRE, ROLE_CONSULTANT];
        if (!in_array($role, $rolesValides)) {
            reponseJson(false, 'Rôle invalide.');
        }

        // Empêcher l'admin de se retirer ses propres droits
        if ($id === $_SESSION['id_utilisateur'] && $role !== ROLE_ADMIN) {
            reponseJson(false, 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        utilisateurModifierRole($id, $role);
        reponseJson(true, 'Rôle modifié avec succès.');
        break;

    // ────────────────────────────────────────────
    //  DÉSACTIVER UN UTILISATEUR
    // ────────────────────────────────────────────
    case 'desactiver':
        $id = nettoyerTexte($donnees['id_utilisateur'] ?? '');

        if (empty($id)) {
            reponseJson(false, 'ID utilisateur manquant.');
        }

        // Empêcher l'admin de se désactiver lui-même
        if ($id === $_SESSION['id_utilisateur']) {
            reponseJson(false, 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        utilisateurDesactiver($id);
        reponseJson(true, 'Utilisateur désactivé avec succès.');
        break;

    default:
        reponseJson(false, 'Action inconnue.');
}