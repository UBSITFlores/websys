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
</head>
<body>

    <div class="header">
        <div style="display: flex; align-items: center;">
            <img src="../assets/slu.png" alt="School Logo" style="height: 50px; margin-right: 15px;">
            <span style="font-weight: bold; font-size: 1.2rem;">Saint Louis School of Pacdal, Inc. (Management)</span>
        </div>
        <div style="font-size: 14px;">
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['FNAME'] ?? 'Instructor'); ?></strong>
            &nbsp;|&nbsp; 
            <a href="../account/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar-right">
            <button onclick="loadZone('welcome.php', this)">Dashboard Home</button>
            <button onclick="loadZone('schedule.php', this)">My Schedule</button>
            <button onclick="loadZone('grading-sheet-ajax.php', this)">My Class Loads</button>
            <button onclick="loadZone('advisory.php', this)">My Advisory Class</button>
        </div>

        <div class="content-zone" id="main-content">
        </div>
    </div>

    <script>
    // ===============================================
    // 1. CORE NAVIGATION
    // ===============================================
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
                console.error(err);
            });
    }

    // ===============================================
    // 2. PDF DOWNLOAD FUNCTIONS (FPDF)
    // ===============================================
    
    // Download Class Loads PDF
    function downloadClassesPDF(view) {
        const url = 'generate_grading_pdf.php' + (view === 'archive' ? '?view=archive' : '');
        window.open(url, '_blank');
    }

    // Print Attendance PDF
    // OLD (immediate execution)
function printAttendancePDF() {
    const section = document.getElementById('att_sec_name')?.value;
    // ...
}

// NEW (with timeout for DOM readiness)
function printAttendancePDF() {
    setTimeout(() => {
        const section = document.getElementById('att_sec_name')?.value;
        const code = document.getElementById('att_sub_code')?.value;
        const month = document.getElementById('month_picker')?.value || '<?php echo date('Y-m'); ?>';
        
        console.log('PDF Debug - Section:', section, 'Code:', code, 'Month:', month);
        
        if (!section || !code) {
            alert('Error: Missing section or code information.\n\nPlease make sure the attendance page is fully loaded.');
            return;
        }
        
        const url = 'print_attendance_pdf.php?section=' + encodeURIComponent(section) + 
                    '&code=' + encodeURIComponent(code) + 
                    '&month=' + encodeURIComponent(month);
        
        console.log('Opening PDF:', url);
        window.open(url, '_blank');
    }, 100); // Wait 100ms for DOM to be ready
}

    // ===============================================
    // 3. ATTENDANCE LOGIC
    // ===============================================
    function changeMonth() {
        let m = document.getElementById('month_picker').value;
        let sec = document.getElementById('att_sec_name').value;
        let code = document.getElementById('att_sub_code').value;
        
        loadZone('attendance.php?section=' + encodeURIComponent(sec) + '&code=' + encodeURIComponent(code) + '&month=' + m);
    }

    function saveAttendance() {
        let form = document.getElementById('attForm');
        let fd = new FormData(form);
        let msg = document.getElementById('save_msg');
        
        let sec = document.getElementById('att_sec_name').value;
        let code = document.getElementById('att_sub_code').value;
        let month = document.getElementById('month_picker').value;

        msg.innerText = "Saving...";
        msg.style.color = "#856404";
        
        let url = 'attendance.php?section=' + encodeURIComponent(sec) + '&code=' + encodeURIComponent(code) + '&month=' + month;

        fetch(url, { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            if(data.includes("SAVED")) {
                msg.style.color = "green";
                msg.innerText = "✅ Saved!";
                setTimeout(() => { msg.innerText = ""; }, 2000);
            } else {
                msg.style.color = "red";
                msg.innerText = "❌ Error saving";
                console.error(data);
            }
        })
        .catch(err => {
            msg.style.color = "red";
            msg.innerText = "❌ Network error";
            console.error(err);
        });
    }

    function updateCounts(input) {
        // Force uppercase & style
        let val = input.value.toUpperCase();
        input.value = val;
        
        // Color coding
        if(val === 'A') { 
            input.style.color = 'red'; 
            input.style.backgroundColor = '#ffe6e6'; 
        }
        else if(val === 'P') { 
            input.style.color = 'green'; 
            input.style.backgroundColor = '#e6ffe6'; 
        }
        else if(val === 'L') { 
            input.style.color = '#856404'; 
            input.style.backgroundColor = '#fff3cd'; 
        }
        else { 
            input.style.color = 'black'; 
            input.style.backgroundColor = ''; 
        }

        // Recalculate totals for this row
        let row = input.closest('tr');
        let allInputs = row.querySelectorAll('.att-input');
        let p = 0;
        let a = 0;

        allInputs.forEach(box => {
            if(box.value === 'P') p++;
            if(box.value === 'A') a++;
        });

        // Update cells
        row.querySelector('.count-p').innerText = p;
        row.querySelector('.count-a').innerText = a;
    }

    // ===============================================
    // 4. GRADING LOGIC
    // ===============================================
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
        var inputs = row.querySelectorAll('.score-val');
        
        var total = 0;
        var count = 0;
        var filled = 0;

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

        if(filled > 0) {
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

        status.style.display = 'inline'; 
        status.innerHTML = 'Saving...';
        status.style.color = '#856404';

        fetch('section-grades.php?section=' + encodeURIComponent(sec) + '&code=' + encodeURIComponent(code), {
            method: 'POST', 
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if(data.includes("SAVED")) {
                status.style.color = '#198754'; 
                status.innerHTML = '✅ Saved!';
                setTimeout(() => { status.innerHTML = ""; }, 3000);
            } else {
                status.style.color = '#dc3545';
                status.innerHTML = '❌ Error';
                console.error(data);
            }
        })
        .catch(err => {
            status.style.color = '#dc3545';
            status.innerHTML = '❌ Network Error';
            console.error(err);
        });
    }

    // ===============================================
    // 5. ADVISORY LOGIC
    // ===============================================
    function loadAdvisory() {
        let p = document.getElementById('adv_period').value;
        loadZone('advisory.php?period=' + p);
    }
    
    // ===============================================
    // 6. INITIALIZE ON LOAD
    // ===============================================
    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>