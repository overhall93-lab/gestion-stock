<!-- ── Fin du contenu principal ── -->
</main>

<footer class="pied-page">
    <span>GestionStock Pro v<?= APP_VERSION ?></span>
    <span>Connecté en tant que <strong><?= htmlspecialchars($_SESSION['nom'] ?? '') ?></strong></span>
    <span id="horloge"></span>
</footer>

<!-- Scripts globaux -->
<script src="<?= getBaseUrl() ?>assets/js/utils.js"></script>

<script>
// ── Horloge en temps réel ──
function mettreAJourHorloge() {
    const maintenant = new Date();
    const h = String(maintenant.getHours()).padStart(2,'0');
    const m = String(maintenant.getMinutes()).padStart(2,'0');
    const s = String(maintenant.getSeconds()).padStart(2,'0');
    const el = document.getElementById('horloge');
    if (el) el.textContent = h + ':' + m + ':' + s;
}
mettreAJourHorloge();
setInterval(mettreAJourHorloge, 1000);
</script>

</body>
</html>