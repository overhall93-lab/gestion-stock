<?php
// ============================================================
//  views/articles/formulaire.php — Ajout / Modification article
//  ?id=ART001 → mode modification
//  sans paramètre → mode ajout
// ============================================================

define('GESTION_STOCK', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/xml-manager.php';
require_once __DIR__ . '/../../includes/session.php';

verifierRole([ROLE_ADMIN, ROLE_GESTIONNAIRE]);

$idArticle  = $_GET['id'] ?? null;
$modeEdit   = ($idArticle !== null);
$article    = null;

if ($modeEdit) {
    $article = articleLireParId($idArticle);
    if (!$article) {
        header('Location: liste.php?erreur=article_introuvable');
        exit;
    }
}

$titrePage = $modeEdit ? 'Modifier l\'article' : 'Nouvel article';
include __DIR__ . '/../layout/header.php';
?>

<div class="form-container">
    <div id="zone-message"></div>

    <div class="form-card">
        <h3><?= $modeEdit ? 'Modifier : ' . htmlspecialchars($article['nom']) : 'Ajouter un article' ?></h3>

        <div class="form-group">
            <label for="nom">Nom de l'article <span class="required">*</span></label>
            <input type="text" id="nom" name="nom" maxlength="100"
                   value="<?= $modeEdit ? htmlspecialchars($article['nom']) : '' ?>"
                   placeholder="Ex: Clavier mécanique">
        </div>

        <div class="form-group">
            <label for="categorie">Catégorie <span class="required">*</span></label>
            <select id="categorie" name="categorie">
                <option value="">-- Sélectionner --</option>
                <?php foreach (CATEGORIES as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"
                        <?= ($modeEdit && $article['categorie'] === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prix_unitaire">Prix unitaire (€) <span class="required">*</span></label>
                <input type="number" id="prix_unitaire" name="prix_unitaire"
                       min="0" step="0.01"
                       value="<?= $modeEdit ? htmlspecialchars($article['prix_unitaire']) : '' ?>"
                       placeholder="0.00">
            </div>

            <?php if (!$modeEdit): ?>
            <div class="form-group">
                <label for="quantite_stock">Quantité initiale <span class="required">*</span></label>
                <input type="number" id="quantite_stock" name="quantite_stock"
                       min="0" step="1"
                       value="0"
                       placeholder="0">
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="seuil_alerte">Seuil d'alerte <span class="required">*</span></label>
                <input type="number" id="seuil_alerte" name="seuil_alerte"
                       min="0" step="1"
                       value="<?= $modeEdit ? htmlspecialchars($article['seuil_alerte']) : STOCK_ALERTE_MIN ?>"
                       placeholder="5">
            </div>
        </div>

        <div class="form-actions">
            <button type="button" id="btn-soumettre" class="btn btn-primary">
                <?= $modeEdit ? 'Enregistrer les modifications' : 'Ajouter l\'article' ?>
            </button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </div>
    </div>
</div>

<script src="<?= $assetsPath ?>js/utils.js"></script>
<script>
const MODE_EDIT  = <?= $modeEdit ? 'true' : 'false' ?>;
const ID_ARTICLE = <?= $modeEdit ? json_encode($idArticle) : 'null' ?>;

document.getElementById('btn-soumettre').addEventListener('click', soumettreFormulaire);

function soumettreFormulaire() {
    const donnees = {
        action:        MODE_EDIT ? 'modifier' : 'ajouter',
        nom:           document.getElementById('nom').value.trim(),
        categorie:     document.getElementById('categorie').value,
        prix_unitaire: document.getElementById('prix_unitaire').value,
        seuil_alerte:  document.getElementById('seuil_alerte').value
    };

    if (MODE_EDIT) {
        donnees.id = ID_ARTICLE;
    } else {
        donnees.quantite_stock = document.getElementById('quantite_stock').value;
    }

    const btn = document.getElementById('btn-soumettre');
    btn.disabled    = true;
    btn.textContent = 'Enregistrement...';

    ajaxPost(RACINE_URL + 'controllers/articleController.php', donnees)
    .then(data => {
        afficherMessage(data.succes, data.message, 'zone-message');
        if (data.succes) {
            setTimeout(() => {
                window.location.href = RACINE_URL + 'views/articles/liste.php';
            }, 1000);
        } else {
            btn.disabled    = false;
            btn.textContent = MODE_EDIT ? 'Enregistrer les modifications' : 'Ajouter l\'article';
        }
    })
    .catch(() => {
        afficherErreur('Erreur réseau.', 'zone-message');
        btn.disabled    = false;
        btn.textContent = MODE_EDIT ? 'Enregistrer les modifications' : 'Ajouter l\'article';
    });
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>