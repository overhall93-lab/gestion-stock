<?php
// header.php est toujours chargé depuis index.php
// Les variables de session sont donc disponibles
?>
<header class="app-header">
    <div class="header-left">
        <button id="btn-menu-toggle" class="btn-menu-toggle" title="Menu">
            &#9776;
        </button>
        <span class="app-title"><?= APP_NOM ?></span>
    </div>

    <div class="header-right">
        <!-- Indicateur alertes stock -->
        <div class="header-alerte" id="header-alerte" title="Articles en alerte de stock">
            <span class="alerte-icone">⚠</span>
            <span class="alerte-count" id="alerte-count">0</span>
        </div>

        <!-- Infos utilisateur connecté -->
        <div class="header-user">
            <span class="user-nom"><?= htmlspecialchars($_SESSION['nom']) ?></span>
            <span class="user-role badge-<?= $_SESSION['role'] ?>">
                <?= ucfirst($_SESSION['role']) ?>
            </span>
        </div>

        <!-- Bouton déconnexion -->
        <a href="logout.php" class="btn btn-logout" title="Se déconnecter">
            Déconnexion
        </a>
    </div>
</header>

<script>
// Charger le nombre d'alertes au démarrage de chaque page
document.addEventListener('DOMContentLoaded', function () {
    fetch('controllers/articleController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'alertes' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.succes) {
            const count = data.data.length;
            const el    = document.getElementById('alerte-count');
            const bloc  = document.getElementById('header-alerte');
            el.textContent = count;
            if (count > 0) {
                bloc.classList.add('has-alertes');
            }
        }
    })
    .catch(() => {});
});
</script>