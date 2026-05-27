<?php
// ============================================================
//  views/articles/liste.php — Liste des articles
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/session.php';

verifierConnexion();

$titrePage = 'Articles';
include __DIR__ . '/../layout/header.php';
?>

<div class="toolbar">
    <?php if (estGestionnaire()): ?>
    <a href="<?= $racineUrl ?>views/articles/formulaire.php" class="btn btn-primary">
        + Nouvel article
    </a>
    <?php endif; ?>
    <div class="search-box">
        <input type="text" id="champ-recherche" placeholder="Rechercher par nom ou catégorie..."
               oninput="filtrerArticles(this.value)">
    </div>
    <select id="filtre-categorie" onchange="filtrerArticles()">
        <option value="">Toutes les catégories</option>
        <?php foreach (CATEGORIES as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="filtre-statut" onchange="filtrerArticles()">
        <option value="actif">Actifs uniquement</option>
        <option value="">Tous</option>
        <option value="archive">Archivés</option>
    </select>
</div>

<div id="zone-message"></div>

<div class="table-responsive">
    <table class="table table-striped" id="table-articles">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Prix unitaire</th>
                <th>Stock</th>
                <th>Seuil alerte</th>
                <th>Date ajout</th>
                <th>Statut</th>
                <?php if (estGestionnaire()): ?>
                <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody id="tbody-articles">
            <tr><td colspan="9" class="text-center">Chargement...</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal confirmation suppression -->
<div class="modal" id="modal-suppression" style="display:none;">
    <div class="modal-content">
        <h3>Confirmer la suppression</h3>
        <p>Cette action est <strong>irréversible</strong>. Supprimer cet article définitivement ?</p>
        <div class="modal-actions">
            <button class="btn btn-danger" id="btn-confirmer-suppression">Supprimer</button>
            <button class="btn btn-secondary" onclick="fermerModal()">Annuler</button>
        </div>
    </div>
</div>

<script src="<?= $assetsPath ?>js/utils.js"></script>
<script src="<?= $assetsPath ?>js/articles.js"></script>
<script>
let tousLesArticles = [];
let idASupprimer    = null;

document.addEventListener('DOMContentLoaded', function() {
    chargerArticles();
});

function chargerArticles() {
    ajaxPost(RACINE_URL + 'controllers/articleController.php', { action: 'lister' })
    .then(data => {
        if (!data.succes) { afficherErreur(data.message); return; }
        tousLesArticles = data.data || [];
        afficherArticles(tousLesArticles);
    })
    .catch(() => afficherErreur('Impossible de charger les articles.'));
}

function afficherArticles(articles) {
    const statut    = document.getElementById('filtre-statut').value;
    const categorie = document.getElementById('filtre-categorie').value;
    const terme     = document.getElementById('champ-recherche').value.toLowerCase();

    let filtres = articles;
    if (statut)    filtres = filtres.filter(a => a.statut === statut);
    if (categorie) filtres = filtres.filter(a => a.categorie === categorie);
    if (terme)     filtres = filtres.filter(a =>
        a.nom.toLowerCase().includes(terme) || a.categorie.toLowerCase().includes(terme)
    );

    const tbody = document.getElementById('tbody-articles');
    if (filtres.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">Aucun article trouvé.</td></tr>';
        return;
    }

    const estGest = <?= estGestionnaire() ? 'true' : 'false' ?>;
    const estAdm  = <?= estAdmin() ? 'true' : 'false' ?>;

    tbody.innerHTML = filtres.map(a => {
        const enAlerte = parseInt(a.quantite_stock) <= parseInt(a.seuil_alerte);
        const stockClass = enAlerte ? 'stock-alerte' : '';
        let actions = '';
        if (estGest) {
            actions = `
                <a href="${RACINE_URL}views/articles/formulaire.php?id=${a.id}" class="btn btn-sm btn-secondary">Modifier</a>
                <button class="btn btn-sm btn-warning" onclick="archiverArticle('${a.id}')">Archiver</button>
            `;
        }
        if (estAdm && a.statut === 'archive') {
            actions += `<button class="btn btn-sm btn-danger" onclick="demanderSuppression('${a.id}')">Supprimer</button>`;
        }
        return `
        <tr class="${a.statut === 'archive' ? 'row-archive' : ''}">
            <td><small>${echapper(a.id)}</small></td>
            <td><strong>${echapper(a.nom)}</strong></td>
            <td>${echapper(a.categorie)}</td>
            <td>${parseFloat(a.prix_unitaire).toFixed(2)} €</td>
            <td class="${stockClass}">${a.quantite_stock}</td>
            <td>${a.seuil_alerte}</td>
            <td>${formaterDate(a.date_ajout, false)}</td>
            <td><span class="badge badge-${a.statut}">${a.statut}</span></td>
            ${estGest ? `<td class="actions">${actions}</td>` : ''}
        </tr>`;
    }).join('');
}

function filtrerArticles() {
    afficherArticles(tousLesArticles);
}

function archiverArticle(id) {
    if (!confirm('Archiver cet article ?')) return;
    ajaxPost(RACINE_URL + 'controllers/articleController.php', { action: 'archiver', id })
    .then(data => {
        afficherMessage(data.succes, data.message, 'zone-message');
        if (data.succes) chargerArticles();
    });
}

function demanderSuppression(id) {
    idASupprimer = id;
    document.getElementById('modal-suppression').style.display = 'flex';
    document.getElementById('btn-confirmer-suppression').onclick = function() {
        supprimerArticle(idASupprimer);
        fermerModal();
    };
}

function supprimerArticle(id) {
    ajaxPost(RACINE_URL + 'controllers/articleController.php', { action: 'supprimer', id })
    .then(data => {
        afficherMessage(data.succes, data.message, 'zone-message');
        if (data.succes) chargerArticles();
    });
}

function fermerModal() {
    document.getElementById('modal-suppression').style.display = 'none';
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>