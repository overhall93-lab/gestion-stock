<?php
// ============================================================
//  session.php — Gestion des sessions et contrôle d'accès
//  À inclure en tête de chaque page et controller
// ============================================================

if (!defined('GESTION_STOCK')) {
    die('Accès direct interdit.');
}

// Démarrer la session si pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
//  VÉRIFIER SI L'UTILISATEUR EST CONNECTÉ
//  Redirige vers login.php si la session est absente ou expirée
// ============================================================
function verifierConnexion() {
    if (empty($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
        header('Location: ' . getBaseUrl() . 'login.php');
        exit;
    }

    // Vérifier l'expiration de la session
    if (isset($_SESSION['derniere_activite'])) {
        $inactif = time() - $_SESSION['derniere_activite'];
        if ($inactif > SESSION_DUREE) {
            detruireSession();
            header('Location: ' . getBaseUrl() . 'login.php?expiration=1');
            exit;
        }
    }

    // Mettre à jour le timestamp d'activité
    $_SESSION['derniere_activite'] = time();
}

// ============================================================
//  VÉRIFIER UN RÔLE PRÉCIS
//  Arrête l'exécution si le rôle ne correspond pas
// ============================================================
function verifierRole($rolesAutorises) {
    verifierConnexion();

    // Accepter un rôle unique ou un tableau de rôles
    if (!is_array($rolesAutorises)) {
        $rolesAutorises = [$rolesAutorises];
    }

    if (!in_array($_SESSION['role'], $rolesAutorises)) {
        // Réponse JSON si appel AJAX
        if (estRequeteAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'succes'  => false,
                'message' => 'Accès refusé. Droits insuffisants.'
            ]);
            exit;
        }
        // Sinon redirection
        header('Location: ' . getBaseUrl() . 'index.php?erreur=acces_refuse');
        exit;
    }
}

// ============================================================
//  CRÉER UNE SESSION APRÈS LOGIN RÉUSSI
// ============================================================
function creerSession($utilisateur) {
    session_regenerate_id(true); // sécurité : nouvel ID de session
    $_SESSION['connecte']         = true;
    $_SESSION['id_utilisateur']   = $utilisateur['id'];
    $_SESSION['nom']              = $utilisateur['nom'];
    $_SESSION['login']            = $utilisateur['login'];
    $_SESSION['role']             = $utilisateur['role'];
    $_SESSION['derniere_activite']= time();
}

// ============================================================
//  DÉTRUIRE LA SESSION (logout)
// ============================================================
function detruireSession() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ============================================================
//  HELPERS DE RÔLE — vérifications rapides dans les vues
// ============================================================
function estAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

function estGestionnaire() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_GESTIONNAIRE]);
}

function estConsultant() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_CONSULTANT;
}

// ============================================================
//  DÉTECTER SI LA REQUÊTE VIENT D'AJAX
// ============================================================
function estRequeteAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// ============================================================
//  RÉCUPÉRER L'URL DE BASE DU PROJET
// ============================================================
function getBaseUrl() {
    $protocole = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'];
    $dossier   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // Remonter jusqu'à la racine du projet
    $parts     = explode('/', trim($dossier, '/'));
    // Chercher "gestion-stock" dans le chemin
    $racine    = '';
    foreach ($parts as $part) {
        $racine .= '/' . $part;
        if ($part === 'gestion-stock') break;
    }
    return $protocole . '://' . $host . $racine . '/';
}