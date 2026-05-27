<div class="page-header">
    <h2>👥 Gestion des utilisateurs</h2>
    <button id="btn-ouvrir-form" class="btn btn-primary">+ Ajouter un utilisateur</button>
</div>

<div id="message-admin" class="message hidden"></div>

<!-- Formulaire ajout (masqué par défaut) -->
<div class="section-card hidden" id="form-ajout-user">
    <h3>Nouvel utilisateur</h3>
    <div class="form-container">
        <div class="form-row">
            <div class="form-group">
                <label>Nom complet *</label>
                <input type="text" id="u-nom" placeholder="Ex: Jean Dupont">
            </div>
            <div class="form-group">
                <label>Login *</label>
                <input type="text" id="u-login" placeholder="Ex: jdupont">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Mot de passe *</label>
                <input type="password" id="u-password" placeholder="Min. 6 caractères">
            </div>
            <div class="form-group">
                <label>Rôle *</label>
                <select id="u-role">
                    <option value="consultant">Consultant</option>
                    <option value="gestionnaire">Gestionnaire</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button id="btn-creer-user" class="btn btn-primary">Créer l'utilisateur</button>
            <button id="btn-annuler-form" class="btn btn-secondary">Annuler</button>
        </div>
    </div>
</div>

<!-- Tableau des utilisateurs -->
<div class="section-card">
    <div id="tableau-utilisateurs">
        <p class="chargement">Chargement...</p>
    </div>
</div>

<script src="assets/js/admin.js"></script>  