<?php
// ============================================================
//  xml-manager.php — Moteur central de manipulation XML
//  C'est le fichier le plus critique du projet.
//  Toutes les lectures et écritures XML passent par ici.
// ============================================================

if (!defined('GESTION_STOCK')) {
    die('Accès direct interdit.');
}

// ============================================================
//  CHARGER UN FICHIER XML
//  Retourne un objet DOMDocument prêt à l'emploi
// ============================================================
function xmlCharger($chemin) {
    if (!file_exists($chemin)) {
        throw new Exception("Fichier XML introuvable : $chemin");
    }
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput       = true;
    if (!$dom->load($chemin)) {
        throw new Exception("Impossible de charger le fichier XML : $chemin");
    }
    return $dom;
}

// ============================================================
//  SAUVEGARDER UN FICHIER XML AVEC VERROU
//  Empêche la corruption en cas d'accès simultané
// ============================================================
function xmlSauvegarder(DOMDocument $dom, $chemin) {
    $fp = fopen($chemin, 'r+');
    if (!$fp) {
        throw new Exception("Impossible d'ouvrir le fichier pour écriture : $chemin");
    }
    if (flock($fp, LOCK_EX)) {       // verrou exclusif
        $dom->save($chemin);
        flock($fp, LOCK_UN);          // libérer le verrou
    } else {
        fclose($fp);
        throw new Exception("Impossible de verrouiller le fichier : $chemin");
    }
    fclose($fp);
}

// ============================================================
//  CRÉER UN ÉLÉMENT XML AVEC TEXTE
//  Helper pour éviter la répétition
// ============================================================
function xmlCreerElement(DOMDocument $dom, $tag, $valeur) {
    $element = $dom->createElement($tag);
    $element->appendChild($dom->createTextNode((string)$valeur));
    return $element;
}

// ============================================================
//  GÉNÉRER UN NOUVEL IDENTIFIANT UNIQUE
//  Ex : xmlGenererID('ART', $dom, 'article') → ART004
// ============================================================
function xmlGenererID($prefixe, DOMDocument $dom, $tagElement) {
    $elements = $dom->getElementsByTagName($tagElement);
    $nb       = $elements->length + 1;
    // Chercher l'ID le plus élevé pour éviter les doublons après suppression
    $maxId = 0;
    foreach ($elements as $el) {
        $id  = $el->getAttribute('id');
        $num = (int) preg_replace('/[^0-9]/', '', $id);
        if ($num > $maxId) $maxId = $num;
    }
    return $prefixe . str_pad($maxId + 1, 3, '0', STR_PAD_LEFT);
}

// ============================================================
//  ─────────────────────────────────────────────────────────
//  FONCTIONS ARTICLES
//  ─────────────────────────────────────────────────────────
// ============================================================

// Lire tous les articles — retourne un tableau PHP
function articlesListerTous() {
    $dom      = xmlCharger(XML_ARTICLES);
    $articles = [];
    foreach ($dom->getElementsByTagName('article') as $node) {
        $articles[] = articleNodeVersTableau($node);
    }
    return $articles;
}

// Lire un article par son ID — retourne un tableau ou null
function articleLireParId($id) {
    $dom  = xmlCharger(XML_ARTICLES);
    $node = articleTrouverNode($dom, $id);
    if (!$node) return null;
    return articleNodeVersTableau($node);
}

// Ajouter un article — retourne le nouvel ID
function articleAjouter($donnees) {
    $dom    = xmlCharger(XML_ARTICLES);
    $racine = $dom->documentElement;
    $newId  = xmlGenererID(PREFIX_ARTICLE, $dom, 'article');

    $article = $dom->createElement('article');
    $article->setAttribute('id', $newId);
    $article->appendChild(xmlCreerElement($dom, 'nom',            nettoyerTexte($donnees['nom'])));
    $article->appendChild(xmlCreerElement($dom, 'categorie',      nettoyerTexte($donnees['categorie'])));
    $article->appendChild(xmlCreerElement($dom, 'prix_unitaire',  (float)$donnees['prix_unitaire']));
    $article->appendChild(xmlCreerElement($dom, 'quantite_stock', (int)$donnees['quantite_stock']));
    $article->appendChild(xmlCreerElement($dom, 'seuil_alerte',   (int)$donnees['seuil_alerte']));
    $article->appendChild(xmlCreerElement($dom, 'date_ajout',     date('Y-m-d')));
    $article->appendChild(xmlCreerElement($dom, 'statut',         'actif'));

    $racine->appendChild($article);
    xmlSauvegarder($dom, XML_ARTICLES);
    return $newId;
}

