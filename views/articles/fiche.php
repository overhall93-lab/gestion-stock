<?php
// ============================================================
//  views/articles/fiche.php
//  Détail d'un article + historique de mouvements
// ============================================================

$id = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($id)) {
    header('Location: index.php?vue=articles/liste');
    exit;
}
?>

<div class="page-header">
    <h2>Détail Article</h2>
    <a href="index.php?vue=articles/liste" class="btn btn-secondary">← Retour à la liste</a>
</div>

<!-- ── FICHE ARTICLE ── -->
<div class="section-card">
    <div id="fiche-article" class="fiche-article">
        <p class="chargement">Chargement de l'article...</p>
    </div>
</div>

<!-- ── HISTORIQUE MOUVEMENTS ── -->
<div class="section-card" style="margin-top: 30px;">
    <h3>Historique des mouvements</h3>
    <div id="fiche-historique" class="historique-mouvements">
        <p class="chargement">Chargement de l'historique...</p>
    </div>
</div>

<!-- ── MESSAGE RETOUR ── -->
<div id="message-fiche" class="message hidden"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    initialiserFiche('<?= htmlspecialchars($id) ?>');
});

function initialiserFiche(articleId) {
    // Charger la fiche article + historique
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
        afficherFiche(data.data);
        afficherFicheHistorique(articleId);
    })
    .catch(err => {
        document.getElementById('fiche-article').innerHTML =
            '<p class="erreur">Erreur de chargement.</p>';
    });
}

function afficherFiche(article) {
    const stockBas = parseInt(article.quantite_stock) <= parseInt(article.seuil_alerte);
    const stockClass = stockBas ? 'alerte-stock' : '';

    let html = `
        <div class="fiche-content">
            <div class="fiche-row">
                <label>ID Article :</label>
                <span>${article.id}</span>
            </div>
            <div class="fiche-row">
                <label>Nom :</label>
                <span>${article.nom}</span>
            </div>
            <div class="fiche-row">
                <label>Catégorie :</label>
                <span>${article.categorie}</span>
            </div>
            <div class="fiche-row">
                <label>Prix unitaire :</label>
                <span>${parseFloat(article.prix_unitaire).toFixed(2)} FCFA</span>
            </div>
            <div class="fiche-row">
                <label>Stock actuel :</label>
                <span class="${stockClass}">${article.quantite_stock} ${stockBas ? '⚠️ ALERTE' : ''}</span>
            </div>
            <div class="fiche-row">
                <label>Seuil d'alerte :</label>
                <span>${article.seuil_alerte}</span>
            </div>
            <div class="fiche-row">
                <label>Valeur du stock :</label>
                <span>${(parseFloat(article.quantite_stock) * parseFloat(article.prix_unitaire)).toFixed(2)} FCFA</span>
            </div>
            <div class="fiche-row">
                <label>Statut :</label>
                <span><span class="badge badge-${article.statut}">${article.statut}</span></span>
            </div>
            <div class="fiche-row">
                <label>Date d'ajout :</label>
                <span>${article.date_ajout}</span>
            </div>
    `;

    if (<?php echo estGestionnaire() ? 'true' : 'false'; ?>) {
        html += `
            <div class="fiche-actions">
                <a href="index.php?vue=articles/formulaire&id=${article.id}" class="btn btn-warning">
                    ✏️ Modifier l'article
                </a>
        `;

        if (<?php echo estGestionnaire() ? 'true' : 'false'; ?>) {
            html += `
                <a href="index.php?vue=mouvements/entree&id=${article.id}" class="btn btn-success">
                    ⬆️ Ajouter au stock
                </a>
                <a href="index.php?vue=mouvements/sortie&id=${article.id}" class="btn btn-warning">
                    ⬇️ Retirer du stock
                </a>
            `;
        }

        html += `</div>`;
    }

    html += `</div>`;
    document.getElementById('fiche-article').innerHTML = html;
}

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
                '<p class="vide">Aucun mouvement enregistré.</p>';
            return;
        }

        let html = `<table class="tableau tableau-historique">
            <thead><tr>
                <th>ID Mouvement</th><th>Type</th><th>Quantité</th>
                <th>Motif</th><th>Date/Heure</th><th>Stock après</th>
            </tr></thead><tbody>`;

        data.data.forEach(mv => {
            const typeClass = mv.type === 'entree' ? 'entree' : 'sortie';
            const typeIcon = mv.type === 'entree' ? '⬆️' : '⬇️';

            html += `<tr class="mouvement-${typeClass}">
                <td>${mv.id}</td>
                <td><span class="badge badge-${typeClass}">${typeIcon} ${mv.type}</span></td>
                <td>${mv.quantite}</td>
                <td>${mv.motif}</td>
                <td>${mv.date_heure}</td>
                <td>${mv.stock_apres}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('fiche-historique').innerHTML = html;
    })
    .catch(() => {
        document.getElementById('fiche-historique').innerHTML =
            '<p class="erreur">Erreur de chargement de l\'historique.</p>';
    });
}
</script>

<style>
.fiche-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.fiche-row {
    display: flex;
    gap: 20px;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.fiche-row label {
    font-weight: bold;
    min-width: 150px;
    color: #4F86C6;
}

.fiche-row span {
    flex: 1;
}

.fiche-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.alerte-stock {
    color: #e74c3c;
    font-weight: bold;
}

.tableau-historique {
    font-size: 13px;
}

.mouvement-entree {
    background: #d5f4e6;
}

.mouvement-sortie {
    background: #fadbd8;
}
</style>