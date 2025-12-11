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
    <link rel="stylesheet" href="dashboard.css?v=8">
</head>
<body>

    <div class="header">
        <div style="display: flex; align-items: center;">
            <img src="../assets/slu.png" alt="School Logo" style="height: 50px; margin-right: 15px;">
            <span style="font-weight: bold; font-size: 1.2rem;">Saint Louis School of Pacdal, Inc. (Management)</span>
        </div>
        <div style="font-size: 14px;">
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['FNAME'] ?? 'Manager'); ?></strong>
            &nbsp;|&nbsp; 
            <a href="../account/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar-right">
            <button onclick="loadZone('welcome.php', null)">Dashboard Home</button>
            <button onclick="loadZone('enroll-student-ajax.php', this)">Register New Student</button>
            <button onclick="loadZone('re_enroll.php', this)">Re-enrollment / Promotion</button>
            <button onclick="loadZone('billing.php', this)">Student Accounts</button>
            <button onclick="loadZone('student_records.php', this)">Student Records</button>
            <button onclick="loadZone('honor_list.php', this)">Honor List & Ranking</button>
        </div>

        <div class="content-zone" id="main-content">
            </div>
    </div>

    <script>
    // 1. CORE FUNCTIONS
    // Updated loadZone function with cache busting
function loadZone(url, btn) {
    if(btn) {
        document.querySelectorAll('.sidebar-right button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    
    // Add cache busting parameter
    const separator = url.includes('?') ? '&' : '?';
    const cacheBuster = '_=' + Date.now();
    const finalUrl = url + separator + cacheBuster;
    
    fetch(finalUrl)
        .then(response => {
            if (!response.ok) throw new Error("HTTP " + response.status);
            return response.text();
        })
        .then(html => {
            if(html.includes('<title>Login</title>')) {
                window.location.href = '../account/login.php';
                return;
            }
            document.getElementById('main-content').innerHTML = html;
            
            // Auto-trigger logic for specific pages
            if(url.includes('instructors.php')) filterFaculty();
        })
        .catch(err => {
            document.getElementById('main-content').innerHTML = "<div style='color:red; padding:20px;'>Error loading content.</div>";
        });
    }

    function submitForm(formElement, url) {
        let formData = new FormData(formElement);
        let btn = formElement.querySelector('button[type="submit"]');
        if(btn && btn.name) formData.append(btn.name, btn.value);
        
        if(btn) { btn.disabled = true; btn.innerText = "Processing..."; }

        fetch(url, { method: 'POST', body: formData })
        .then(response => response.text())
        .then(responseHTML => {
            document.getElementById('main-content').innerHTML = responseHTML;
            let scripts = document.getElementById('main-content').querySelectorAll("script");
            scripts.forEach(script => { try { eval(script.innerHTML); } catch(e) {} });
        })
        .catch(err => {
            alert("Error: " + err);
            if(btn) { btn.disabled = false; btn.innerText = "Retry"; }
        });
    }

    // 2. RE-ENROLLMENT LOGIC (The Missing Piece)
    // 2. RE-ENROLLMENT LOGIC (Fixed for Semester Support)
    function promoteStudent(id, nextLevel, nextSem, isRepeater) {
        let verb = isRepeater ? "RETAIN" : "PROMOTE";
        
        if(confirm("Confirm Action: " + verb + " student to " + nextLevel + " (" + nextSem + ")?")) {
            let fd = new FormData();
            fd.append('promote_id', id);
            fd.append('next_level', nextLevel);
            fd.append('next_sem', nextSem); // This was missing
            fd.append('is_repeater', isRepeater ? 1 : 0);

            // Reload context
            let levelSelect = document.querySelector('select[name="level"]');
            let currentLevel = levelSelect ? levelSelect.value : '';
            let currentUrl = 're_enroll.php?level=' + encodeURIComponent(currentLevel);

            fetch('re_enroll.php', { method: 'POST', body: fd })
            .then(res => res.text())
            .then(data => {
                if(data.includes("SUCCESS")) {
                    let parts = data.split("|");
                    alert(parts[1] || "Action Successful!");
                    loadZone(currentUrl); 
                } else {
                    alert("Error processing: " + data);
                }
            });
        }
    }

    // 3. REGISTRATION LOGIC
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

    function updateRegisterGrade() {
        var track = document.getElementById('reg_track').value;
        var levelSelect = document.getElementById('reg_level');
        
        levelSelect.value = "";
        var opts = levelSelect.getElementsByClassName("opt-lvl");
        for(var i=0; i<opts.length; i++) opts[i].style.display = "none";

        if(track == 'kinder') {
            var show = levelSelect.getElementsByClassName("opt-kinder");
            for(var i=0; i<show.length; i++) show[i].style.display = "block";
        } else if(track == 'junior high school') {
            var show = levelSelect.getElementsByClassName("opt-jhs");
            for(var i=0; i<show.length; i++) show[i].style.display = "block";
            if(document.getElementById('esc_box')) document.getElementById('esc_box').style.display = 'block';
        } else {
            var show = levelSelect.getElementsByClassName("opt-shs");
            for(var i=0; i<show.length; i++) show[i].style.display = "block";
            if(document.getElementById('esc_box')) document.getElementById('esc_box').style.display = 'none';
        }
    }

    // 4. BILLING LOGIC
    function checkBillingID() {
        var id = document.getElementById('bill_search').value;
        var statusDiv = document.getElementById('bill_check_status');
        var btn = document.getElementById('btn_bill_search');
        if(id.trim() == "") { statusDiv.innerHTML = ""; return; }
        var fd = new FormData(); fd.append('student_id', id);
        fetch('get_student_info.php', { method: 'POST', body: fd }).then(res => res.text()).then(data => {
            if(data.includes("|")) {
                var parts = data.split("|");
                statusDiv.style.color = "#198754"; statusDiv.innerHTML = "✅ Found: " + parts[1];
                btn.disabled = false; btn.style.opacity = "1";
            } else {
                statusDiv.style.color = "#dc3545"; statusDiv.innerHTML = "❌ Not Found";
                btn.disabled = true; btn.style.opacity = "0.5";
            }
        });
    }

    function loadBilling() {
        var id = document.getElementById('bill_search').value;
        var fd = new FormData(); fd.append('student_id', id);
        fetch('get_billing_info.php', { method: 'POST', body: fd }).then(res => res.text()).then(data => {
            if(data.includes("||")) {
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
            }
        });
    }
    function updatePeriodOptions() {
        var levelSelect = document.getElementById('hl_level');
        var periodSelect = document.getElementById('hl_period');
        var level = levelSelect.value;
        
        // Define Options
        var jhsOptions = [
            {v:'1', t:'1st Quarter'}, {v:'2', t:'2nd Quarter'},
            {v:'3', t:'3rd Quarter'}, {v:'4', t:'4th Quarter'},
            {v:'5', t:'General Average (Final)'}
        ];
        
        var shsOptions = [
            {v:'1st Sem', t:'1st Semester'}, {v:'2nd Sem', t:'2nd Semester'}
        ];

        // Determine Set
        var optionsToUse = [];
        if (level === 'Grade 11' || level === 'Grade 12') {
            optionsToUse = shsOptions;
        } else if (level === "") {
            optionsToUse = [{v:'', t:'-- Select Level First --'}];
        } else {
            optionsToUse = jhsOptions;
        }

        // Rebuild Dropdown
        periodSelect.innerHTML = "";
        for (var i = 0; i < optionsToUse.length; i++) {
            var opt = document.createElement('option');
            opt.value = optionsToUse[i].v;
            opt.text = optionsToUse[i].t;
            periodSelect.add(opt);
        }
    }
    function openPDF() {
        var id = document.getElementById('bill_search').value;
        if(id && id.trim() !== "") {
            window.open('print_billing.php?student_id=' + id, '_blank');
        } else {
            alert("Please search for a student first.");
        }
    }

    // Default Load
    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>