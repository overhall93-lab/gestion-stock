// ============================================================
//  assets/js/mouvements.js
//  Gestion AJAX des mouvements (entrée, sortie, historique)
// ============================================================

// ── ENREGISTRER UNE ENTRÉE DE STOCK ──
function enregistrerEntree(formData) {
    formData.action = 'entree';

    fetch('controllers/mouvementController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('message-mouvements');
        if (data.succes) {
            msg.textContent = '✓ ' + data.message;
            msg.className = 'message succes';

            rafraichirStockApresAction(data.nouveau_stock, data.data?.id_article);
            mettreAJourCompteurSidebar();

            setTimeout(() => {
                document.getElementById('formulaire-entree').reset();
                chargerHistorique();
            }, 1500);
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className = 'message erreur';
        }
    })
    .catch(err => {
        document.getElementById('message-mouvements').textContent = '✗ Erreur réseau';
        document.getElementById('message-mouvements').className = 'message erreur';
        console.error('Erreur enregistrerEntree:', err);
    });
}

// ── ENREGISTRER UNE SORTIE DE STOCK ──
function enregistrerSortie(formData) {
    formData.action = 'sortie';

    fetch('controllers/mouvementController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('message-mouvements');
        if (data.succes) {
            msg.textContent = '✓ ' + data.message;
            msg.className = 'message succes';

            if (data.alerte) {
                msg.textContent = '⚠️ ' + data.message + ' — ALERTE STOCK ATTEINTE !';
                msg.className = 'message alerte';
            }

            rafraichirStockApresAction(data.nouveau_stock, data.data?.id_article);
            mettreAJourCompteurSidebar();

            setTimeout(() => {
                document.getElementById('formulaire-sortie').reset();
                chargerHistorique();
            }, 1500);
        } else {
            msg.textContent = '✗ ' + data.message;
            msg.className = 'message erreur';
        }
    })
    .catch(err => {
        document.getElementById('message-mouvements').textContent = '✗ Erreur réseau';
        document.getElementById('message-mouvements').className = 'message erreur';
        console.error('Erreur enregistrerSortie:', err);
    });
}

// ── RAFRAÎCHIR LE STOCK APRÈS UNE ACTION ──
// Cette fonction met à jour l'affichage du stock en temps réel
function rafraichirStockApresAction(nouveauStock, idArticle) {
    // Mettre à jour dans la liste si elle existe
    const ligneListe = document.querySelector(`tr[data-article-id="${idArticle}"]`);
    if (ligneListe) {
        const celluleStock = ligneListe.querySelector('td:nth-child(5)');
        if (celluleStock) {
            celluleStock.textContent = nouveauStock;
            celluleStock.classList.toggle('stock-bas', parseInt(nouveauStock) <= 5);
        }
    }

    // Mettre à jour dans la fiche si elle existe
    const fichStock = document.getElementById('fiche-stock');
    if (fichStock) {
        const seuil = parseInt(document.getElementById('fiche-seuil').textContent);
        const estAlerte = parseInt(nouveauStock) <= seuil;
        fichStock.textContent = nouveauStock + (estAlerte ? ' ⚠️ ALERTE' : '');
        fichStock.classList.toggle('alerte-stock', estAlerte);
    }

    // Mettre à jour la valeur du stock si elle existe
    const fichValeur = document.getElementById('fiche-valeur');
    if (fichValeur) {
        const prix = parseFloat(document.getElementById('fiche-prix').textContent);
        const nouvelleValeur = (nouveauStock * prix).toFixed(2);
        fichValeur.textContent = nouvelleValeur + ' FCFA';
    }
}

