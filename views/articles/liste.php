<div class="page-header">
    <h2>Articles</h2>
    <?php if (estGestionnaire()): ?>
    <a href="index.php?vue=articles/formulaire" class="btn btn-primary">+ Ajouter un article</a>
    <?php endif; ?>
</div>

<!-- ── BARRE DE RECHERCHE ET FILTRES ── -->
<div class="barre-filtres">
    <input type="text" id="recherche-article" placeholder="Rechercher par nom ou catégorie..."
           class="input-recherche">
    <select id="filtre-categorie" class="select-filtre">
        <option value="">Toutes les catégories</option>
        <?php foreach (CATEGORIES as $cat): ?>
        <option value="<?= $cat ?>"><?= $cat ?></option>
        <?php endforeach; ?>
    </select>
    <select id="filtre-statut" class="select-filtre">
        <option value="actif">Actifs seulement</option>
        <option value="archive">Archivés</option>
        <option value="">Tous</option>
    </select>
</div>

<!-- ── MESSAGE RETOUR ── -->
<div id="message-articles" class="message hidden"></div>

<!-- ── TABLEAU DES ARTICLES ── -->
<div class="section-card">
    <div id="tableau-articles">
        <p class="chargement">Chargement des articles...</p>
    </div>
</div>

<script>
let tousLesArticles = [];

document.addEventListener('DOMContentLoaded', function () {
    chargerArticles();

    // Recherche en temps réel — filtre côté client
    document.getElementById('recherche-article').addEventListener('input', filtrerArticles);
    document.getElementById('filtre-categorie').addEventListener('change', filtrerArticles);
    document.getElementById('filtre-statut').addEventListener('change', filtrerArticles);
});

function chargerArticles() {
    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'lister' })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) {
            document.getElementById('tableau-articles').innerHTML =
                '<p class="erreur">' + data.message + '</p>';
            return;
        }
        tousLesArticles = data.data;
        filtrerArticles();
    })
    .catch(() => {
        document.getElementById('tableau-articles').innerHTML =
            '<p class="erreur">Erreur de chargement.</p>';
    });
}

function filtrerArticles() {
    const terme    = document.getElementById('recherche-article').value.toLowerCase();
    const categorie= document.getElementById('filtre-categorie').value;
    const statut   = document.getElementById('filtre-statut').value;

    const filtres = tousLesArticles.filter(a => {
        const matchTerme    = !terme    || a.nom.toLowerCase().includes(terme) || a.categorie.toLowerCase().includes(terme);
        const matchCategorie= !categorie|| a.categorie === categorie;
        const matchStatut   = !statut   || a.statut === statut;
        return matchTerme && matchCategorie && matchStatut;
    });

    afficherTableauArticles(filtres);
}

function afficherTableauArticles(articles) {
    const zone = document.getElementById('tableau-articles');

    if (articles.length === 0) {
        zone.innerHTML = '<p class="vide">Aucun article trouvé.</p>';
        return;
    }

    let html = `<table class="tableau">
        <thead><tr>
            <th>ID</th><th>Nom</th><th>Catégorie</th>
            <th>Prix</th><th>Stock</th><th>Seuil</th><th>Statut</th><th>Actions</th>
        </tr></thead><tbody>`;

    articles.forEach(a => {
        const stockBas = parseInt(a.quantite_stock) <= parseInt(a.seuil_alerte);
        const stockClass = stockBas ? 'stock-bas' : '';

        html += `<tr>
            <td>${a.id}</td>
            <td>${a.nom}</td>
            <td>${a.categorie}</td>
            <td>${parseFloat(a.prix_unitaire).toFixed(2)} FCFA</td>
            <td class="${stockClass}">${a.quantite_stock} ${stockBas ? '⚠️' : ''}</td>
            <td>${a.seuil_alerte}</td>
            <td><span class="badge badge-${a.statut}">${a.statut}</span></td>
            <td class="actions">
                <a href="index.php?vue=articles/fiche&id=${a.id}"
                   class="btn btn-sm btn-info">Voir</a>
                <?php if (estGestionnaire()): ?>
                <a href="index.php?vue=articles/formulaire&id=${a.id}"
                   class="btn btn-sm btn-warning">Modifier</a>
                <?php endif; ?>
                <?php if (estAdmin()): ?>
                <button class="btn btn-sm btn-danger"
                        onclick="supprimerArticle('${a.id}', '${a.nom}')">Supprimer</button>
                <?php endif; ?>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    zone.innerHTML = html;
}

<?php if (estAdmin()): ?>
function supprimerArticle(id, nom) {
    if (!confirm('Supprimer définitivement "' + nom + '" ? Cette action est irréversible.')) return;

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
            msg.className   = 'message succes';
            chargerArticles();
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className   = 'message erreur';
        }
    });
}
<?php endif; ?>
</script>