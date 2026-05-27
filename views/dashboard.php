<div class="page-header">
    <h2>Tableau de bord</h2>
    <p class="page-subtitle">Vue générale du stock</p>
</div>

<!-- ── 4 WIDGETS INDICATEURS ── -->
<div class="widgets-grid" id="widgets-grid">

    <div class="widget" id="widget-articles">
        <div class="widget-icone">📦</div>
        <div class="widget-valeur" id="w-total-articles">--</div>
        <div class="widget-label">Articles actifs</div>
    </div>

    <div class="widget" id="widget-valeur">
        <div class="widget-icone">💰</div>
        <div class="widget-valeur" id="w-valeur-stock">--</div>
        <div class="widget-label">Valeur du stock</div>
    </div>

    <div class="widget widget-alerte" id="widget-alertes">
        <div class="widget-icone">⚠️</div>
        <div class="widget-valeur" id="w-nb-alertes">--</div>
        <div class="widget-label">Articles en alerte</div>
    </div>

    <div class="widget" id="widget-mouvements">
        <div class="widget-icone">🔄</div>
        <div class="widget-valeur" id="w-nb-mouvements">--</div>
        <div class="widget-label">Mouvements total</div>
    </div>

</div>

<!-- ── DERNIERS MOUVEMENTS ── -->
<div class="section-card">
    <h3>Derniers mouvements</h3>
    <div id="derniers-mouvements">
        <p class="chargement">Chargement...</p>
    </div>
</div>

<!-- ── ARTICLES EN ALERTE ── -->
<div class="section-card">
    <h3>Articles en alerte de stock ⚠️</h3>
    <div id="liste-alertes-dashboard">
        <p class="chargement">Chargement...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Charger les indicateurs ──
    fetch('controllers/dashboardController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'indicateurs' })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) return;

        document.getElementById('w-total-articles').textContent = data.total_articles;
        document.getElementById('w-valeur-stock').textContent   = data.valeur_stock.toFixed(2) + ' FCFA';
        document.getElementById('w-nb-alertes').textContent     = data.nb_alertes;

        // Colorer le widget alertes si > 0
        if (data.nb_alertes > 0) {
            document.getElementById('widget-alertes').classList.add('alerte-active');
        }

        // ── Afficher les derniers mouvements ──
        const zone = document.getElementById('derniers-mouvements');
        if (data.derniers_mouvements.length === 0) {
            zone.innerHTML = '<p class="vide">Aucun mouvement enregistré.</p>';
            return;
        }

        let html = '<table class="tableau"><thead><tr>' +
            '<th>Date</th><th>Article</th><th>Type</th><th>Quantité</th><th>Stock après</th>' +
            '</tr></thead><tbody>';

        data.derniers_mouvements.forEach(m => {
            const classe = m.type === 'entree' ? 'entree' : 'sortie';
            html += `<tr>
                <td>${m.date_heure}</td>
                <td>${m.nom_article}</td>
                <td><span class="badge badge-${classe}">${m.type}</span></td>
                <td>${m.quantite}</td>
                <td>${m.stock_apres}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        zone.innerHTML = html;
    })
    .catch(() => {
        document.getElementById('derniers-mouvements').innerHTML =
            '<p class="erreur">Erreur de chargement.</p>';
    });

    // ── Charger les alertes ──
    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'alertes' })
    })
    .then(r => r.json())
    .then(data => {
        const zone = document.getElementById('liste-alertes-dashboard');
        if (!data.succes || data.data.length === 0) {
            zone.innerHTML = '<p class="succes-texte">✓ Aucun article en alerte.</p>';
            document.getElementById('w-nb-mouvements');
            return;
        }

        let html = '<table class="tableau"><thead><tr>' +
            '<th>Article</th><th>Catégorie</th><th>Stock actuel</th><th>Seuil</th><th>Action</th>' +
            '</tr></thead><tbody>';

        data.data.forEach(a => {
            html += `<tr class="ligne-alerte">
                <td>${a.nom}</td>
                <td>${a.categorie}</td>
                <td class="stock-bas">${a.quantite_stock}</td>
                <td>${a.seuil_alerte}</td>
                <td>
                    <a href="index.php?vue=mouvements/entree&id=${a.id}"
                       class="btn btn-sm btn-primary">Réapprovisionner</a>
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        zone.innerHTML = html;
    })
    .catch(() => {});
});
</script>