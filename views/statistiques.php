<div class="page-header">
    <h2>📈 Statistiques</h2>
</div>

<div class="stats-grid">

    <!-- Camembert répartition par catégorie -->
    <div class="section-card">
        <h3>Répartition par catégorie</h3>
        <div class="chart-wrapper">
            <canvas id="chart-categories"></canvas>
        </div>
    </div>

    <!-- Top 5 articles les plus mouvementés -->
    <div class="section-card">
        <h3>Top 5 articles mouvementés</h3>
        <div class="chart-wrapper">
            <canvas id="chart-top"></canvas>
        </div>
    </div>

    <!-- Évolution stock d'un article -->
    <div class="section-card stat-full">
        <h3>Évolution du stock</h3>
        <div class="form-row">
            <select id="select-article-evolution" class="select-filtre">
                <option value="">-- Choisir un article --</option>
            </select>
        </div>
        <div class="chart-wrapper">
            <canvas id="chart-evolution"></canvas>
        </div>
    </div>

</div>

<!-- Chart.js depuis CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script src="assets/js/statistiques.js"></script>