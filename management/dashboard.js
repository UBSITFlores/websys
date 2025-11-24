function loadZone(url, btn) {
    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById('main-content').innerHTML = html;
            document.querySelectorAll('.sidebar-left button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            // No need for AJAX handler here.
        })
        .catch(() => {
            document.getElementById('main-content').innerHTML =
                "<div style='color:red;'>Error loading content.</div>";
        });
}

window.onload = function() {
    loadZone('enroll_student.php', document.getElementById('btn-enroll'));
};
