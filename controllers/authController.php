<?php
// ============================================================
//  authController.php — Authentification
//  Gère le login et le logout
//  Appelé par login.php et logout.php
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/xml-manager.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/validators.php';

header('Content-Type: application/json; charset=UTF-8');

$donnees = lireJsonAjax();
$action  = $donnees['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ────────────────────────────────────────────
    //  LOGIN
    // ────────────────────────────────────────────
    case 'login':
        $login = nettoyerTexte($donnees['login'] ?? '');
        $mdp   = $donnees['password'] ?? '';

        if (empty($login) || empty($mdp)) {
            reponseJson(false, 'Login et mot de passe obligatoires.');
        }

        // Chercher l'utilisateur dans XML
        $utilisateur = utilisateurParLogin($login);

        if (!$utilisateur) {
            reponseJson(false, 'Identifiants incorrects.');
        }

        // Vérifier que le compte est actif
        if ($utilisateur['statut'] !== 'actif') {
            reponseJson(false, 'Ce compte est désactivé. Contactez l\'administrateur.');
        }

        // Vérifier le mot de passe hashé
        if (!password_verify($mdp, $utilisateur['password_hash'])) {
            reponseJson(false, 'Identifiants incorrects.');
        }

        // Créer la session
        creerSession($utilisateur);

        reponseJson(true, 'Connexion réussie.', null, [
            'role'     => $utilisateur['role'],
            'nom'      => $utilisateur['nom'],
            'redirect' => getBaseUrl() . 'index.php'
        ]);
        break;

    // ────────────────────────────────────────────
    //  LOGOUT
    // ────────────────────────────────────────────
    case 'logout':
        detruireSession();
        reponseJson(true, 'Déconnexion réussie.', null, [
            'redirect' => getBaseUrl() . 'login.php'
        ]);
        break;

    // ────────────────────────────────────────────
    //  VÉRIFIER SESSION (pour JS au chargement)
    // ────────────────────────────────────────────
    case 'verifier':
        if (!empty($_SESSION['connecte'])) {
            reponseJson(true, 'Session active.', null, [
                'role' => $_SESSION['role'],
                'nom'  => $_SESSION['nom']
            ]);
        } else {
            reponseJson(false, 'Non connecté.');
        }
        break;

    default:
        reponseJson(false, 'Action inconnue.');
}