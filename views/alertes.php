<div class="page-header">
    <h2>⚠️ Articles en Alerte</h2>
</div>

<!-- ── MESSAGE RETOUR ── -->
<div id="message-alertes" class="message hidden"></div>

<!-- ── TABLEAU DES ARTICLES EN ALERTE ── -->
<div class="section-card">
    <div id="tableau-alertes">
        <p class="chargement">Chargement des articles en alerte...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    chargerAlerts();
    setInterval(chargerAlerts, 30000); // Rafraîchir toutes les 30 secondes
});

function chargerAlerts() {
    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'alertes' })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) {
            document.getElementById('tableau-alertes').innerHTML =
                '<p class="erreur">' + data.message + '</p>';
            return;
        }

        if (!data.data || data.data.length === 0) {
            document.getElementById('tableau-alertes').innerHTML =
                '<p class="succes">✓ Aucun article en alerte. Stock bien géré !</p>';
            return;
        }

        afficherTableauAlertes(data.data);
    })
    .catch(() => {
        document.getElementById('tableau-alertes').innerHTML =
            '<p class="erreur">Erreur de chargement.</p>';
    });
}

function afficherTableauAlertes(articles) {
    const zone = document.getElementById('tableau-alertes');

    let html = `<div class="alertes-resume">
        <p class="alerte-count">
            <strong>${articles.length}</strong> article(s) sous le seuil d'alerte
        </p>
    </div>

    <table class="tableau tableau-alertes">
        <thead><tr>
            <th>ID</th><th>Nom</th><th>Catégorie</th>
            <th>Stock actuel</th><th>Seuil d'alerte</th><th>Manque</th><th>Actions</th>
        </tr></thead><tbody>`;

    articles.forEach(a => {
        const manque = parseInt(a.seuil_alerte) - parseInt(a.quantite_stock);

        html += `<tr class="alerte-row">
            <td>${a.id}</td>
            <td>${a.nom}</td>
            <td>${a.categorie}</td>
            <td class="stock-critique">${a.quantite_stock}</td>
            <td>${a.seuil_alerte}</td>
            <td class="manque"><strong>-${manque}</strong></td>
            <td class="actions">
                <a href="index.php?vue=articles/fiche&id=${a.id}"
                   class="btn btn-sm btn-info">Voir fiche</a>
                <?php if (estGestionnaire()): ?>
                <a href="index.php?vue=mouvements/entree&id=${a.id}"
                   class="btn btn-sm btn-success">+ Stock</a>
                <?php endif; ?>
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    zone.innerHTML = html;
}
</script>

<style>
.alertes-resume {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
}

.alerte-count {
    margin: 0;
    color: #856404;
    font-size: 16px;
}

.tableau-alertes {
    width: 100%;
}

.alerte-row {
    background: #ffe6e6;
    border-left: 4px solid #e74c3c;
}

.stock-critique {
    font-weight: bold;
    color: #e74c3c;
}

.manque {
    background: #fadbd8;
    font-weight: bold;
    color: #c0392b;
}

.alerte-row:hover {
    background: #ffcccc;
}
</style>