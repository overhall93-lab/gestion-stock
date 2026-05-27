<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — GestionStock Pro</title>
    <style>
        /* ── Reset et base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bleu:       #1a56db;
            --bleu-dark:  #1e429f;
            --bleu-light: #e8f0fe;
            --gris-bg:    #f3f4f6;
            --gris-bord:  #d1d5db;
            --texte:      #111827;
            --texte-soft: #6b7280;
            --rouge:      #dc2626;
            --vert:       #16a34a;
            --blanc:      #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gris-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── Carte de connexion ── */
        .login-card {
            background: var(--blanc);
            border-radius: 12px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }

        /* ── En-tête ── */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo {
            width: 56px;
            height: 56px;
            background: var(--bleu);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .login-logo svg { width: 28px; height: 28px; fill: white; }

        .login-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--texte);
        }

        .login-header p {
            font-size: 14px;
            color: var(--texte-soft);
            margin-top: 6px;
        }

        /* ── Formulaire ── */
        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--texte);
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--gris-bord);
            border-radius: 8px;
            font-size: 15px;
            color: var(--texte);
            background: var(--blanc);
            transition: border-color 0.2s;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--bleu);
            box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
        }

        /* ── Bouton ── */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--bleu);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-login:hover  { background: var(--bleu-dark); }
        .btn-login:active { transform: scale(0.99); }
        .btn-login:disabled { opacity: 0.6; cursor: not-allowed; }

        /* ── Messages ── */
        .alerte {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            display: none;
        }
        .alerte.erreur  { background: #fee2e2; color: var(--rouge); border: 1px solid #fca5a5; }
        .alerte.succes  { background: #dcfce7; color: var(--vert);  border: 1px solid #86efac; }
        .alerte.visible { display: block; }

        /* ── Expiration ── */
        .session-expiration {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">

    <div class="login-header">
        <div class="login-logo">
            <!-- Icône boîte / stock -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1zM3 5h18a1 1 0 0 0 0-2H3a1 1 0 0 0 0 2zm7 7h4v2h-4v-2z"/>
            </svg>
        </div>
        <h1>GestionStock Pro</h1>
        <p>Connectez-vous pour accéder à l'application</p>
    </div>

    <!-- Message expiration session -->
    <?php if (isset($_GET['expiration'])): ?>
    <div class="session-expiration">
        ⏱ Votre session a expiré. Veuillez vous reconnecter.
    </div>
    <?php endif; ?>

    <!-- Message d'erreur / succès dynamique -->
    <div class="alerte" id="msg-alerte"></div>

    <!-- Formulaire -->
    <form id="form-login">
        <div class="form-group">
            <label for="login">Identifiant</label>
            <input type="text" id="login" name="login"
                   placeholder="Votre identifiant"
                   autocomplete="username"
                   required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password"
                   placeholder="Votre mot de passe"
                   autocomplete="current-password"
                   required>
        </div>

        <button type="submit" class="btn-login" id="btn-submit">
            Se connecter
        </button>
    </form>

</div>

<script>
// ── Gestion du formulaire de login via AJAX ──
document.getElementById('form-login').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn    = document.getElementById('btn-submit');
    const alerte = document.getElementById('msg-alerte');
    const login  = document.getElementById('login').value.trim();
    const mdp    = document.getElementById('password').value;

    // Masquer le message précédent
    alerte.className = 'alerte';
    alerte.textContent = '';

    // Validation rapide côté client
    if (!login || !mdp) {
        afficherMessage('Veuillez remplir tous les champs.', 'erreur');
        return;
    }

    // Désactiver le bouton pendant l'envoi
    btn.disabled = true;
    btn.textContent = 'Connexion en cours…';

    // Envoi AJAX vers authController
    fetch('controllers/authController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'login', login: login, password: mdp })
    })
    .then(res => {
        if (!res.ok) throw new Error('Erreur serveur : ' + res.status);
        return res.json();
    })
    .then(data => {
        if (data.succes) {
            afficherMessage('Connexion réussie. Redirection…', 'succes');
            setTimeout(() => { window.location.href = 'views/dashboard.php'; }, 800);
        } else {
            afficherMessage(data.message || 'Identifiants incorrects.', 'erreur');
            btn.disabled = false;
            btn.textContent = 'Se connecter';
            document.getElementById('password').value = '';
        }
    })
    .catch(() => {
        afficherMessage('Impossible de contacter le serveur. Vérifiez XAMPP.', 'erreur');
        btn.disabled = false;
        btn.textContent = 'Se connecter';
    });
});

function afficherMessage(texte, type) {
    const alerte = document.getElementById('msg-alerte');
    alerte.textContent = texte;
    alerte.className = 'alerte ' + type + ' visible';
}

// Focus automatique sur le champ login
document.getElementById('login').focus();
</script>

</body>
</html>