// Modifier un article existant
function articleModifier($id, $donnees) {
    $dom  = xmlCharger(XML_ARTICLES);
    $node = articleTrouverNode($dom, $id);
    if (!$node) throw new Exception("Article introuvable : $id");

    articleMettreAJourChamp($node, 'nom',            nettoyerTexte($donnees['nom']));
    articleMettreAJourChamp($node, 'categorie',      nettoyerTexte($donnees['categorie']));
    articleMettreAJourChamp($node, 'prix_unitaire',  (float)$donnees['prix_unitaire']);
    articleMettreAJourChamp($node, 'seuil_alerte',   (int)$donnees['seuil_alerte']);

    xmlSauvegarder($dom, XML_ARTICLES);
    return true;
}

// Mettre à jour uniquement le stock d'un article
function articleMettreAJourStock($id, $nouvelleQuantite) {
    $dom  = xmlCharger(XML_ARTICLES);
    $node = articleTrouverNode($dom, $id);
    if (!$node) throw new Exception("Article introuvable : $id");

    articleMettreAJourChamp($node, 'quantite_stock', (int)$nouvelleQuantite);
    xmlSauvegarder($dom, XML_ARTICLES);
    return true;
}

// Archiver un article (jamais supprimer définitivement)
function articleArchiver($id) {
    $dom  = xmlCharger(XML_ARTICLES);
    $node = articleTrouverNode($dom, $id);
    if (!$node) throw new Exception("Article introuvable : $id");

    articleMettreAJourChamp($node, 'statut', 'archive');
    xmlSauvegarder($dom, XML_ARTICLES);
    return true;
}

// Supprimer définitivement un article (admin seulement)
function articleSupprimer($id) {
    $dom  = xmlCharger(XML_ARTICLES);
    $node = articleTrouverNode($dom, $id);
    if (!$node) throw new Exception("Article introuvable : $id");

    $node->parentNode->removeChild($node);
    xmlSauvegarder($dom, XML_ARTICLES);
    return true;
}

// Rechercher des articles par nom ou catégorie
function articlesRechercher($terme) {
    $tous      = articlesListerTous();
    $terme     = strtolower(trim($terme));
    $resultats = [];
    foreach ($tous as $article) {
        if (strpos(strtolower($article['nom']), $terme) !== false ||
            strpos(strtolower($article['categorie']), $terme) !== false) {
            $resultats[] = $article;
        }
    }
    return $resultats;
}

// Lister les articles en alerte de stock
function articlesEnAlerte() {
    $tous    = articlesListerTous();
    $alertes = [];
    foreach ($tous as $article) {
        if ((int)$article['quantite_stock'] <= (int)$article['seuil_alerte']
            && $article['statut'] === 'actif') {
            $alertes[] = $article;
        }
    }
    return $alertes;
}

// ─── Helpers privés articles ───
function articleTrouverNode(DOMDocument $dom, $id) {
    foreach ($dom->getElementsByTagName('article') as $node) {
        if ($node->getAttribute('id') === $id) return $node;
    }
    return null;
}

function articleMettreAJourChamp(DOMElement $node, $tag, $valeur) {
    $champ = $node->getElementsByTagName($tag)->item(0);
    if ($champ) $champ->nodeValue = (string)$valeur;
}

function articleNodeVersTableau(DOMElement $node) {
    return [
        'id'             => $node->getAttribute('id'),
        'nom'            => $node->getElementsByTagName('nom')->item(0)->nodeValue,
        'categorie'      => $node->getElementsByTagName('categorie')->item(0)->nodeValue,
        'prix_unitaire'  => $node->getElementsByTagName('prix_unitaire')->item(0)->nodeValue,
        'quantite_stock' => $node->getElementsByTagName('quantite_stock')->item(0)->nodeValue,
        'seuil_alerte'   => $node->getElementsByTagName('seuil_alerte')->item(0)->nodeValue,
        'date_ajout'     => $node->getElementsByTagName('date_ajout')->item(0)->nodeValue,
        'statut'         => $node->getElementsByTagName('statut')->item(0)->nodeValue,
    ];
}

// ============================================================
//  ─────────────────────────────────────────────────────────
//  FONCTIONS MOUVEMENTS
//  ─────────────────────────────────────────────────────────
// ============================================================

