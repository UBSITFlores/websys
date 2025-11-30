<?php
session_start();
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
    <link rel="stylesheet" href="dashboard.css?v=4">
    <link rel="stylesheet" href="grading-sheet.css">
    <link rel="stylesheet" href="schedule.css">
    <link rel="stylesheet" href="section-grades.css">
    <link rel="stylesheet" href="attendance.css">
    <link rel="stylesheet" href="advisory.css">
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
            <button onclick="loadZone('advisory.php', this)">My Advisory Class</button> </div>

        <div class="content-zone" id="main-content">
            </div>
    </div>

    <script>
    // 1. CORE NAVIGATION
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
                if(html.includes('<title>Login</title>')) {
                    window.location.href = '../account/login.php';
                    return;
                }
                document.getElementById('main-content').innerHTML = html;
                initGrades(); // For grading page
            })
            .catch(err => {
                document.getElementById('main-content').innerHTML = "<div style='color:red; padding:20px;'>Error loading content.</div>";
            });
    }

    // 2. ATTENDANCE LOGIC (New!)
    function changeMonth() {
        let m = document.getElementById('month_picker').value;
        let sec = document.getElementById('att_sec_name').value;
        let code = document.getElementById('att_sub_code').value;
        
        // Reload the page with the new month
        loadZone('attendance.php?section=' + encodeURIComponent(sec) + '&code=' + encodeURIComponent(code) + '&month=' + m);
    }

    function saveAttendance() {
        let form = document.getElementById('attForm');
        let fd = new FormData(form);
        let msg = document.getElementById('save_msg');
        
        // Get current context from hidden inputs
        let sec = document.getElementById('att_sec_name').value;
        let code = document.getElementById('att_sub_code').value;
        let month = document.getElementById('month_picker').value;

        msg.innerText = "Saving...";
        
        // Post to the same URL structure to ensure logic runs
        let url = 'attendance.php?section=' + encodeURIComponent(sec) + '&code=' + encodeURIComponent(code) + '&month=' + month;

        fetch(url, { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            if(data.includes("SAVED")) {
                msg.style.color = "green";
                msg.innerText = "✅ Saved!";
                setTimeout(() => { msg.innerText = ""; }, 2000);
            } else {
                alert("Error saving. Check console.");
                console.error(data);
            }
        });
    }

    // 3. GRADING LOGIC (Existing)
    function initGrades() {
        let rows = document.querySelectorAll('.student-row');
        if(rows.length > 0) {
            rows.forEach(row => {
                let q1 = row.querySelector('.score-val');
                if(q1) calcRow(q1);
            });
        }
    }

    function calcRow(input) {
        var row = input.closest('tr');
        // This works for both 3 columns and 4 columns automatically
        var inputs = row.querySelectorAll('.score-val');
        
        var total = 0;
        var count = 0;
        var filled = 0;

        // Basic Loop
        for(var i = 0; i < inputs.length; i++) {
            var val = inputs[i].value;
            if(val != "" && !isNaN(val)) {
                total = total + parseFloat(val);
                filled++;
            }
            count++;
        }

        var finalCell = row.querySelector('.final-grade');
        var remarkCell = row.querySelector('.remarks');

        // Show average if at least one grade is entered (or wait for all? let's show current avg)
        if(filled > 0) {
            // In real school systems, blank usually means 0, but for display we average what is there
            // OR strict mode: total / count (dividing by 3 or 4 even if some are empty)
            // Let's use strict mode:
            
            var avg = total / count;
            var final = avg.toFixed(2);
            
            finalCell.innerText = final;
            
            if(avg >= 75) {
                remarkCell.innerHTML = "<span style='color:green; font-weight:bold;'>PASSED</span>";
            } else {
                remarkCell.innerHTML = "<span style='color:red; font-weight:bold;'>FAILED</span>";
            }
        } else {
            finalCell.innerText = "-";
            remarkCell.innerText = "-";
        }
    }

    function switchMode(mode, btn) {
        let tabs = document.querySelectorAll('.mode-btn');
        tabs.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        let acadCols = document.querySelectorAll('.col-acad');
        let behavCols = document.querySelectorAll('.col-behav');

        if(mode === 'academic') {
            acadCols.forEach(el => el.style.display = '');
            behavCols.forEach(el => el.style.display = 'none');
        } else {
            acadCols.forEach(el => el.style.display = 'none');
            behavCols.forEach(el => el.style.display = 'table-cell');
            document.querySelectorAll('div.col-behav').forEach(el => el.style.display = 'block');
            document.querySelectorAll('tr.col-behav').forEach(el => el.style.display = 'table-row');
        }
    }

    function enableManual(type, q) {
        document.querySelectorAll('.grade-input').forEach(i => i.readOnly = true);
        if(type === 'acad') {
            document.querySelectorAll('.col-acad.q' + q).forEach(input => input.readOnly = false);
        } else {
            document.querySelectorAll('.b-att.q' + q).forEach(input => input.readOnly = false);
            document.querySelectorAll('.b-con.q' + q).forEach(input => input.readOnly = false);
        }
        alert("Editing enabled for Quarter " + q);
    }

    let activeBulkQuarter = 0;
    function enableBulk(q) {
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
        let inputs = document.querySelectorAll('.col-acad.q' + activeBulkQuarter);
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

        status.style.display = 'inline'; status.innerHTML = 'Saving...';

        fetch('section-grades.php?section=' + encodeURIComponent(sec) + '&code=' + encodeURIComponent(code), {
            method: 'POST', body: formData
        })
        .then(res => res.text())
        .then(data => {
            if(data.includes("SAVED")) {
                status.style.color = '#198754'; status.innerHTML = '✅ Saved!';
                setTimeout(() => { status.innerHTML = ""; }, 3000);
            } else {
                alert("Error saving.");
            }
        });
    }
    // 4. REAL-TIME ATTENDANCE CALC
    function updateCounts(input) {
        // 1. Force Uppercase & Style
        let val = input.value.toUpperCase();
        input.value = val;
        
        // Color Coding
        if(val === 'A') { input.style.color = 'red'; input.style.backgroundColor = '#ffe6e6'; }
        else if(val === 'P') { input.style.color = 'green'; input.style.backgroundColor = ''; }
        else { input.style.color = 'black'; input.style.backgroundColor = ''; }

        // 2. Recalculate Totals for this Row
        let row = input.closest('tr');
        let allInputs = row.querySelectorAll('.att-input');
        let p = 0;
        let a = 0;

        allInputs.forEach(box => {
            if(box.value === 'P') p++;
            if(box.value === 'A') a++;
        });

        // 3. Update Cells
        row.querySelector('.count-p').innerText = p;
        row.querySelector('.count-a').innerText = a;
    }
    // 5. ADVISORY LOGIC
    function loadAdvisory() {
        let p = document.getElementById('adv_period').value;
        loadZone('advisory.php?period=' + p);
    }

    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>