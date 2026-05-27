<?php
// La vue active est définie dans index.php
$vueActive = $_GET['vue'] ?? 'dashboard';
?>
<nav class="sidebar" id="sidebar">

    <ul class="nav-menu">

        <!-- Tableau de bord — tous les rôles -->
        <li class="nav-item <?= $vueActive === 'dashboard' ? 'active' : '' ?>">
            <a href="index.php?vue=dashboard" class="nav-link">
                <span class="nav-icone">📊</span>
                <span class="nav-texte">Tableau de bord</span>
            </a>
        </li>

        <!-- ── STOCK ── -->
        <li class="nav-section">Stock</li>

        <!-- Articles — tous les rôles -->
        <li class="nav-item <?= str_starts_with($vueActive, 'articles') ? 'active' : '' ?>">
            <a href="index.php?vue=articles/liste" class="nav-link">
                <span class="nav-icone">📦</span>
                <span class="nav-texte">Articles</span>
            </a>
        </li>

        <!-- Entrée stock — Admin et Gestionnaire -->
        <?php if (estGestionnaire()): ?>
        <li class="nav-item <?= $vueActive === 'mouvements/entree' ? 'active' : '' ?>">
            <a href="index.php?vue=mouvements/entree" class="nav-link">
                <span class="nav-icone">📥</span>
                <span class="nav-texte">Entrée de stock</span>
            </a>
        </li>

        <!-- Sortie stock — Admin et Gestionnaire -->
        <li class="nav-item <?= $vueActive === 'mouvements/sortie' ? 'active' : '' ?>">
            <a href="index.php?vue=mouvements/sortie" class="nav-link">
                <span class="nav-icone">📤</span>
                <span class="nav-texte">Sortie de stock</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Historique — tous les rôles -->
        <li class="nav-item <?= $vueActive === 'mouvements/historique' ? 'active' : '' ?>">
            <a href="index.php?vue=mouvements/historique" class="nav-link">
                <span class="nav-icone">📋</span>
                <span class="nav-texte">Historique</span>
            </a>
        </li>

        <!-- Alertes — tous les rôles -->
        <li class="nav-item <?= $vueActive === 'alertes' ? 'active' : '' ?>">
            <a href="index.php?vue=alertes" class="nav-link">
                <span class="nav-icone">⚠️</span>
                <span class="nav-texte">Alertes stock</span>
                <span class="badge-alerte" id="sidebar-alerte-count"></span>
            </a>
        </li>

        <!-- ── RAPPORTS ── -->
        <li class="nav-section">Rapports</li>

        <!-- Statistiques — tous les rôles -->
        <li class="nav-item <?= $vueActive === 'statistiques' ? 'active' : '' ?>">
            <a href="index.php?vue=statistiques" class="nav-link">
                <span class="nav-icone">📈</span>
                <span class="nav-texte">Statistiques</span>
            </a>
        </li>

        <!-- ── ADMINISTRATION ── Admin seulement -->
        <?php if (estAdmin()): ?>
        <li class="nav-section">Administration</li>

        <li class="nav-item <?= str_starts_with($vueActive, 'admin') ? 'active' : '' ?>">
            <a href="index.php?vue=admin/utilisateurs" class="nav-link">
                <span class="nav-icone">👥</span>
                <span class="nav-texte">Utilisateurs</span>
            </a>
        </li>
        <?php endif; ?>

    </ul>

    <div class="sidebar-footer">
        v<?= APP_VERSION ?>
    </div>

</nav>