// ── METTRE À JOUR LE COMPTEUR DE SIDEBAR ──
// Recharge les indicateurs du tableau de bord (nombre articles, valeur stock, alertes)
function mettreAJourCompteurSidebar() {
    fetch('controllers/dashboardController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'indicateurs' })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) {
            console.error('Erreur mise à jour sidebar:', data.message);
            return;
        }

        // Mettre à jour les éléments du sidebar s'ils existent
        const elemNbArticles = document.getElementById('sidebar-nb-articles');
        if (elemNbArticles) {
            elemNbArticles.textContent = data.data.nombre_articles_total || 0;
        }

        const elemValeurStock = document.getElementById('sidebar-valeur-stock');
        if (elemValeurStock) {
            elemValeurStock.textContent = parseFloat(data.data.valeur_stock_total).toFixed(2) + ' FCFA';
        }

        const elemAlertes = document.getElementById('sidebar-nb-alertes');
        if (elemAlertes) {
            const nbAlertes = data.data.nombre_articles_en_alerte || 0;
            elemAlertes.textContent = nbAlertes;
            elemAlertes.classList.toggle('alerte', nbAlertes > 0);
        }

        const elemDerniers = document.getElementById('sidebar-derniers-mouvements');
        if (elemDerniers && data.data.derniers_mouvements) {
            elemDerniers.innerHTML = data.data.derniers_mouvements
                .slice(0, 3)
                .map(mv => `<li>${mv.type === 'entree' ? '⬆️' : '⬇️'} ${mv.id} - ${mv.date_heure}</li>`)
                .join('');
        }
    })
    .catch(err => {
        console.error('Erreur réseau mettreAJourCompteurSidebar:', err);
    });
}

// ── CHARGER L'HISTORIQUE DES MOUVEMENTS ──
function chargerHistorique() {
    fetch('controllers/mouvementController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'historique' })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.succes) {
            document.getElementById('tableau-mouvements').innerHTML =
                '<p class="erreur">' + data.message + '</p>';
            return;
        }

        if (!data.data || data.data.length === 0) {
            document.getElementById('tableau-mouvements').innerHTML =
                '<p class="vide">Aucun mouvement enregistré.</p>';
            return;
        }

        afficherTableauHistorique(data.data);
    })
    .catch(() => {
        document.getElementById('tableau-mouvements').innerHTML =
            '<p class="erreur">Erreur de chargement.</p>';
    });
}

// ── AFFICHER LE TABLEAU D'HISTORIQUE ──
function afficherTableauHistorique(mouvements) {
    let html = `<table class="tableau" id="tableau-mouvements-complet">
        <thead><tr>
            <th>ID Mouvement</th>
            <th>Type</th>
            <th>Article</th>
            <th>Quantité</th>
            <th>Motif</th>
            <th>Date/Heure</th>
            <th>Stock après</th>
        </tr></thead><tbody>`;

    mouvements.forEach(mv => {
        const typeClass = mv.type === 'entree' ? 'entree' : 'sortie';
        const typeIcon = mv.type === 'entree' ? '⬆️ Entrée' : '⬇️ Sortie';

        html += `<tr class="mouvement-${typeClass}">
            <td>${mv.id}</td>
            <td><span class="badge badge-${typeClass}">${typeIcon}</span></td>
            <td>${mv.id_article}</td>
            <td>${mv.quantite}</td>
            <td>${mv.motif}</td>
            <td>${mv.date_heure}</td>
            <td>${mv.stock_apres}</td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('tableau-mouvements').innerHTML = html;
}

// ── FILTRER PAR PÉRIODE ──
function filtrerParPeriode(dateDebut, dateFin) {
    fetch('controllers/mouvementController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({
            action: 'parPeriode',
            date_debut: dateDebut,
            date_fin: dateFin
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.succes && data.data) {
            afficherTableauHistorique(data.data);
        } else {
            document.getElementById('tableau-mouvements').innerHTML =
                '<p class="vide">Aucun mouvement pendant cette période.</p>';
        }
    })
    .catch(err => {
        console.error('Erreur filtrerParPeriode:', err);
    });
}

// ── INITIALISER À LA CHARGE DE LA PAGE ──
document.addEventListener('DOMContentLoaded', function () {
    // Charger l'historique si on est sur la page mouvements
    if (document.getElementById('tableau-mouvements')) {
        chargerHistorique();
    }

    // Charger les compteurs sidebar si on en a
    mettreAJourCompteurSidebar();

    // Rafraîchir les compteurs toutes les 60 secondes
    setInterval(mettreAJourCompteurSidebar, 60000);
});