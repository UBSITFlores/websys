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
                
                // If we loaded the manage accounts page, trigger an initial search to fill the table
                if(url.includes('manage_accounts.php')) {
                    liveSearch();
                }
            })
            .catch(err => {
                document.getElementById('main-content').innerHTML = "<div style='color:red; padding:20px;'>Error loading content.</div>";
            });
    }

    // 2. SUBMIT FORMS (For Create/Edit User)
    function submitForm(formElement, url) {
        let formData = new FormData(formElement);
        let btn = formElement.querySelector('button[type="submit"]');
        if(btn && btn.name) formData.append(btn.name, btn.value);

        document.getElementById('main-content').innerHTML = "<div style='text-align:center; padding:50px;'>Processing...</div>";

        fetch(url, { method: 'POST', body: formData })
        .then(response => response.text())
        .then(responseHTML => {
            document.getElementById('main-content').innerHTML = responseHTML;
            // Re-trigger search if we just saved on the manage page
            if(url.includes('manage_accounts.php')) liveSearch();
        });
    }

    // 3. LIVE SEARCH (For Manage Accounts)
    function liveSearch() {
        // Check if elements exist (they might not if we are on a different page)
        let roleSelect = document.getElementById('search_role');
        let textInput = document.getElementById('search_text');
        let tableBody = document.getElementById('account_table_body');

        if(!roleSelect || !textInput || !tableBody) return;

        let r = roleSelect.value;
        let s = textInput.value;
        
        // Fetch ONLY the rows
        fetch('manage_accounts.php?ajax_search=1&role=' + r + '&search=' + encodeURIComponent(s))
        .then(res => res.text())
        .then(html => {
            tableBody.innerHTML = html;
        });
    }

    // 4. DELETE USER
    function deleteUser(id) {
        if(confirm('Are you sure? This cannot be undone.')) {
            let fd = new FormData();
            fd.append('delete_id', id);
            
            fetch('manage_accounts.php', { method: 'POST', body: fd })
            .then(res => res.text())
            .then(html => {
                // Refresh list
                liveSearch(); 
            });
        }
    }

    // Default Load
    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>