// Enregistrer un mouvement de stock
function mouvementEnregistrer($type, $idArticle, $quantite, $motif, $idUtilisateur) {
    // 1. Vérifier et mettre à jour le stock dans articles.xml
    $article = articleLireParId($idArticle);
    if (!$article) throw new Exception("Article introuvable : $idArticle");

    $stockActuel = (int)$article['quantite_stock'];
    $quantite    = (int)$quantite;

    if ($type === 'sortie' && $quantite > $stockActuel) {
        throw new Exception("Stock insuffisant. Stock actuel : $stockActuel");
    }

    $nouveauStock = ($type === 'entree')
        ? $stockActuel + $quantite
        : $stockActuel - $quantite;

    // 2. Mettre à jour articles.xml
    articleMettreAJourStock($idArticle, $nouveauStock);

    // 3. Enregistrer dans mouvements.xml
    $dom    = xmlCharger(XML_MOUVEMENTS);
    $racine = $dom->documentElement;
    $newId  = xmlGenererID(PREFIX_MOUVEMENT, $dom, 'mouvement');

    $mouvement = $dom->createElement('mouvement');
    $mouvement->setAttribute('id', $newId);
    $mouvement->appendChild(xmlCreerElement($dom, 'type',          $type));
    $mouvement->appendChild(xmlCreerElement($dom, 'id_article',    $idArticle));
    $mouvement->appendChild(xmlCreerElement($dom, 'quantite',      $quantite));
    $mouvement->appendChild(xmlCreerElement($dom, 'motif',         nettoyerTexte($motif)));
    $mouvement->appendChild(xmlCreerElement($dom, 'date_heure',    date('Y-m-d H:i:s')));
    $mouvement->appendChild(xmlCreerElement($dom, 'id_utilisateur',$idUtilisateur));
    $mouvement->appendChild(xmlCreerElement($dom, 'stock_apres',   $nouveauStock));

    $racine->appendChild($mouvement);
    xmlSauvegarder($dom, XML_MOUVEMENTS);

    // 4. Détecter si alerte
    $enAlerte = ($nouveauStock <= (int)$article['seuil_alerte']);

    return [
        'id_mouvement'  => $newId,
        'nouveau_stock' => $nouveauStock,
        'alerte'        => $enAlerte
    ];
}

// Lister tous les mouvements — retourne un tableau PHP
function mouvementsListerTous() {
    $dom        = xmlCharger(XML_MOUVEMENTS);
    $mouvements = [];
    foreach ($dom->getElementsByTagName('mouvement') as $node) {
        $mouvements[] = mouvementNodeVersTableau($node);
    }
    // Trier du plus récent au plus ancien
    usort($mouvements, fn($a, $b) => strcmp($b['date_heure'], $a['date_heure']));
    return $mouvements;
}

// Lister les mouvements d'un article précis
function mouvementsParArticle($idArticle) {
    $tous = mouvementsListerTous();
    return array_filter($tous, fn($m) => $m['id_article'] === $idArticle);
}

// Lister les mouvements entre deux dates
function mouvementsParPeriode($dateDebut, $dateFin) {
    $tous = mouvementsListerTous();
    return array_filter($tous, function($m) use ($dateDebut, $dateFin) {
        $date = substr($m['date_heure'], 0, 10);
        return $date >= $dateDebut && $date <= $dateFin;
    });
}

// ─── Helper privé mouvements ───
function mouvementNodeVersTableau(DOMElement $node) {
    return [
        'id'             => $node->getAttribute('id'),
        'type'           => $node->getElementsByTagName('type')->item(0)->nodeValue,
        'id_article'     => $node->getElementsByTagName('id_article')->item(0)->nodeValue,
        'quantite'       => $node->getElementsByTagName('quantite')->item(0)->nodeValue,
        'motif'          => $node->getElementsByTagName('motif')->item(0)->nodeValue,
        'date_heure'     => $node->getElementsByTagName('date_heure')->item(0)->nodeValue,
        'id_utilisateur' => $node->getElementsByTagName('id_utilisateur')->item(0)->nodeValue,
        'stock_apres'    => $node->getElementsByTagName('stock_apres')->item(0)->nodeValue,
    ];
}

// ============================================================
//  ─────────────────────────────────────────────────────────
//  FONCTIONS UTILISATEURS
//  ─────────────────────────────────────────────────────────
// ============================================================

// Trouver un utilisateur par login (pour l'authentification)
function utilisateurParLogin($login) {
    $dom = xmlCharger(XML_UTILISATEURS);
    foreach ($dom->getElementsByTagName('utilisateur') as $node) {
        if ($node->getElementsByTagName('login')->item(0)->nodeValue === $login) {
            return utilisateurNodeVersTableau($node);
        }
    }
    return null;
}

// Lister tous les utilisateurs
function utilisateursListerTous() {
    $dom          = xmlCharger(XML_UTILISATEURS);
    $utilisateurs = [];
    foreach ($dom->getElementsByTagName('utilisateur') as $node) {
        $u = utilisateurNodeVersTableau($node);
        unset($u['password_hash']); // ne jamais exposer le hash
        $utilisateurs[] = $u;
    }
    return $utilisateurs;
}

