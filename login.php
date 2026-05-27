<?php
define('GESTION_STOCK', true);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/session.php';

// Si déjà connecté, rediriger vers le dashboard
if (!empty($_SESSION['connecte'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?= APP_NOM ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-login">

<div class="login-wrapper">
    <div class="login-box">

        <div class="login-header">
            <h1><?= APP_NOM ?></h1>
            <p>Gestion de stock Informatique &amp; Électronique</p>
        </div>

        <div id="message-login" class="message hidden"></div>

        <div class="form-group">
            <label for="login">Identifiant</label>
            <input type="text" id="login" name="login"
                   placeholder="Votre identifiant" autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password"
                   placeholder="Votre mot de passe" autocomplete="current-password">
        </div>

        <button id="btn-login" class="btn btn-primary btn-full">
            Se connecter
        </button>

        <p class="login-footer">
            Version <?= APP_VERSION ?> &mdash; Accès réservé au personnel autorisé
        </p>

    </div>
</div>

<script>
document.getElementById('btn-login').addEventListener('click', function () {
    const login    = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value;
    const msg      = document.getElementById('message-login');
    const btn      = this;

    if (!login || !password) {
        afficherMessage(msg, 'Veuillez remplir tous les champs.', 'erreur');
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Connexion...';

    fetch('controllers/authController.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ action: 'login', login, password })
    })
    .then(r => r.json())
    .then(data => {
        if (data.succes) {
            afficherMessage(msg, 'Connexion réussie. Redirection...', 'succes');
            setTimeout(() => window.location.href = data.redirect, 800);
        } else {
            afficherMessage(msg, data.message, 'erreur');
            btn.disabled    = false;
            btn.textContent = 'Se connecter';
        }
    })
    .catch(() => {
        afficherMessage(msg, 'Erreur réseau. Vérifiez votre connexion.', 'erreur');
        btn.disabled    = false;
        btn.textContent = 'Se connecter';
    });
});

// Permettre la touche Entrée pour valider
document.getElementById('password').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') document.getElementById('btn-login').click();
});

function afficherMessage(el, texte, type) {
    el.textContent  = texte;
    el.className    = 'message ' + type;
}
</script>

</body>
</html>