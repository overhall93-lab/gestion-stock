<?php
// ============================================================
//  validators.php — Validation et nettoyage des données
//  Toujours appeler ces fonctions avant d'écrire dans XML
// ============================================================

if (!defined('GESTION_STOCK')) {
    die('Accès direct interdit.');
}

// ============================================================
//  NETTOYAGE GÉNÉRAL D'UNE CHAÎNE DE TEXTE
// ============================================================
function nettoyerTexte($valeur) {
    return htmlspecialchars(strip_tags(trim($valeur)), ENT_QUOTES, 'UTF-8');
}

// ============================================================
//  VALIDATION DES ARTICLES
// ============================================================
function validerArticle($donnees) {
    $erreurs = [];

    // Nom — obligatoire, entre 2 et 100 caractères
    if (empty($donnees['nom'])) {
        $erreurs[] = 'Le nom de l\'article est obligatoire.';
    } elseif (strlen($donnees['nom']) < 2 || strlen($donnees['nom']) > 100) {
        $erreurs[] = 'Le nom doit contenir entre 2 et 100 caractères.';
    }

    // Catégorie — doit être dans la liste autorisée
    if (empty($donnees['categorie'])) {
        $erreurs[] = 'La catégorie est obligatoire.';
    } elseif (!in_array($donnees['categorie'], CATEGORIES)) {
        $erreurs[] = 'Catégorie invalide.';
    }

    // Prix — décimal positif
    if (!isset($donnees['prix_unitaire']) || $donnees['prix_unitaire'] === '') {
        $erreurs[] = 'Le prix unitaire est obligatoire.';
    } elseif (!is_numeric($donnees['prix_unitaire']) || (float)$donnees['prix_unitaire'] < 0) {
        $erreurs[] = 'Le prix unitaire doit être un nombre positif.';
    }

    // Quantité — entier positif ou nul
    if (!isset($donnees['quantite_stock']) || $donnees['quantite_stock'] === '') {
        $erreurs[] = 'La quantité en stock est obligatoire.';
    } elseif (!ctype_digit((string)$donnees['quantite_stock']) || (int)$donnees['quantite_stock'] < 0) {
        $erreurs[] = 'La quantité doit être un entier positif ou nul.';
    }

    // Seuil d'alerte — entier positif
    if (!isset($donnees['seuil_alerte']) || $donnees['seuil_alerte'] === '') {
        $erreurs[] = 'Le seuil d\'alerte est obligatoire.';
    } elseif (!ctype_digit((string)$donnees['seuil_alerte']) || (int)$donnees['seuil_alerte'] < 0) {
        $erreurs[] = 'Le seuil d\'alerte doit être un entier positif ou nul.';
    }

    return $erreurs; // tableau vide = données valides
}

// ============================================================
//  VALIDATION D'UN MOUVEMENT DE STOCK
// ============================================================
function validerMouvement($donnees) {
    $erreurs = [];

    // Type — seulement "entree" ou "sortie"
    if (empty($donnees['type']) || !in_array($donnees['type'], ['entree', 'sortie'])) {
        $erreurs[] = 'Type de mouvement invalide (entree ou sortie).';
    }

    // id_article — obligatoire
    if (empty($donnees['id_article'])) {
        $erreurs[] = 'L\'article est obligatoire.';
    }

    // Quantité — entier strictement positif
    if (empty($donnees['quantite'])) {
        $erreurs[] = 'La quantité est obligatoire.';
    } elseif (!ctype_digit((string)$donnees['quantite']) || (int)$donnees['quantite'] <= 0) {
        $erreurs[] = 'La quantité doit être un entier strictement positif.';
    }

    // Motif — obligatoire, max 200 caractères
    if (empty($donnees['motif'])) {
        $erreurs[] = 'Le motif est obligatoire.';
    } elseif (strlen($donnees['motif']) > 200) {
        $erreurs[] = 'Le motif ne peut pas dépasser 200 caractères.';
    }

    return $erreurs;
}

// ============================================================
//  VALIDATION D'UN UTILISATEUR
// ============================================================
function validerUtilisateur($donnees) {
    $erreurs = [];

    // Nom — obligatoire
    if (empty($donnees['nom'])) {
        $erreurs[] = 'Le nom est obligatoire.';
    }

    // Login — obligatoire, alphanumérique, 3-30 caractères
    if (empty($donnees['login'])) {
        $erreurs[] = 'Le login est obligatoire.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $donnees['login'])) {
        $erreurs[] = 'Le login doit contenir entre 3 et 30 caractères alphanumériques.';
    }

    // Rôle — doit être dans les rôles définis
    $rolesValides = [ROLE_ADMIN, ROLE_GESTIONNAIRE, ROLE_CONSULTANT];
    if (empty($donnees['role']) || !in_array($donnees['role'], $rolesValides)) {
        $erreurs[] = 'Rôle invalide.';
    }

    return $erreurs;
}

// ============================================================
//  VALIDATION D'UN MOT DE PASSE
// ============================================================
function validerMotDePasse($mdp) {
    $erreurs = [];

    if (strlen($mdp) < 6) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
    if (strlen($mdp) > 100) {
        $erreurs[] = 'Le mot de passe est trop long.';
    }

    return $erreurs;
}

// ============================================================
//  LIRE LES DONNÉES JSON ENVOYÉES PAR AJAX
//  Retourne un tableau ou false si invalide
// ============================================================
function lireJsonAjax() {
    $input = file_get_contents('php://input');
    if (empty($input)) {
        return [];
    }
    $donnees = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }
    return $donnees;
}

// ============================================================
//  GÉNÉRER UNE RÉPONSE JSON STANDARD
// ============================================================
function reponseJson($succes, $message, $data = null, $extra = []) {
    header('Content-Type: application/json; charset=UTF-8');
    $reponse = [
        'succes'  => $succes,
        'message' => $message
    ];
    if ($data !== null) {
        $reponse['data'] = $data;
    }
    // Fusionner les données supplémentaires (ex: nouveau_stock, alerte)
    if (!empty($extra)) {
        $reponse = array_merge($reponse, $extra);
    }
    echo json_encode($reponse, JSON_UNESCAPED_UNICODE);
    exit;
}