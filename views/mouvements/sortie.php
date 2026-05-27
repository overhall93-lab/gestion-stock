<div class="page-header">
    <h2>📤 Sortie de stock</h2>
</div>

<div id="message-sortie" class="message hidden"></div>

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
            <label for="quantite">Quantité à sortir *</label>
            <input type="number" id="quantite" min="1" placeholder="0">
        </div>

        <div class="form-group">
            <label for="motif">Motif *</label>
            <input type="text" id="motif" placeholder="Ex: Vente client, Perte, Casse...">
        </div>

        <div class="form-actions">
            <button id="btn-sortie" class="btn btn-danger">Enregistrer la sortie</button>
            <a href="index.php?vue=articles/liste" class="btn btn-secondary">Annuler</a>
        </div>

    </div>
</div>

<script>
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
        data.data.filter(a => a.statut === 'actif' && parseInt(a.quantite_stock) > 0).forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id;
            opt.dataset.stock = a.quantite_stock;
            opt.textContent   = a.nom + ' (stock: ' + a.quantite_stock + ')';
            select.appendChild(opt);
        });
    });
});

document.getElementById('id_article').addEventListener('change', function () {
    const opt  = this.options[this.selectedIndex];
    const info = document.getElementById('stock-actuel-info');
    if (opt.value) {
        info.textContent  = 'Stock disponible : ' + opt.dataset.stock;
        info.style.color  = parseInt(opt.dataset.stock) <= 5 ? 'red' : 'green';
    } else {
        info.textContent = '';
    }
});

document.getElementById('btn-sortie').addEventListener('click', function () {
    const msg = document.getElementById('message-sortie');
    const btn = this;
    const donnees = {
        action    : 'sortie',
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
            let texte = '✓ Sortie enregistrée. Nouveau stock : ' + data.nouveau_stock;
            if (data.alerte) texte += ' ⚠️ ALERTE : stock sous le seuil !';
            msg.textContent = texte;
            msg.className   = data.alerte ? 'message alerte' : 'message succes';
            document.getElementById('quantite').value = '';
            document.getElementById('motif').value    = '';
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className   = 'message erreur';
        }
        btn.disabled    = false;
        btn.textContent = 'Enregistrer la sortie';
    })
    .catch(() => {
        msg.textContent = 'Erreur réseau.';
        msg.className   = 'message erreur';
        btn.disabled    = false;
    });
});
</script>