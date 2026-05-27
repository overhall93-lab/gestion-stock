<?php
// ============================================================
//  config.php — Configuration centrale du projet
//  Tous les chemins, constantes et paramètres globaux
//  Inclure ce fichier en PREMIER dans chaque controller
// ============================================================

// --- Sécurité : empêcher l'accès direct à ce fichier ---
if (!defined('GESTION_STOCK')) {
    die('Accès direct interdit.');
}

// ============================================================
//  CHEMINS DES FICHIERS XML
// ============================================================
define('DATA_DIR',        __DIR__ . '/../data/');
define('XML_ARTICLES',    DATA_DIR . 'articles.xml');
define('XML_MOUVEMENTS',  DATA_DIR . 'mouvements.xml');
define('XML_UTILISATEURS',DATA_DIR . 'utilisateurs.xml');

// ============================================================
//  CHEMINS DES VUES
// ============================================================
define('VIEWS_DIR',       __DIR__ . '/../views/');

// ============================================================
//  RÔLES UTILISATEURS
// ============================================================
define('ROLE_ADMIN',        'admin');
define('ROLE_GESTIONNAIRE', 'gestionnaire');
define('ROLE_CONSULTANT',   'consultant');

// ============================================================
//  CATÉGORIES D'ARTICLES (liste fixe)
// ============================================================
define('CATEGORIES', [
    'Ordinateurs',
    'Peripheriques',
    'Composants',
    'Stockage',
    'Reseaux',
    'Ecrans',
    'Smartphones',
    'Accessoires'
]);

// ============================================================
//  PARAMÈTRES DE L'APPLICATION
// ============================================================
define('APP_NOM',           'GestionStock Pro');
define('APP_VERSION',       '1.0.0');
define('SESSION_DUREE',     3600);      // 1 heure en secondes
define('STOCK_ALERTE_MIN',  5);         // seuil par défaut si non défini

// ============================================================
//  PRÉFIXES DES IDENTIFIANTS XML
// ============================================================
define('PREFIX_ARTICLE',    'ART');
define('PREFIX_MOUVEMENT',  'MOV');
define('PREFIX_UTILISATEUR','U');