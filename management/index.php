<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    header('Location: ../account/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Management Dashboard</title>
    
    <link rel="stylesheet" href="dashboard.css?v=6">
    
</head>
<body>

    <div class="header">
        <div>University of Saint Louis - Management</div>
        <div style="font-size: 14px;">
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['FNAME'] ?? 'Manager'); ?></strong>
            &nbsp;|&nbsp; 
            <a href="../account/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar-right">
            <button onclick="loadZone('welcome.php', null)">Dashboard Home</button>
            
            <button onclick="loadZone('enroll-student-ajax.php', this)">Register Student</button>
            
            <button onclick="loadZone('billing.php', this)">Student Accounts</button>
        </div>

        <div class="content-zone" id="main-content">
            </div>
    </div>

    <script>
    // ==========================================
    // 1. CORE DASHBOARD FUNCTIONS
    // ==========================================
    function loadZone(url, btn) {
        if(btn) {
            document.querySelectorAll('.sidebar-right button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        console.log("Loading: " + url);
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error("HTTP " + response.status);
                return response.text();
            })
            .then(html => {
                if(html.includes('<title>Login</title>') || html.includes('name="accountid"')) {
                    window.location.href = '../account/login.php';
                    return;
                }
                document.getElementById('main-content').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('main-content').innerHTML = "<div style='color:red; padding:20px;'>Error loading content.</div>";
            });
    }

    function submitForm(formElement, url) {
        let formData = new FormData(formElement);
        let btn = formElement.querySelector('button[type="submit"]');
        if(btn && btn.name) formData.append(btn.name, btn.value);

        document.getElementById('main-content').innerHTML = "<div style='text-align:center; padding:50px;'>Processing...</div>";

        fetch(url, { method: 'POST', body: formData })
        .then(response => response.text())
        .then(responseHTML => {
            document.getElementById('main-content').innerHTML = responseHTML;
            let scripts = document.getElementById('main-content').querySelectorAll("script");
            scripts.forEach(script => { try { eval(script.innerHTML); } catch(e) {} });
        });
    }

    // ==========================================
    // 2. REGISTRATION LOGIC
    // ==========================================
    
    // A. Update Grade Levels based on Track
    function updateRegisterGrade() {
        var track = document.getElementById('reg_track').value;
        var levelSelect = document.getElementById('reg_level');
        
        levelSelect.value = "";
        
        var opts = levelSelect.getElementsByClassName("opt-lvl");
        for(var i=0; i<opts.length; i++) {
            opts[i].style.display = "none";
        }

        if(track == 'kinder') {
            var show = levelSelect.getElementsByClassName("opt-kinder");
            for(var i=0; i<show.length; i++) show[i].style.display = "block";
        } 
        else if(track == 'junior high school') {
            var show = levelSelect.getElementsByClassName("opt-jhs");
            for(var i=0; i<show.length; i++) show[i].style.display = "block";
            if(document.getElementById('esc_box')) document.getElementById('esc_box').style.display = 'block';
        }
        else if(track == 'senior high school') {
            var show = levelSelect.getElementsByClassName("opt-shs");
            for(var i=0; i<show.length; i++) show[i].style.display = "block";
            if(document.getElementById('esc_box')) document.getElementById('esc_box').style.display = 'none';
        }
    }

    // B. Fetch Sections based on Track/Year
    function fetchSections() {
        var track = document.getElementById('reg_track').value;
        var year = document.getElementById('reg_level').value;
        var secSelect = document.getElementById('reg_section');

        if(track == "" || year == "") return;

        secSelect.innerHTML = "<option>Loading...</option>";
        
        var fd = new FormData();
        fd.append('track', track);
        fd.append('year_level', year);

        fetch('get_sections.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            secSelect.innerHTML = data;
        });
    }

    // ==========================================
    // 3. BILLING LOGIC
    // ==========================================
    
    // A. Check Student ID Real-time
    function checkBillingID() {
        var id = document.getElementById('bill_search').value;
        var statusDiv = document.getElementById('bill_check_status');
        var btn = document.getElementById('btn_bill_search');

        if(id.trim() == "") {
            statusDiv.innerHTML = "";
            return;
        }

        var fd = new FormData();
        fd.append('student_id', id);

        fetch('get_student_info.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            if(data.includes("|")) {
                var parts = data.split("|");
                statusDiv.style.color = "#198754";
                statusDiv.innerHTML = "✅ Found: " + parts[1];
                btn.disabled = false;
                btn.style.opacity = "1";
            } else {
                statusDiv.style.color = "#dc3545";
                statusDiv.innerHTML = "❌ Student not found";
                btn.disabled = true;
                btn.style.opacity = "0.5";
            }
        });
    }

    // B. Load Billing Dashboard
    function loadBilling() {
        var id = document.getElementById('bill_search').value;
        if(!id) { alert("Please enter a Student ID"); return; }

        var fd = new FormData();
        fd.append('student_id', id);

        var btn = document.querySelector('#bill_search + button');
        var originalText = btn.innerText;
        btn.innerText = "Searching...";

        fetch('get_billing_info.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            btn.innerText = originalText;
            
            if(data.trim() == "NOT_FOUND") {
                alert("Student ID not found.");
                document.getElementById('billing_dashboard').style.display = 'none';
            } 
            else if(data.includes("||")) {
                var parts = data.split("||");
                
                document.getElementById('lbl_name').innerText = parts[0];
                document.getElementById('lbl_track').innerText = parts[1];
                document.getElementById('val_total').innerText = parts[2];
                document.getElementById('val_paid').innerText  = parts[3];
                document.getElementById('val_balance').innerText = parts[4];
                document.getElementById('history_table').innerHTML = parts[5];
                document.getElementById('lbl_status').innerHTML = parts[6];
                
                document.querySelectorAll('.target_sid').forEach(el => el.value = parts[7]);

                document.getElementById('billing_dashboard').style.display = 'block';
            } else {
                alert("Error fetching data.");
                console.log(data);
            }
        })
        .catch(err => {
            btn.innerText = originalText;
            console.error(err);
        });
    }

    // Default Load
    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>