// ============================================================
//  admin.js — Gestion des utilisateurs (admin seulement)
//  Dépend de : utils.js
//  Responsable : Membre 3 (DOM/JS) pour les améliorations
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    chargerUtilisateurs();

    // Toggle formulaire ajout
    document.getElementById('btn-ouvrir-form').addEventListener('click', function () {
        document.getElementById('form-ajout-user').classList.toggle('hidden');
    });
    document.getElementById('btn-annuler-form').addEventListener('click', function () {
        document.getElementById('form-ajout-user').classList.add('hidden');
        reinitialiserFormulaire();
    });

    // Créer un utilisateur
    document.getElementById('btn-creer-user').addEventListener('click', creerUtilisateur);
});

// ── Charger tous les utilisateurs ──
function chargerUtilisateurs() {
    afficherChargement('tableau-utilisateurs');

    ajaxPost('controllers/adminController.php', { action: 'lister' })
    .then(data => {
        if (!data.succes) {
            afficherVide('tableau-utilisateurs', data.message);
            return;
        }
        afficherTableauUtilisateurs(data.data);
    })
    .catch(() => afficherVide('tableau-utilisateurs', 'Erreur de chargement.'));
}

// ── Afficher le tableau des utilisateurs ──
function afficherTableauUtilisateurs(utilisateurs) {
    const zone = document.getElementById('tableau-utilisateurs');

    if (utilisateurs.length === 0) {
        zone.innerHTML = '<p class="vide">Aucun utilisateur trouvé.</p>';
        return;
    }

    let html = `<table class="tableau">
        <thead><tr>
            <th>ID</th><th>Nom</th><th>Login</th>
            <th>Rôle</th><th>Statut</th><th>Actions</th>
        </tr></thead><tbody>`;

    utilisateurs.forEach(u => {
        html += `<tr>
            <td>${u.id}</td>
            <td>${u.nom}</td>
            <td>${u.login}</td>
            <td>
                <select class="select-role" data-id="${u.id}" onchange="changerRole(this)">
                    <option value="consultant"   ${u.role === 'consultant'   ? 'selected' : ''}>Consultant</option>
                    <option value="gestionnaire" ${u.role === 'gestionnaire' ? 'selected' : ''}>Gestionnaire</option>
                    <option value="admin"        ${u.role === 'admin'        ? 'selected' : ''}>Admin</option>
                </select>
            </td>
            <td><span class="badge badge-${u.statut}">${u.statut}</span></td>
            <td class="actions">
                ${u.statut === 'actif'
                    ? `<button class="btn btn-sm btn-danger"
                               onclick="desactiverUtilisateur('${u.id}', '${u.nom}')">
                           Désactiver
                       </button>`
                    : '<span class="texte-inactif">Inactif</span>'
                }
            </td>
        </tr>`;
    });

    html += '</tbody></table>';
    zone.innerHTML = html;
}

// ── Créer un utilisateur ──
function creerUtilisateur() {
    const erreurs = validerChamps([
        { id: 'u-nom',      label: 'Nom' },
        { id: 'u-login',    label: 'Login' },
        { id: 'u-password', label: 'Mot de passe' }
    ]);

    if (erreurs.length > 0) {
        afficherMessage('message-admin', erreurs.join(' | '), 'erreur');
        return;
    }

    const btn = document.getElementById('btn-creer-user');
    boutonChargement(btn, true, "Créer l'utilisateur", 'Création...');

    const donnees = {
        action  : 'ajouter',
        nom     : document.getElementById('u-nom').value.trim(),
        login   : document.getElementById('u-login').value.trim(),
        password: document.getElementById('u-password').value,
        role    : document.getElementById('u-role').value
    };

    ajaxPost('controllers/adminController.php', donnees)
    .then(data => {
        if (data.succes) {
            afficherMessage('message-admin', '✓ ' + data.message, 'succes');
            reinitialiserFormulaire();
            document.getElementById('form-ajout-user').classList.add('hidden');
            chargerUtilisateurs();
        } else {
            afficherMessage('message-admin', '✗ ' + data.message, 'erreur');
        }
        boutonChargement(btn, false, "Créer l'utilisateur");
    })
    .catch(() => {
        afficherMessage('message-admin', 'Erreur réseau.', 'erreur');
        boutonChargement(btn, false, "Créer l'utilisateur");
    });
}

// ── Changer le rôle d'un utilisateur ──
function changerRole(select) {
    const id   = select.dataset.id;
    const role = select.value;

    ajaxPost('controllers/adminController.php', {
        action        : 'modifierRole',
        id_utilisateur: id,
        role          : role
    })
    .then(data => {
        const type = data.succes ? 'succes' : 'erreur';
        afficherMessage('message-admin', data.succes ? '✓ ' + data.message : '✗ ' + data.message, type);
        if (!data.succes) chargerUtilisateurs(); // Recharger pour annuler le changement visuel
    });
}

// ── Désactiver un utilisateur ──
function desactiverUtilisateur(id, nom) {
    if (!confirmerAction('Désactiver le compte de "' + nom + '" ?')) return;

    ajaxPost('controllers/adminController.php', {
        action        : 'desactiver',
        id_utilisateur: id
    })
    .then(data => {
        const type = data.succes ? 'succes' : 'erreur';
        afficherMessage('message-admin', data.succes ? '✓ ' + data.message : '✗ ' + data.message, type);
        if (data.succes) chargerUtilisateurs();
    });
}

// ── Vider le formulaire ──
function reinitialiserFormulaire() {
    document.getElementById('u-nom').value      = '';
    document.getElementById('u-login').value    = '';
    document.getElementById('u-password').value = '';
    document.getElementById('u-role').value     = 'consultant';
}