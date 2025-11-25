<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'admin') {
    header('Location: ../account/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

    <div class="header">
        <div>University of Saint Louis - Admin</div>
        <div style="font-size: 14px;">
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['FNAME'] ?? 'Admin'); ?></strong>
            &nbsp;|&nbsp; 
            <a href="../account/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar-right">
            <button onclick="loadZone('welcome.php', null)">Dashboard Home</button>
            <button onclick="loadZone('instructors.php', this)">Faculty List</button> 
            <button onclick="loadZone('manage_accounts.php', this)">Manage Accounts</button>
            <button onclick="loadZone('add_user.php', this)">Create New User</button>
        </div>

        <div class="content-zone" id="main-content">
            </div>
    </div>

    <script>
    // 1. LOAD CONTENT
    function loadZone(url, btn) {
        if(btn) {
            document.querySelectorAll('.sidebar-right button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        console.log("Loading: " + url);
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error("File not found");
                return response.text();
            })
            .then(html => {
                if(html.includes('<title>Login</title>')) {
                    window.location.href = '../account/login.php';
                    return;
                }
                document.getElementById('main-content').innerHTML = html;
                
                // REMOVE THE liveSearch() TRIGGER HERE
                // Only auto-load for faculty list if you want, or remove that too.
                if(url.includes('instructors.php')) {
                    filterFaculty();
                }
            })
            .catch(err => {
                document.getElementById('main-content').innerHTML = "<div style='color:red; padding:20px;'>Error loading content.</div>";
            });
    }

    // 2. SUBMIT FORMS (Generic Save)
    function submitForm(formElement, url) {
        let formData = new FormData(formElement);
        let btn = formElement.querySelector('button[type="submit"]');
        if(btn && btn.name) formData.append(btn.name, btn.value);

        // We don't wipe the content here because inline edits (like in faculty list) should just refresh the table
        // but for full page forms, we might want a loader. 
        // For this specific case, we will rely on the fetch callback to refresh the specific list.

        fetch(url, { method: 'POST', body: formData })
        .then(response => response.text())
        .then(responseHTML => {
            // Check if the response asks to reload a list
            if(responseHTML.includes('filterFaculty();')) {
                // Execute the alert inside the response
                let tempDiv = document.createElement('div');
                tempDiv.innerHTML = responseHTML;
                let scripts = tempDiv.querySelectorAll("script");
                scripts.forEach(script => { try { eval(script.innerHTML); } catch(e) {} });
            } else {
                // Normal page reload behavior
                document.getElementById('main-content').innerHTML = responseHTML;
            }
        });
    }

    // 3. MANAGE ACCOUNTS LOGIC
    
    // New: Button Click Handler
    function setRoleFilter(role, btn) {
        // 1. Update Hidden Input
        document.getElementById('search_role').value = role;
        
        // 2. Update Visuals (Active Class)
        let buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // 3. Trigger Search
        liveSearch();
    }

    function liveSearch() {
        let roleInput = document.getElementById('search_role');
        let textInput = document.getElementById('search_text');
        let tableBody = document.getElementById('account_table_body');

        if(!roleInput || !textInput || !tableBody) return;

        let r = roleInput.value; // Reads from hidden input now
        let s = textInput.value;
        
        fetch('manage_accounts.php?ajax_search=1&role=' + r + '&search=' + encodeURIComponent(s))
        .then(res => res.text())
        .then(html => { tableBody.innerHTML = html; });
    }

    function deleteUser(id) {
        if(confirm('Are you sure? This cannot be undone.')) {
            let fd = new FormData();
            fd.append('delete_id', id);
            fetch('manage_accounts.php', { method: 'POST', body: fd })
            .then(res => res.text())
            .then(html => { liveSearch(); });
        }
    }

    // 4. FACULTY LIST LOGIC (New!)
    function filterFaculty() {
        var t = document.getElementById('f_track').value;
        var d = document.getElementById('f_degree').value;
        var s = document.getElementById('f_status').value;
        var tableBody = document.getElementById('faculty_table_body');

        if(!tableBody) return;

        // Show loading state
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#666;">Loading...</td></tr>';

        fetch('instructors.php?ajax_search=1&track=' + encodeURIComponent(t) + '&degree=' + encodeURIComponent(d) + '&status=' + encodeURIComponent(s))
        .then(res => res.text())
        .then(html => {
            tableBody.innerHTML = html;
        });
    }

    // Default Load
    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>