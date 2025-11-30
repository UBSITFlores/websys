function loadZone(url, btn) {
    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById('main-content').innerHTML = html;
            document.querySelectorAll('.sidebar-right button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        })
        .catch(() => {
            document.getElementById('main-content').innerHTML = "<div style='color:red;'>Error loading content.</div>";
        });
}
window.onload = function() {
    loadZone('grading-sheet-ajax.php', document.getElementById('btn-grading'));
};
