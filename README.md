# Gestion Stock Pro

Application web de gestion de stock commerciale d'une entreprise — Informatique/Électronique.

**Stack technique** : PHP 7+ | XML | AJAX | JavaScript | HTML5 | CSS3

---

## 📋 Vue d'ensemble

**Gestion Stock Pro** est une application PHP permettant à une entreprise commerciale de gérer son inventaire d'articles en temps réel. Elle offre le suivi des entrées/sorties de stock, des alertes automatiques, un tableau de bord intuitif et des statistiques détaillées.

**Durée de développement** : 1 semaine | **Équipe** : 4 personnes

---

## ✨ Fonctionnalités

### Authentification
- Login / logout sécurisé
- Gestion des sessions (1h d'expiration)
- 3 rôles d'accès : Admin, Gestionnaire, Consultant

### Gestion des Articles
- ✅ Ajouter / Modifier / Supprimer / Archiver des articles
- ✅ Recherche et filtres par catégorie
- ✅ Vue détaillée avec historique des mouvements
- ✅ 8 catégories prédéfinies

### Mouvements de Stock
- ✅ Entrées de stock (achats, retours)
- ✅ Sorties de stock (ventes, pertes)
- ✅ Vérification automatique des seuils d'alerte
- ✅ Alertes visuelles quand le stock passe sous le seuil
- ✅ Historique complet et filtrable

### Tableau de Bord
- 4 indicateurs clés : Nb articles | Valeur stock | Articles en alerte | Derniers mouvements

### Statistiques
- 📊 Camembert : répartition des articles par catégorie
- 📈 Courbe : évolution du stock dans le temps
- 🏆 Classement : articles les plus mouvementés

### Administration (Admin seulement)
- Créer / Modifier / Désactiver des utilisateurs
- Assigner des rôles (Admin / Gestionnaire / Consultant)
- Gérer les droits d'accès

---

## 👥 Rôles et Droits

| Droit | Admin | Gestionnaire | Consultant |
|-------|:-----:|:------------:|:----------:|
| Voir tableau de bord | ✅ | ✅ | ✅ |
| Voir articles & historique | ✅ | ✅ | ✅ |
| Voir statistiques | ✅ | ✅ | ✅ |
| Ajouter / modifier articles | ✅ | ✅ | ❌ |
| Entrées / sorties de stock | ✅ | ✅ | ❌ |
| Supprimer articles | ✅ | ❌ | ❌ |
| Gérer utilisateurs & droits | ✅ | ❌ | ❌ |
| Accès panneau admin | ✅ | ❌ | ❌ |

---

## 📁 Structure du Projet

```
gestion-stock/
│
├── index.php                          # Point d'entrée principal
├── login.php                          # Page de connexion
├── logout.php                         # Déconnexion
├── .htaccess                          # Sécurité : bloque accès direct à data/
│
├── assets/                            # Fichiers statiques côté client
│   ├── css/
│   │   └── style.css                  # Styles globaux (Membre 2 : CSS)
│   └── js/
│       ├── utils.js                   # Fonctions AJAX génériques
│       ├── articles.js                # AJAX CRUD articles (Membre 3 : DOM/JS)
│       ├── mouvements.js              # AJAX mouvements (Membre 3 : DOM/JS)
│       ├── statistiques.js            # Graphiques Chart.js (Membre 3 : DOM/JS)
│       └── admin.js                   # Gestion utilisateurs (Membre 3 : DOM/JS)
│
├── controllers/                       # Reçoivent requêtes AJAX, répondent JSON
│   ├── authController.php             # Login / logout (Toi : Backend)
│   ├── articleController.php          # CRUD articles (Toi : Backend)
│   ├── mouvementController.php        # Entrées / sorties (Toi : Backend)
│   ├── dashboardController.php        # Calcul indicateurs (Membre 4 : Backend)
│   ├── statistiquesController.php     # Calcul statistiques (Toi : Backend)
│   └── adminController.php            # Gestion utilisateurs (Membre 4 : Backend)
│
├── includes/                          # Code PHP réutilisable (moteur interne)
│   ├── config.php                     # Chemins, constantes, catégories
│   ├── xml-manager.php                # Lecture/écriture XML (verrouillage)
│   ├── session.php                    # Démarrage session + vérification rôles
│   └── validators.php                 # Nettoyage + validation + reponseJson()
│
├── views/                             # Fragments HTML générés serveur
│   ├── layout/
│   │   ├── header.php                 # Navbar + vérification session
│   │   ├── sidebar.php                # Menu navigation par rôle
│   │   └── footer.php                 # Pied de page
│   ├── dashboard.php                  # Tableau de bord (Membre 2 : CSS)
│   ├── statistiques.php               # Vue statistiques (Membre 2 : CSS)
│   ├── alertes.php                    # Articles en alerte (Membre 2 : CSS)
│   ├── articles/
│   │   ├── liste.php                  # Tableau articles (Membre 2 : CSS)
│   │   ├── formulaire.php             # Ajout/modification (Membre 2 : CSS)
│   │   └── fiche.php                  # Détail article + historique (Membre 2 : CSS)
│   ├── mouvements/
│   │   ├── entree.php                 # Formulaire entrée (Membre 2 : CSS)
│   │   ├── sortie.php                 # Formulaire sortie (Membre 2 : CSS)
│   │   └── historique.php             # Historique mouvements (Membre 2 : CSS)
│   └── admin/
│       ├── utilisateurs.php           # Liste utilisateurs (Membre 2 : CSS)
│       └── formulaire_user.php        # Ajout/modification user (Membre 2 : CSS)
│
└── data/                              # Fichiers XML (base de données)
    ├── articles.xml                   # Catalogue articles
    ├── mouvements.xml                 # Historique mouvements
    └── utilisateurs.xml               # Comptes utilisateurs (hashés)
```

---

## 🚀 Installation

### Prérequis
- PHP 7.0+
- XAMPP (Apache + PHP)
- Git

### Étapes
```bash
# 1. Cloner le dépôt
git clone https://github.com/overhall93-lab/gestion-stock.git
cd gestion-stock

# 2. Démarrer Apache dans XAMPP
# (XAMPP Control Panel → Apache : Start)

# 3. Accéder à l'application
http://localhost/gestion-stock/

# 4. Connexion par défaut
Login    : admin
Password : (défini par l'admin en XML)
```

---

## 📊 Variables Clés du Projet

### Fichier XML : utilisateurs.xml
```xml
<utilisateur id="U001">
    <nom>Administrateur</nom>
    <login>admin</login>
    <password_hash>...</password_hash>
    <role>admin</role>
    <statut>actif</statut>
</utilisateur>
```

### Fichier XML : articles.xml
```xml
<article id="ART001">
    <nom>Clavier Mecanique</nom>
    <categorie>Peripheriques</categorie>
    <prix_unitaire>89.99</prix_unitaire>
    <quantite_stock>25</quantite_stock>
    <seuil_alerte>5</seuil_alerte>
    <date_ajout>2024-01-15</date_ajout>
    <statut>actif</statut>
</article>
```

### Fichier XML : mouvements.xml
```xml
<mouvement id="MOV001">
    <type>entree</type>
    <id_article>ART001</id_article>
    <quantite>25</quantite>
    <motif>Stock initial</motif>
    <date_heure>2024-01-15 09:00:00</date_heure>
    <id_utilisateur>U001</id_utilisateur>
    <stock_apres>25</stock_apres>
</mouvement>
```

### Variables Session PHP
```php
$_SESSION['id_utilisateur']   // Ex: U001
$_SESSION['role']             // admin / gestionnaire / consultant
$_SESSION['nom']              // Nom de l'utilisateur
$_SESSION['connecte']         // true / false
```

### Réponse AJAX Standard (PHP → JavaScript)
```json
{
    "succes": true,
    "message": "Opération réalisée",
    "data": {...},
    "nouveau_stock": 25,
    "alerte": false
}
```

---

## 👨‍💼 Répartition de l'Équipe

### **Toi — Chef Technique / Backend Principal**
**Domaine** : PHP, XML, AJAX, Architecture serveur

**Responsabilités** :
- ✅ Architecture et configuration complète du projet
- ✅ Implémentation de `xml-manager.php` (moteur XML central)
- ✅ Controllers : authController, articleController, mouvementController, statistiquesController
- ✅ Vérification de sécurité : injection XML, accès non autorisés, validation données
- ✅ Communication AJAX : structure JSON, gestion erreurs
- ✅ Supervision et débogage global

**Fichiers** : config.php, session.php, validators.php, xml-manager.php, authController.php, articleController.php, mouvementController.php, statistiquesController.php, index.php, login.php

---

### **Membre 2 — Designer/CSS**
**Domaine** : HTML, CSS, UX/UI

**Responsabilités** :
- ✅ Customiser et styliser toutes les vues HTML
- ✅ Mise en page responsive
- ✅ Design cohérent dans toute l'application
- ✅ Accessibilité (contraste, lisibilité)

**Fichiers à modifier** : Tous les fichiers `.php` dans `views/` + `assets/css/style.css`

**Variables que tu manipuleras** :
```html
<!-- Classes CSS pour styliser -->
class="btn btn-primary"
class="alert alert-danger"
class="card widget"
class="table-responsive"

<!-- Variables PHP transmises pour afficher -->
<?php echo $article['nom'] ?>
<?php echo $utilisateur['role'] ?>
```

---

### **Membre 3 — Frontend/DOM JavaScript**
**Domaine** : JavaScript, AJAX, Interactions dynamiques

**Responsabilités** :
- ✅ Implémenter tous les fichiers `.js` dans `assets/js/`
- ✅ Gestion AJAX : envoi requêtes, traitement réponses
- ✅ DOM manipulation : affichage/masquage, animation alertes
- ✅ Graphiques statistiques (Chart.js)
- ✅ Validation formulaires côté client

**Fichiers à développer** : articles.js, mouvements.js, statistiques.js, admin.js, utils.js

**Variables que tu manipuleras** :
```javascript
// Envois AJAX au serveur
{
    action: "lister",
    id_article: "ART001",
    quantite: 5,
    motif: "Vente client"
}

// Réponses reçues du serveur
{
    succes: true,
    message: "Stock modifié",
    nouveau_stock: 20,
    alerte: true
}

// Manipulation du DOM
document.getElementById('liste-articles')
document.querySelector('.alert-stock')
document.getElementById('tableau-mouvements')
```

---

### **Membre 4 — Backend Assistant**
**Domaine** : PHP, contrôle d'accès, indicateurs

**Responsabilités** :
- ✅ Développer dashboardController et adminController
- ✅ Calculs des indicateurs du tableau de bord
- ✅ Gestion des utilisateurs (créer, modifier rôles, désactiver)
- ✅ Vérification des droits d'accès par rôle

**Fichiers à développer** : dashboardController.php, adminController.php

**Variables que tu manipuleras** :
```php
// Indicateurs à calculer et renvoyer
$nombre_articles_total
$valeur_stock_total
$nombre_articles_en_alerte
$derniers_mouvements[]

// Gestion utilisateurs
$id_utilisateur
$role (admin / gestionnaire / consultant)
$statut (actif / inactif)
```

---

## 📅 Calendrier de Développement

| Jour | Phase | Responsable | Livrables |
|------|-------|-------------|-----------|
| 1 | Fondations | Toi | xml-manager.php ✅, config.php ✅, session.php ✅ |
| 2 | CRUD Articles | Toi | articleController.php ✅ |
| 3 | Mouvements | Toi | mouvementController.php ✅ |
| 4 | AJAX | Membre 3 | articles.js ✅, mouvements.js ✅ |
| 5 | Tableaux & Graphiques | Toi + Membre 4 | statistiquesController.php, statistiques.js ✅ |
| 6 | Design & CSS | Membre 2 | style.css ✅, toutes vues stylisées |
| 7 | Tests & Finitions | Tout | Debug, données démo, prêt pour présentation |

---

## 🔒 Sécurité

- ✅ Données XML inaccessibles directement (`.htaccess`)
- ✅ Mots de passe hashés avec `password_hash()`
- ✅ Sessions PHP avec expiration 1h
- ✅ Vérification rôles à chaque requête AJAX
- ✅ Validation et échappement toutes entrées utilisateur
- ✅ Verrouillage fichiers XML en cas d'écritures concurrentes

---

## 🐛 Débogage

### Test de connexion
```
URL: http://localhost/gestion-stock/login.php
User: admin
Pass: (voir dans data/utilisateurs.xml)
```

### Vérifier les erreurs PHP
Regarder dans la console du navigateur (F12 → Network) ou les logs Apache.

### Logs XML
Les fichiers dans `data/` sont lisibles en texte — ouvrir avec un éditeur texte pour vérifier la structure.

---

## 📞 Support

Pour les questions de l'équipe, faire référence à :
- **Backend** → Voir documentations et commentaires dans les controllers
- **Frontend** → Voir les appels AJAX dans articles.js, mouvements.js
- **Design** → Voir les classes CSS dans style.css

---

**Créé par** : Équipe Gestion Stock | **Durée** : 1 semaine | **2024**
