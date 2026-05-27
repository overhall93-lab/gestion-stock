// ============================================================
//  statistiques.js — Graphiques de la page statistiques
//  Dépend de : utils.js + Chart.js (chargé dans statistiques.php)
//  Responsable : Membre 3 (DOM/JS) pour les améliorations visuelles
// ============================================================

let chartCategories = null;
let chartTop        = null;
let chartEvolution  = null;

document.addEventListener('DOMContentLoaded', function () {
    chargerStatistiques();
    chargerArticlesPourEvolution();
});

// ── Charger les données statistiques depuis le serveur ──
function chargerStatistiques() {
    ajaxPost('controllers/dashboardController.php', { action: 'statistiques' })
    .then(data => {
        if (!data.succes) return;
        dessinerCamembertCategories(data.repartition_categories);
        dessinerBarresTop(data.top_articles);
    })
    .catch(() => {
        console.error('Erreur chargement statistiques');
    });
}

// ── Graphique 1 : Camembert répartition par catégorie ──
function dessinerCamembertCategories(repartition) {
    const labels = Object.keys(repartition);
    const valeurs = Object.values(repartition);

    const couleurs = [
        '#4F86C6', '#F4A261', '#2A9D8F', '#E76F51',
        '#264653', '#A8DADC', '#457B9D', '#E9C46A'
    ];

    const ctx = document.getElementById('chart-categories').getContext('2d');

    if (chartCategories) chartCategories.destroy();

    chartCategories = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels  : labels,
            datasets: [{
                data           : valeurs,
                backgroundColor: couleurs.slice(0, labels.length),
                borderWidth    : 2,
                borderColor    : '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels  : { padding: 15, font: { size: 13 } }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.label + ' : ' + ctx.raw + ' article(s)'
                    }
                }
            }
        }
    });
}

// ── Graphique 2 : Barres horizontales top articles ──
function dessinerBarresTop(topArticles) {
    if (!topArticles || topArticles.length === 0) return;

    const labels  = topArticles.map(a => a.nom);
    const valeurs = topArticles.map(a => a.nb_mouvements);

    const ctx = document.getElementById('chart-top').getContext('2d');

    if (chartTop) chartTop.destroy();

    chartTop = new Chart(ctx, {
        type: 'bar',
        data: {
            labels  : labels,
            datasets: [{
                label          : 'Nombre de mouvements',
                data           : valeurs,
                backgroundColor: '#4F86C6',
                borderRadius   : 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}

// ── Charger la liste des articles pour le select évolution ──
function chargerArticlesPourEvolution() {
    ajaxPost('controllers/articleController.php', { action: 'lister' })
    .then(data => {
        if (!data.succes) return;
        const select = document.getElementById('select-article-evolution');
        data.data.filter(a => a.statut === 'actif').forEach(a => {
            const opt       = document.createElement('option');
            opt.value       = a.id;
            opt.textContent = a.nom;
            select.appendChild(opt);
        });

        // Charger l'évolution quand on choisit un article
        select.addEventListener('change', function () {
            if (this.value) chargerEvolution(this.value);
        });
    });
}

// ── Graphique 3 : Courbe d'évolution du stock ──
function chargerEvolution(idArticle) {
    ajaxPost('controllers/dashboardController.php', {
        action    : 'evolution',
        id_article: idArticle
    })
    .then(data => {
        if (!data.succes) return;
        dessinerCourbeEvolution(data.evolution, data.nom_article);
    });
}

function dessinerCourbeEvolution(evolution, nomArticle) {
    const labels  = Object.keys(evolution);
    const valeurs = Object.values(evolution);

    const ctx = document.getElementById('chart-evolution').getContext('2d');

    if (chartEvolution) chartEvolution.destroy();

    chartEvolution = new Chart(ctx, {
        type: 'line',
        data: {
            labels  : labels,
            datasets: [{
                label          : 'Stock — ' + nomArticle,
                data           : valeurs,
                borderColor    : '#2A9D8F',
                backgroundColor: 'rgba(42, 157, 143, 0.1)',
                borderWidth    : 2,
                pointRadius    : 4,
                fill           : true,
                tension        : 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}