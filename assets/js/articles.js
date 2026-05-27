// ============================================================
//  assets/js/articles.js
//  Gestion AJAX des articles (CRUD, recherche, fiche)
// ============================================================

// ── INITIALISER FICHE ARTICLE ──
// Appelée au chargement de views/articles/fiche.php
function initialiserFiche(articleId) {
    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'lire', id: articleId })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) {
            document.getElementById('fiche-article').innerHTML =
                '<p class="erreur">' + data.message + '</p>';
            return;
        }
        afficherFicheArticle(data.data);
        afficherFicheHistorique(articleId);
    })
    .catch(err => {
        document.getElementById('fiche-article').innerHTML =
            '<p class="erreur">Erreur de chargement.</p>';
        console.error('Erreur initialiserFiche:', err);
    });
}

// ── AFFICHER LES DÉTAILS DE LA FICHE ──
function afficherFicheArticle(article) {
    const stockBas = parseInt(article.quantite_stock) <= parseInt(article.seuil_alerte);
    const stockClass = stockBas ? 'alerte-stock' : '';
    const valeurStock = (parseFloat(article.quantite_stock) * parseFloat(article.prix_unitaire)).toFixed(2);

    let html = `
        <div class="fiche-content">
            <div class="fiche-row">
                <label>ID Article :</label>
                <span id="fiche-id">${article.id}</span>
            </div>
            <div class="fiche-row">
                <label>Nom :</label>
                <span id="fiche-nom">${article.nom}</span>
            </div>
            <div class="fiche-row">
                <label>Catégorie :</label>
                <span id="fiche-categorie">${article.categorie}</span>
            </div>
            <div class="fiche-row">
                <label>Prix unitaire :</label>
                <span id="fiche-prix">${parseFloat(article.prix_unitaire).toFixed(2)} FCFA</span>
            </div>
            <div class="fiche-row">
                <label>Stock actuel :</label>
                <span id="fiche-stock" class="${stockClass}">
                    ${article.quantite_stock} ${stockBas ? '⚠️ ALERTE' : ''}
                </span>
            </div>
            <div class="fiche-row">
                <label>Seuil d'alerte :</label>
                <span id="fiche-seuil">${article.seuil_alerte}</span>
            </div>
            <div class="fiche-row">
                <label>Valeur du stock :</label>
                <span id="fiche-valeur">${valeurStock} FCFA</span>
            </div>
            <div class="fiche-row">
                <label>Statut :</label>
                <span id="fiche-statut">
                    <span class="badge badge-${article.statut}">${article.statut}</span>
                </span>
            </div>
            <div class="fiche-row">
                <label>Date d'ajout :</label>
                <span id="fiche-date">${article.date_ajout}</span>
            </div>
            <div class="fiche-actions">
                <a href="index.php?vue=articles/formulaire&id=${article.id}" class="btn btn-warning">
                    ✏️ Modifier
                </a>
                <a href="index.php?vue=mouvements/entree&id=${article.id}" class="btn btn-success">
                    ⬆️ Entrée stock
                </a>
                <a href="index.php?vue=mouvements/sortie&id=${article.id}" class="btn btn-danger">
                    ⬇️ Sortie stock
                </a>
            </div>
        </div>
    `;

    document.getElementById('fiche-article').innerHTML = html;
}

// ── AFFICHER L'HISTORIQUE DE LA FICHE ──
function afficherFicheHistorique(articleId) {
    fetch('controllers/mouvementController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'parArticle', id_article: articleId })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) {
            document.getElementById('fiche-historique').innerHTML =
                '<p class="erreur">' + data.message + '</p>';
            return;
        }

        if (!data.data || data.data.length === 0) {
            document.getElementById('fiche-historique').innerHTML =
                '<p class="vide">Aucun mouvement enregistré pour cet article.</p>';
            return;
        }

        let html = `<table class="tableau" id="tableau-fiche-historique">
            <thead><tr>
                <th>ID Mouvement</th>
                <th>Type</th>
                <th>Quantité</th>
                <th>Motif</th>
                <th>Date/Heure</th>
                <th>Stock après</th>
            </tr></thead><tbody>`;

        data.data.forEach(mv => {
            const typeClass = mv.type === 'entree' ? 'entree' : 'sortie';
            const typeIcon = mv.type === 'entree' ? '⬆️ Entrée' : '⬇️ Sortie';

            html += `<tr class="mouvement-${typeClass}">
                <td>${mv.id}</td>
                <td><span class="badge badge-${typeClass}">${typeIcon}</span></td>
                <td>${mv.quantite}</td>
                <td>${mv.motif}</td>
                <td>${mv.date_heure}</td>
                <td>${mv.stock_apres}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('fiche-historique').innerHTML = html;
    })
    .catch(err => {
        document.getElementById('fiche-historique').innerHTML =
            '<p class="erreur">Erreur de chargement de l\'historique.</p>';
        console.error('Erreur afficherFicheHistorique:', err);
    });
}

// ── AJOUTER UN ARTICLE (formulaire AJAX) ──
function ajouterArticle(formData) {
    const action = formData.id ? 'modifier' : 'ajouter';

    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ ...formData, action: action })
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('message-articles');
        if (data.succes) {
            msg.textContent = '✓ ' + data.message;
            msg.className = 'message succes';
            setTimeout(() => {
                window.location.href = 'index.php?vue=articles/liste';
            }, 1500);
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className = 'message erreur';
        }
    })
    .catch(err => {
        document.getElementById('message-articles').textContent = '✗ Erreur réseau';
        document.getElementById('message-articles').className = 'message erreur';
        console.error('Erreur ajouterArticle:', err);
    });
}

// ── SUPPRIMER UN ARTICLE ──
function supprimerArticle(id, nom) {
    if (!confirm('Supprimer définitivement "' + nom + '" ? Cette action est irréversible.')) {
        return;
    }

    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'supprimer', id: id })
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('message-articles');
        if (data.succes) {
            msg.textContent = '✓ ' + data.message;
            msg.className = 'message succes';
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className = 'message erreur';
        }
    })
    .catch(err => {
        document.getElementById('message-articles').textContent = '✗ Erreur réseau';
        document.getElementById('message-articles').className = 'message erreur';
        console.error('Erreur supprimerArticle:', err);
    });
}

// ── ARCHIVER UN ARTICLE ──
function archiverArticle(id, nom) {
    if (!confirm('Archiver "' + nom + '" ? L\'article ne sera plus visible.')) {
        return;
    }

    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'archiver', id: id })
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('message-articles');
        if (data.succes) {
            msg.textContent = '✓ ' + data.message;
            msg.className = 'message succes';
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className = 'message erreur';
        }
    })
    .catch(err => {
        document.getElementById('message-articles').textContent = '✗ Erreur réseau';
        document.getElementById('message-articles').className = 'message erreur';
    });
}

// ── RECHERCHE EN TEMPS RÉEL ──
function rechercherArticles(terme) {
    const recherche = terme.toLowerCase();
    const lignes = document.querySelectorAll('#tableau-articles tbody tr');

    lignes.forEach(ligne => {
        const texte = ligne.textContent.toLowerCase();
        ligne.style.display = texte.includes(recherche) ? '' : 'none';
    });
}