<?php
session_start();
// Security Check
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'instructor') {
    header('Location: ../account/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard</title>
    <style>
        /* --- INSTRUCTOR THEME (Green) --- */
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f5f6fa; color: #232b47; }
        
        /* Header */
        .header { padding: 20px 24px; background: #198754; color: #fff; font-size: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: #fff; font-size: 14px; text-decoration: none; opacity: 0.8; }
        .header a:hover { opacity: 1; }

        /* Layout */
        .container { display: flex; min-height: calc(100vh - 69px); }
        
        /* Sidebar - Right Side as per your original design, or Left? Let's stick to Left for consistency, 
           but if you prefer Right like the Management one, we can flip it. 
           Let's keep it Left to match Management for a clean UI suite. */
        .sidebar-right { width: 240px; background: #146c43; color: #fff; padding: 20px 10px; flex-shrink: 0; }
        .sidebar-right button {
            width: 100%; margin-bottom: 8px; padding: 12px 15px; 
            background: transparent; border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 6px; color: #e9ecef; font-size: 14px; 
            text-align: left; cursor: pointer; transition: 0.2s;
        }
        .sidebar-right button:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar-right button.active { background: #ffc107; color: #000; font-weight: bold; border-color: #ffc107; }

        /* Content */
        .content-zone { flex: 1; padding: 40px; overflow-y: auto; }

        /* Welcome Page Styles */
        .welcome-box { text-align: center; margin-top: 60px; }
        .welcome-box h1 { color: #198754; font-size: 2.5rem; margin-bottom: 10px; }
        .welcome-icon { font-size: 4rem; margin-top: 30px; opacity: 0.5; }
    </style>
</head>
<body>

    <div class="header">
        <div>University of Saint Louis - Instructor Panel</div>
        <div style="font-size: 14px;">
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['FNAME'] ?? 'Instructor'); ?></strong>
            &nbsp;|&nbsp; 
            <a href="../account/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar-right">
            <button onclick="loadZone('welcome.php', null)">Dashboard Home</button>
            <button onclick="loadZone('schedule.php', this)">My Schedule</button> 
            <button onclick="loadZone('grading-sheet-ajax.php', this)">My Class Loads</button>
        </div>

        <div class="content-zone" id="main-content">
            </div>
    </div>
    <script>

    function loadZone(url, btn) {
        if(btn) {
            document.querySelectorAll('.sidebar-right button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        console.log("Loading: " + url);
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error("File not found: " + url);
                return response.text();
            })
            .then(html => {
                if(html.includes('<title>Login</title>') || html.includes('name="accountid"')) {
                    window.location.href = '../account/login.php';
                    return;
                }
                document.getElementById('main-content').innerHTML = html;
                 // NEW: Initialize Grades if we loaded the grading sheet
                initGrades();
            })
            .catch(err => {
                document.getElementById('main-content').innerHTML = "<div style='color:red; padding:20px;'>Error loading content.</div>";
            });
    }
    function initGrades() {
        let rows = document.querySelectorAll('.student-row');
        if(rows.length > 0) {
            rows.forEach(row => {
                let q1 = row.querySelector('.q1');
                if(q1) calcRow(q1);
            });
        }
    }

    function calcRow(input) {
        let row = input.closest('tr');
        let g1 = row.querySelector('.q1').value;
        let g2 = row.querySelector('.q2').value;
        let g3 = row.querySelector('.q3').value;
        let g4 = row.querySelector('.q4').value;
        
        let finalCell = row.querySelector('.final-grade');
        let remarkCell = row.querySelector('.remarks');

        if(g1 && g2 && g3 && g4 && !isNaN(g1) && !isNaN(g2) && !isNaN(g3) && !isNaN(g4)) {
            let avg = (parseFloat(g1) + parseFloat(g2) + parseFloat(g3) + parseFloat(g4)) / 4;
            let final = avg.toFixed(2);
            
            finalCell.innerText = final;
            if(avg >= 75) {
                remarkCell.innerHTML = "<span class='result-pass'>PASSED</span>";
            } else {
                remarkCell.innerHTML = "<span class='result-fail'>FAILED</span>";
            }
        } else {
            finalCell.innerText = "-";
            remarkCell.innerText = "-";
        }
    }

    function enableManual(q, btn) {
        closeBulk();
        document.querySelectorAll('.ctrl-btn').forEach(b => b.classList.remove('ctrl-active'));
        btn.classList.add('ctrl-active');
        document.querySelectorAll('.grade-input').forEach(i => i.readOnly = true);
        document.querySelectorAll('.q' + q).forEach(input => { input.readOnly = false; });
    }

    let activeBulkQuarter = 0;
    function enableBulk(q, btn) {
        document.querySelectorAll('.ctrl-btn').forEach(b => b.classList.remove('ctrl-active'));
        btn.classList.add('ctrl-active');
        document.getElementById('bulk-container').style.display = 'flex';
        document.getElementById('bulk-q-label').innerText = q;
        document.getElementById('bulk-input').value = "";
        document.getElementById('bulk-input').focus();
        activeBulkQuarter = q;
    }

    function applyBulk() {
        let raw = document.getElementById('bulk-input').value.trim();
        if(!raw) return;
        let grades = raw.split(/\s+/);
        let inputs = document.querySelectorAll('.q' + activeBulkQuarter);
        inputs.forEach((input, index) => {
            if(grades[index] !== undefined) {
                input.value = grades[index];
                calcRow(input);
            }
        });
        closeBulk();
    }

    function closeBulk() {
        document.getElementById('bulk-container').style.display = 'none';
        activeBulkQuarter = 0;
    }

    function saveGrades() {
        var form = document.getElementById('gradingForm');
        var status = document.getElementById('save_status');
        var formData = new FormData(form);
        
        var sec = document.getElementById('hidden_sec_name').value;
        var code = document.getElementById('hidden_subj_code').value;

        status.style.display = 'inline';
        status.style.color = '#666';
        status.innerHTML = 'Saving...';

        fetch('section-grades.php?section=' + encodeURIComponent(sec) + '&code=' + encodeURIComponent(code), {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if(data.includes("SAVED")) {
                status.style.color = '#198754';
                status.innerHTML = '✅ Grades Saved Successfully!';
                setTimeout(() => { status.innerHTML = ""; }, 3000);
            } else {
                alert("Error saving grades.");
                console.error(data);
            }
        })
        .catch(err => {
            alert("Network Error");
        });
    }

    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>