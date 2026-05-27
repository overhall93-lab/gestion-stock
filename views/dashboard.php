<?php
// ============================================================
//  views/dashboard.php — Tableau de bord
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';

verifierConnexion();

$titrePage = 'Tableau de bord';
include __DIR__ . '/layout/header.php';
?>

<div class="dashboard-grid">

    <!-- Widget : Total articles -->
    <div class="widget" id="widget-articles">
        <div class="widget-icon widget-icon-blue">&#128230;</div>
        <div class="widget-body">
            <div class="widget-valeur" id="total-articles">—</div>
            <div class="widget-label">Articles actifs</div>
        </div>
    </div>

    <!-- Widget : Valeur du stock -->
    <div class="widget" id="widget-valeur">
        <div class="widget-icon widget-icon-green">&#128181;</div>
        <div class="widget-body">
            <div class="widget-valeur" id="valeur-stock">—</div>
            <div class="widget-label">Valeur du stock</div>
        </div>
    </div>

    <!-- Widget : Alertes -->
    <div class="widget" id="widget-alertes">
        <div class="widget-icon widget-icon-red">&#9888;&#65039;</div>
        <div class="widget-body">
            <div class="widget-valeur" id="nb-alertes">—</div>
            <div class="widget-label">Articles en alerte</div>
        </div>
    </div>

    <!-- Widget : Derniers mouvements -->
    <div class="widget widget-wide" id="widget-mouvements">
        <h3 class="widget-titre">Derniers mouvements</h3>
        <table class="table" id="table-derniers-mouvements">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Type</th>
                    <th>Quantité</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="tbody-mouvements">
                <tr><td colspan="4" class="text-center">Chargement...</td></tr>
            </tbody>
        </table>
    </div>

</div><!-- .dashboard-grid -->

<?php if (estGestionnaire()): ?>
<div class="dashboard-actions">
    <a href="<?= $racineUrl ?>views/articles/formulaire.php" class="btn btn-primary">
        + Nouvel article
    </a>
    <a href="<?= $racineUrl ?>views/mouvements/entree.php" class="btn btn-success">
        &#8593; Entrée stock
    </a>
    <a href="<?= $racineUrl ?>views/mouvements/sortie.php" class="btn btn-warning">
        &#8595; Sortie stock
    </a>
</div>
<?php endif; ?>

<script src="<?= $assetsPath ?>js/utils.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    chargerIndicateurs();
});

function chargerIndicateurs() {
    ajaxPost(RACINE_URL + 'controllers/dashboardController.php', {
        action: 'indicateurs'
    })
    .then(data => {
        if (!data.succes) return;

        document.getElementById('total-articles').textContent = data.total_articles;
        document.getElementById('valeur-stock').textContent   =
            new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' })
                .format(data.valeur_stock);
        document.getElementById('nb-alertes').textContent     = data.nb_alertes;

        // Badge alerte dans la navbar
        const badge = document.getElementById('badge-alertes');
        if (data.nb_alertes > 0) {
            badge.textContent     = data.nb_alertes;
            badge.style.display   = 'inline-block';
        }

        // Remplir le tableau des derniers mouvements
        const tbody = document.getElementById('tbody-mouvements');
        if (!data.derniers_mouvements || data.derniers_mouvements.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Aucun mouvement enregistré.</td></tr>';
            return;
        }

        tbody.innerHTML = data.derniers_mouvements.map(m => `
            <tr>
                <td>${echapper(m.nom_article)}</td>
                <td><span class="badge badge-${m.type}">${m.type}</span></td>
                <td>${m.quantite}</td>
                <td>${formaterDate(m.date_heure)}</td>
            </tr>
        `).join('');
    })
    .catch(() => afficherErreur('Impossible de charger les indicateurs.'));
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>