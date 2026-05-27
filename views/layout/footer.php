<footer class="app-footer">
    <p><?= APP_NOM ?> &mdash; <?= date('Y') ?> &mdash; Tous droits réservés</p>
</footer>

<script>
// Toggle menu sidebar sur mobile
const btnToggle = document.getElementById('btn-menu-toggle');
const sidebar   = document.getElementById('sidebar');
if (btnToggle && sidebar) {
    btnToggle.addEventListener('click', function () {
        sidebar.classList.toggle('sidebar-ouverte');
    });
}
</script>