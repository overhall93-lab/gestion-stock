<?php
// ============================================================
//  views/mouvements/entree.php — Formulaire entrée de stock
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/xml-manager.php';
require_once __DIR__ . '/../../includes/session.php';

verifierRole([ROLE_ADMIN, ROLE_GESTIONNAIRE]);

$articles = array_filter(articlesListerTous(), fn($a) => $a['statut'] === 'actif');

$titrePage = 'Entrée de stock';
include __DIR__ . '/../layout/header.php';
?>

<div class="form-container">
    <div id="zone-message"></div>

    <div class="form-card">
        <h3>&#8593; Enregistrer une entrée de stock</h3>

        <div class="form-group">
            <label for="id_article">Article <span class="required">*</span></label>
            <select id="id_article" name="id_article" onchange="afficherStockActuel()">
                <option value="">-- Sélectionner un article --</option>
                <?php foreach ($articles as $a): ?>
                    <option value="<?= htmlspecialchars($a['id']) ?>"
                            data-stock="<?= $a['quantite_stock'] ?>"
                            data-seuil="<?= $a['seuil_alerte'] ?>">
                        <?= htmlspecialchars($a['nom']) ?> — Stock actuel : <?= $a['quantite_stock'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="stock-info" id="stock-info" style="display:none;">
            <span>Stock actuel : <strong id="stock-actuel">—</strong></span>
            <span>Seuil d'alerte : <strong id="seuil-alerte">—</strong></span>
        </div>

        <div class="form-group">
            <label for="quantite">Quantité à ajouter <span class="required">*</span></label>
            <input type="number" id="quantite" name="quantite"
                   min="1" step="1" value="" placeholder="Ex: 10">
        </div>

        <div class="form-group">
            <label for="motif">Motif <span class="required">*</span></label>
            <input type="text" id="motif" name="motif" maxlength="200"
                   placeholder="Ex: Réapprovisionnement fournisseur">
        </div>

        <div class="form-actions">
            <button type="button" id="btn-entree" class="btn btn-success">
                &#8593; Valider l'entrée
            </button>
            <a href="historique.php" class="btn btn-secondary">Annuler</a>
        </div>
    </div>
</div>

<script src="<?= $assetsPath ?>js/utils.js"></script>
<script>
function afficherStockActuel() {
    const sel   = document.getElementById('id_article');
    const opt   = sel.options[sel.selectedIndex];
    const info  = document.getElementById('stock-info');
    if (!opt.value) { info.style.display = 'none'; return; }
    document.getElementById('stock-actuel').textContent = opt.dataset.stock;
    document.getElementById('seuil-alerte').textContent  = opt.dataset.seuil;
    info.style.display = 'flex';
}

document.getElementById('btn-entree').addEventListener('click', function() {
    const donnees = {
        action:     'entree',
        id_article: document.getElementById('id_article').value,
        quantite:   document.getElementById('quantite').value,
        motif:      document.getElementById('motif').value.trim()
    };

    if (!donnees.id_article) { afficherErreur('Veuillez sélectionner un article.', 'zone-message'); return; }
    if (!donnees.quantite || donnees.quantite < 1) { afficherErreur('La quantité doit être ≥ 1.', 'zone-message'); return; }
    if (!donnees.motif) { afficherErreur('Le motif est obligatoire.', 'zone-message'); return; }

    const btn = this;
    btn.disabled    = true;
    btn.textContent = 'Enregistrement...';

    ajaxPost(RACINE_URL + 'controllers/mouvementController.php', donnees)
    .then(data => {
        afficherMessage(data.succes, data.message, 'zone-message');
        if (data.succes) {
            // Mettre à jour le stock affiché dans le select
            const sel = document.getElementById('id_article');
            sel.options[sel.selectedIndex].dataset.stock = data.nouveau_stock;
            sel.options[sel.selectedIndex].text = sel.options[sel.selectedIndex].text.replace(
                /Stock actuel : \d+/, 'Stock actuel : ' + data.nouveau_stock
            );
            document.getElementById('stock-actuel').textContent = data.nouveau_stock;

            if (data.alerte) {
                afficherMessage(false, 'Attention : le stock est sous le seuil d\'alerte !', 'zone-message');
            }

            // Réinitialiser le formulaire
            document.getElementById('quantite').value = '';
            document.getElementById('motif').value    = '';
        }
        btn.disabled    = false;
        btn.textContent = '↑ Valider l\'entrée';
    })
    .catch(() => {
        afficherErreur('Erreur réseau.', 'zone-message');
        btn.disabled    = false;
        btn.textContent = '↑ Valider l\'entrée';
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>