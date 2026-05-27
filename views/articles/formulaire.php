<?php
// Si un ID est passé en paramètre, c'est une modification
$idArticle = $_GET['id'] ?? null;
$modeEdition = !empty($idArticle);
?>

<div class="page-header">
    <h2><?= $modeEdition ? 'Modifier l\'article' : 'Ajouter un article' ?></h2>
    <a href="index.php?vue=articles/liste" class="btn btn-secondary">← Retour</a>
</div>

<div id="message-form" class="message hidden"></div>

<div class="section-card">
    <div class="form-container">

        <div class="form-group">
            <label for="nom">Nom de l'article *</label>
            <input type="text" id="nom" placeholder="Ex: Disque dur SSD 1To" maxlength="100">
        </div>

        <div class="form-group">
            <label for="categorie">Catégorie *</label>
            <select id="categorie">
                <option value="">-- Choisir --</option>
                <?php foreach (CATEGORIES as $cat): ?>
                <option value="<?= $cat ?>"><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prix_unitaire">Prix unitaire (FCFA) *</label>
                <input type="number" id="prix_unitaire" min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group">
                <label for="seuil_alerte">Seuil d'alerte *</label>
                <input type="number" id="seuil_alerte" min="0" placeholder="5">
            </div>
        </div>

        <?php if (!$modeEdition): ?>
        <div class="form-group">
            <label for="quantite_stock">Quantité initiale en stock *</label>
            <input type="number" id="quantite_stock" min="0" placeholder="0">
        </div>
        <?php endif; ?>

        <div class="form-actions">
            <button id="btn-valider" class="btn btn-primary">
                <?= $modeEdition ? 'Enregistrer les modifications' : 'Ajouter l\'article' ?>
            </button>
            <a href="index.php?vue=articles/liste" class="btn btn-secondary">Annuler</a>
        </div>

    </div>
</div>

<script>
const ID_ARTICLE  = <?= $idArticle ? "'$idArticle'" : 'null' ?>;
const MODE_EDITION = <?= $modeEdition ? 'true' : 'false' ?>;

// Si mode édition, charger les données de l'article
if (MODE_EDITION && ID_ARTICLE) {
    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'lire', id: ID_ARTICLE })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) return;
        const a = data.data;
        document.getElementById('nom').value          = a.nom;
        document.getElementById('categorie').value    = a.categorie;
        document.getElementById('prix_unitaire').value= a.prix_unitaire;
        document.getElementById('seuil_alerte').value = a.seuil_alerte;
    });
}

document.getElementById('btn-valider').addEventListener('click', function () {
    const msg = document.getElementById('message-form');
    const btn = this;

    const donnees = {
        action       : MODE_EDITION ? 'modifier' : 'ajouter',
        nom          : document.getElementById('nom').value.trim(),
        categorie    : document.getElementById('categorie').value,
        prix_unitaire: document.getElementById('prix_unitaire').value,
        seuil_alerte : document.getElementById('seuil_alerte').value,
    };

    if (MODE_EDITION) {
        donnees.id = ID_ARTICLE;
    } else {
        donnees.quantite_stock = document.getElementById('quantite_stock').value;
    }

    // Validation côté client rapide
    if (!donnees.nom || !donnees.categorie || !donnees.prix_unitaire || !donnees.seuil_alerte) {
        msg.textContent = 'Veuillez remplir tous les champs obligatoires.';
        msg.className   = 'message erreur';
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Enregistrement...';

    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify(donnees)
    })
    .then(r => r.json())
    .then(data => {
        if (data.succes) {
            msg.textContent = '✓ ' + data.message;
            msg.className   = 'message succes';
            setTimeout(() => window.location.href = 'index.php?vue=articles/liste', 1200);
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className   = 'message erreur';
            btn.disabled    = false;
            btn.textContent = MODE_EDITION ? 'Enregistrer les modifications' : 'Ajouter l\'article';
        }
    })
    .catch(() => {
        msg.textContent = 'Erreur réseau.';
        msg.className   = 'message erreur';
        btn.disabled    = false;
    });
});
</script>