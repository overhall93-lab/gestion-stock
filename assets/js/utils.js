// ============================================================
//  utils.js — Fonctions utilitaires communes
//  Chargé en PREMIER sur toutes les pages via index.php
//  NE PAS MODIFIER sans consulter le responsable backend
// ============================================================

// ── Afficher un message de retour dans une zone HTML ──
function afficherMessage(idZone, texte, type) {
    // type : 'succes' | 'erreur' | 'alerte' | 'info'
    const el = document.getElementById(idZone);
    if (!el) return;
    el.textContent = texte;
    el.className   = 'message ' + type;
    // Masquer automatiquement après 5 secondes pour les succès
    if (type === 'succes') {
        setTimeout(() => { el.className = 'message hidden'; }, 5000);
    }
}

// ── Requête AJAX POST générique ──
// Retourne une Promise avec la réponse JSON parsée
function ajaxPost(url, donnees) {
    return fetch(url, {
        method : 'POST',
        headers: {
            'Content-Type'     : 'application/json',
            'X-Requested-With' : 'XMLHttpRequest'
        },
        body: JSON.stringify(donnees)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur serveur HTTP ' + response.status);
        }
        return response.json();
    });
}

// ── Requête AJAX GET générique ──
function ajaxGet(url, params) {
    const query = new URLSearchParams(params).toString();
    const urlComplete = query ? url + '?' + query : url;
    return fetch(urlComplete, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur HTTP ' + response.status);
        return response.json();
    });
}

// ── Désactiver/réactiver un bouton pendant une requête ──
function boutonChargement(btn, enChargement, texteOriginal, texteChargement) {
    if (enChargement) {
        btn.disabled    = true;
        btn.dataset.original = btn.textContent;
        btn.textContent = texteChargement || 'Chargement...';
    } else {
        btn.disabled    = false;
        btn.textContent = texteOriginal || btn.dataset.original || 'Valider';
    }
}

// ── Formater une date ISO en date lisible ──
function formaterDate(dateISO) {
    if (!dateISO) return '';
    return new Date(dateISO).toLocaleDateString('fr-FR', {
        day  : '2-digit',
        month: '2-digit',
        year : 'numeric',
        hour : '2-digit',
        minute: '2-digit'
    });
}

// ── Formater un nombre en montant ──
function formaterMontant(nombre) {
    return parseFloat(nombre).toLocaleString('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + ' FCFA';
}

// ── Créer une ligne de tableau HTML ──
function creerLigneTableau(cellules) {
    return '<tr>' + cellules.map(c => '<td>' + c + '</td>').join('') + '</tr>';
}

// ── Afficher un état de chargement dans une zone ──
function afficherChargement(idZone) {
    const el = document.getElementById(idZone);
    if (el) el.innerHTML = '<p class="chargement">⏳ Chargement...</p>';
}

// ── Afficher une zone vide ──
function afficherVide(idZone, message) {
    const el = document.getElementById(idZone);
    if (el) el.innerHTML = '<p class="vide">' + (message || 'Aucun résultat.') + '</p>';
}

// ── Confirmer une action destructive ──
function confirmerAction(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir effectuer cette action ?');
}

// ── Valider un formulaire côté client avant envoi ──
function validerChamps(champs) {
    // champs = [ {id: 'nom', label: 'Nom'}, ... ]
    const erreurs = [];
    champs.forEach(champ => {
        const el = document.getElementById(champ.id);
        if (!el) return;
        const valeur = el.value.trim();
        if (!valeur) {
            erreurs.push(champ.label + ' est obligatoire.');
            el.classList.add('champ-erreur');
        } else {
            el.classList.remove('champ-erreur');
        }
    });
    return erreurs;
}