// Ajouter un utilisateur
function utilisateurAjouter($donnees, $motDePasse) {
    $dom    = xmlCharger(XML_UTILISATEURS);
    $racine = $dom->documentElement;
    $newId  = xmlGenererID(PREFIX_UTILISATEUR, $dom, 'utilisateur');

    $user = $dom->createElement('utilisateur');
    $user->setAttribute('id', $newId);
    $user->appendChild(xmlCreerElement($dom, 'nom',           nettoyerTexte($donnees['nom'])));
    $user->appendChild(xmlCreerElement($dom, 'login',         nettoyerTexte($donnees['login'])));
    $user->appendChild(xmlCreerElement($dom, 'password_hash', password_hash($motDePasse, PASSWORD_DEFAULT)));
    $user->appendChild(xmlCreerElement($dom, 'role',          $donnees['role']));
    $user->appendChild(xmlCreerElement($dom, 'statut',        'actif'));

    $racine->appendChild($user);
    xmlSauvegarder($dom, XML_UTILISATEURS);
    return $newId;
}

// Modifier le rôle d'un utilisateur
function utilisateurModifierRole($id, $nouveauRole) {
    $dom  = xmlCharger(XML_UTILISATEURS);
    $node = utilisateurTrouverNode($dom, $id);
    if (!$node) throw new Exception("Utilisateur introuvable : $id");

    $node->getElementsByTagName('role')->item(0)->nodeValue = $nouveauRole;
    xmlSauvegarder($dom, XML_UTILISATEURS);
    return true;
}

// Désactiver un utilisateur
function utilisateurDesactiver($id) {
    $dom  = xmlCharger(XML_UTILISATEURS);
    $node = utilisateurTrouverNode($dom, $id);
    if (!$node) throw new Exception("Utilisateur introuvable : $id");

    $node->getElementsByTagName('statut')->item(0)->nodeValue = 'inactif';
    xmlSauvegarder($dom, XML_UTILISATEURS);
    return true;
}

// ─── Helpers privés utilisateurs ───
function utilisateurTrouverNode(DOMDocument $dom, $id) {
    foreach ($dom->getElementsByTagName('utilisateur') as $node) {
        if ($node->getAttribute('id') === $id) return $node;
    }
    return null;
}

function utilisateurNodeVersTableau(DOMElement $node) {
    return [
        'id'            => $node->getAttribute('id'),
        'nom'           => $node->getElementsByTagName('nom')->item(0)->nodeValue,
        'login'         => $node->getElementsByTagName('login')->item(0)->nodeValue,
        'password_hash' => $node->getElementsByTagName('password_hash')->item(0)->nodeValue,
        'role'          => $node->getElementsByTagName('role')->item(0)->nodeValue,
        'statut'        => $node->getElementsByTagName('statut')->item(0)->nodeValue,
    ];
}

// ============================================================
//  ─────────────────────────────────────────────────────────
//  FONCTIONS STATISTIQUES
//  ─────────────────────────────────────────────────────────
// ============================================================

// Répartition des articles par catégorie (pour camembert)
function statsRepartitionCategories() {
    $articles = articlesListerTous();
    $repartition = [];
    foreach ($articles as $a) {
        if ($a['statut'] !== 'actif') continue;
        $cat = $a['categorie'];
        $repartition[$cat] = ($repartition[$cat] ?? 0) + 1;
    }
    return $repartition;
}

// Valeur totale du stock
function statsValeurStockTotal() {
    $articles = articlesListerTous();
    $total    = 0;
    foreach ($articles as $a) {
        if ($a['statut'] !== 'actif') continue;
        $total += (float)$a['prix_unitaire'] * (int)$a['quantite_stock'];
    }
    return round($total, 2);
}

// Top 5 articles les plus mouvementés
function statsTopArticles($limite = 5) {
    $mouvements = mouvementsListerTous();
    $compteur   = [];
    foreach ($mouvements as $m) {
        $id = $m['id_article'];
        $compteur[$id] = ($compteur[$id] ?? 0) + 1;
    }
    arsort($compteur);
    $top = array_slice($compteur, 0, $limite, true);

    // Enrichir avec le nom de l'article
    $resultat = [];
    foreach ($top as $id => $nb) {
        $article = articleLireParId($id);
        $resultat[] = [
            'id_article'       => $id,
            'nom'              => $article ? $article['nom'] : 'Article supprimé',
            'nb_mouvements'    => $nb
        ];
    }
    return $resultat;
}

// Évolution du stock sur les 30 derniers jours (pour courbe)
function statsEvolutionStock($idArticle) {
    $mouvements = mouvementsParArticle($idArticle);
    $evolution  = [];
    foreach ($mouvements as $m) {
        $date = substr($m['date_heure'], 0, 10);
        $evolution[$date] = (int)$m['stock_apres'];
    }
    ksort($evolution);
    return $evolution;
}