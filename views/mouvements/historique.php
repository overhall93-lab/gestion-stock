<div class="page-header">
    <h2>📋 Historique des mouvements</h2>
</div>

<!-- Filtres -->
<div class="barre-filtres">
    <input type="date" id="date-debut" class="input-date">
    <span>au</span>
    <input type="date" id="date-fin" class="input-date">
    <select id="filtre-type" class="select-filtre">
        <option value="">Tous les types</option>
        <option value="entree">Entrées</option>
        <option value="sortie">Sorties</option>
    </select>
    <button id="btn-filtrer" class="btn btn-primary">Filtrer</button>
    <button id="btn-reset" class="btn btn-secondary">Réinitialiser</button>
</div>

<div id="message-historique" class="message hidden"></div>

<div class="section-card">
    <div id="tableau-historique">
        <p class="chargement">Chargement...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', chargerHistorique);
document.getElementById('btn-filtrer').addEventListener('click', filtrerParDate);
document.getElementById('btn-reset').addEventListener('click', function () {
    document.getElementById('date-debut').value = '';
    document.getElementById('date-fin').value   = '';
    document.getElementById('filtre-type').value = '';
    chargerHistorique();
});

function chargerHistorique() {
    fetch('controllers/mouvementController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'historique' })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) return;
        const type = document.getElementById('filtre-type').value;
        const filtres = type ? data.data.filter(m => m.type === type) : data.data;
        afficherHistorique(filtres);
    });
}

function filtrerParDate() {
    const debut = document.getElementById('date-debut').value;
    const fin   = document.getElementById('date-fin').value;
    if (!debut || !fin) {
        chargerHistorique();
        return;
    }
    fetch('controllers/mouvementController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'parPeriode', date_debut: debut, date_fin: fin })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) return;
        afficherHistorique(data.data);
    });
}

function afficherHistorique(mouvements) {
    const zone = document.getElementById('tableau-historique');
    if (mouvements.length === 0) {
        zone.innerHTML = '<p class="vide">Aucun mouvement trouvé.</p>';
        return;
    }
    let html = `<table class="tableau">
        <thead><tr>
            <th>ID</th><th>Date</th><th>Article</th>
            <th>Type</th><th>Quantité</th><th>Stock après</th><th>Motif</th>
        </tr></thead><tbody>`;

    mouvements.forEach(m => {
        html += `<tr>
            <td>${m.id}</td>
            <td>${m.date_heure}</td>
            <td>${m.nom_article || m.id_article}</td>
            <td><span class="badge badge-${m.type}">${m.type}</span></td>
            <td>${m.quantite}</td>
            <td>${m.stock_apres}</td>
            <td>${m.motif}</td>
        </tr>`;
    });
    html += '</tbody></table>';
    zone.innerHTML = html;
}
</script>