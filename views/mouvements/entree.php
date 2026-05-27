<div class="page-header">
    <h2>📥 Entrée de stock</h2>
</div>

<div id="message-entree" class="message hidden"></div>

<div class="section-card">
    <div class="form-container">

        <div class="form-group">
            <label for="id_article">Article *</label>
            <select id="id_article">
                <option value="">-- Choisir un article --</option>
            </select>
            <small id="stock-actuel-info" class="info-stock"></small>
        </div>

        <div class="form-group">
            <label for="quantite">Quantité reçue *</label>
            <input type="number" id="quantite" min="1" placeholder="0">
        </div>

        <div class="form-group">
            <label for="motif">Motif *</label>
            <input type="text" id="motif" placeholder="Ex: Achat fournisseur, Retour client...">
        </div>

        <div class="form-actions">
            <button id="btn-entree" class="btn btn-primary">Enregistrer l'entrée</button>
            <a href="index.php?vue=articles/liste" class="btn btn-secondary">Annuler</a>
        </div>

    </div>
</div>

<script>
// Charger la liste des articles dans le select
document.addEventListener('DOMContentLoaded', function () {
    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'lister' })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) return;
        const select = document.getElementById('id_article');
        data.data.filter(a => a.statut === 'actif').forEach(a => {
            const opt   = document.createElement('option');
            opt.value   = a.id;
            opt.dataset.stock = a.quantite_stock;
            opt.textContent   = a.nom + ' (stock: ' + a.quantite_stock + ')';
            select.appendChild(opt);
        });

        // Pré-sélectionner si id passé en URL
        const params = new URLSearchParams(window.location.search);
        if (params.get('id')) select.value = params.get('id');
    });
});

// Afficher le stock actuel quand on change l'article
document.getElementById('id_article').addEventListener('change', function () {
    const opt   = this.options[this.selectedIndex];
    const info  = document.getElementById('stock-actuel-info');
    info.textContent = opt.value ? 'Stock actuel : ' + opt.dataset.stock : '';
});

// Soumettre l'entrée
document.getElementById('btn-entree').addEventListener('click', function () {
    const msg = document.getElementById('message-entree');
    const btn = this;
    const donnees = {
        action    : 'entree',
        id_article: document.getElementById('id_article').value,
        quantite  : document.getElementById('quantite').value,
        motif     : document.getElementById('motif').value.trim()
    };

    if (!donnees.id_article || !donnees.quantite || !donnees.motif) {
        msg.textContent = 'Tous les champs sont obligatoires.';
        msg.className   = 'message erreur';
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Enregistrement...';

    fetch('controllers/mouvementController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify(donnees)
    })
    .then(r => r.json())
    .then(data => {
        if (data.succes) {
            msg.textContent = '✓ Entrée enregistrée. Nouveau stock : ' + data.nouveau_stock;
            msg.className   = 'message succes';
            document.getElementById('quantite').value = '';
            document.getElementById('motif').value    = '';
            // Mettre à jour l'option du select
            const opt = document.getElementById('id_article').options[
                document.getElementById('id_article').selectedIndex
            ];
            opt.dataset.stock   = data.nouveau_stock;
            opt.textContent     = opt.textContent.replace(/\(stock: \d+\)/, '(stock: ' + data.nouveau_stock + ')');
            document.getElementById('stock-actuel-info').textContent = 'Stock actuel : ' + data.nouveau_stock;
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className   = 'message erreur';
        }
        btn.disabled    = false;
        btn.textContent = "Enregistrer l'entrée";
    })
    .catch(() => {
        msg.textContent = 'Erreur réseau.';
        msg.className   = 'message erreur';
        btn.disabled    = false;
        btn.textContent = "Enregistrer l'entrée";
    });
});
</script>