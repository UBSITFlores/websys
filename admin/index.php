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
    <link rel="stylesheet" href="dashboard.css?v=3">
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
            
            <button onclick="loadZone('curriculum.php', this)">Curriculum Setup</button>

            <button onclick="loadZone('section_manager.php', this)">Section Manager</button>

            <button onclick="loadZone('class_offering.php', this)">Class Assignment</button>

            <button onclick="loadZone('instructors.php', this)">Faculty List</button>

            <button onclick="loadZone('manage_accounts.php', this)">Manage Accounts</button>

            <button onclick="loadZone('add_user.php', this)">Create New User</button>

            <button onclick="loadZone('settings.php', this)">System Settings</button>
        </div>

        <div class="content-zone" id="main-content">
            </div>
    </div>

    <script>
    // ==========================================
    // 1. CORE FUNCTIONS (Load & Submit)
    // ==========================================
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
                
                // AUTO-TRIGGER LOGIC
                // If Faculty List loaded -> Load data immediately
                if(url.includes('instructors.php')) { filterFaculty(); }
                
                // If Manage Accounts loaded -> Clear inputs (Start fresh)
                if(url.includes('manage_accounts.php')) { 
                     // Optional: liveSearch(); if you want it to auto-load all users
                }
            })
            .catch(err => {
                document.getElementById('main-content').innerHTML = "<div style='color:red; padding:20px;'>Error loading content.</div>";
            });
    }

    function submitForm(formElement, url) {
        let formData = new FormData(formElement);
        let btn = formElement.querySelector('button[type="submit"]');
        if(btn && btn.name) formData.append(btn.name, btn.value);

        // Show Loader
        document.getElementById('main-content').innerHTML = "<div style='text-align:center; padding:50px; font-size:1.2em; color:#002D72;'>Processing...</div>";

        fetch(url, { method: 'POST', body: formData })
        .then(response => response.text())
        .then(responseHTML => {
            // If response contains a script (like an alert or a refresh command), run it
            if(responseHTML.includes('<script>') || responseHTML.includes('filterFaculty();') || responseHTML.includes('loadZone')) {
                 // Render the HTML (which might include the alert script)
                 document.getElementById('main-content').innerHTML = responseHTML;
                 
                 // FIND AND EXECUTE SCRIPTS
                 let scripts = document.getElementById('main-content').querySelectorAll("script");
                 scripts.forEach(script => { try { eval(script.innerHTML); } catch(e) {} });
            } else {
                // Just show the content
                document.getElementById('main-content').innerHTML = responseHTML;
            }
        });
    }

    // ==========================================
    // 2. MANAGE ACCOUNTS LOGIC
    // ==========================================
    function setRoleFilter(role, btn) {
        document.getElementById('search_role').value = role;
        let buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        liveSearch();
    }

    function liveSearch() {
        let roleInput = document.getElementById('search_role');
        let gradeInput = document.getElementById('search_grade'); // New
        let textInput = document.getElementById('search_text');
        let tableBody = document.getElementById('account_table_body');

        if(!roleInput || !textInput || !tableBody) return;

        let r = roleInput.value;
        let g = gradeInput ? gradeInput.value : ''; // New (Safety check)
        let s = textInput.value;
        
        // Include &grade= in the URL
        fetch('manage_accounts.php?ajax_search=1&role=' + r + '&grade=' + encodeURIComponent(g) + '&search=' + encodeURIComponent(s))
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

    // ==========================================
    // 3. FACULTY LIST LOGIC
    // ==========================================
    function filterFaculty() {
        var t = document.getElementById('f_track').value;
        var d = document.getElementById('f_degree').value;
        var s = document.getElementById('f_status').value;
        var tableBody = document.getElementById('faculty_table_body');

        if(!tableBody) return;
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#aaa;">Loading...</td></tr>';

        fetch('instructors.php?ajax_search=1&track=' + encodeURIComponent(t) + '&degree=' + encodeURIComponent(d) + '&status=' + encodeURIComponent(s))
        .then(res => res.text())
        .then(html => { tableBody.innerHTML = html; });
    }

    // ==========================================
    // 4. CLASS ASSIGNMENT LOGIC (Filter Subjects)
    // ==========================================
    function filterSubjects() {
        var track = document.getElementById('sel_track').value;
        var yearSelect = document.getElementById('sel_year');
        var subjSelect = document.getElementById('sel_subject');
        
        // A. UPDATE YEAR DROPDOWN
        if(yearSelect.value === "" || yearSelect.selectedOptions[0].hidden) {
            yearSelect.value = "";
        }
        var opts = yearSelect.getElementsByClassName("opt-level");
        for(var i=0; i<opts.length; i++) { opts[i].hidden = true; opts[i].style.display = "none"; }

        if(track == "kinder") {
            var show = yearSelect.getElementsByClassName("opt-kinder");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        } 
        else if(track == "junior high school") {
            var show = yearSelect.getElementsByClassName("opt-jhs");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        }
        else if(["STEM", "ABM", "HUMSS"].includes(track)) {
            var show = yearSelect.getElementsByClassName("opt-shs");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        }

        // B. FILTER SUBJECTS
        var year = yearSelect.value;
        var options = document.getElementsByClassName('sub-opt');
        var visibleCount = 0;

        for (var i = 0; i < options.length; i++) {
            var opt = options[i];
            var oTrack = opt.getAttribute('data-track');
            var oYear = opt.getAttribute('data-year');
            
            var isYearMatch = (oYear == year);
            var isTrackMatch = false;

            if (oTrack == track) {
                isTrackMatch = true;
            } else if (oTrack == 'Regular' && (track == 'kinder' || track == 'junior high school')) {
                isTrackMatch = true;
            }

            if (isYearMatch && isTrackMatch) {
                opt.style.display = 'block';
                visibleCount++;
            } else {
                opt.style.display = 'none';
            }
        }

        if(visibleCount === 0) {
            subjSelect.value = "";
            if(track && year) subjSelect.options[0].text = "-- No subjects found --";
            else subjSelect.options[0].text = "-- Select Track & Year First --";
        } else {
            subjSelect.options[0].text = "-- Choose from " + visibleCount + " Subjects --";
        }
    }

    // 5. CLASS OFFERING LOGIC (Smart Filters)
    function filterSubjects() {
        var track = document.getElementById('sel_track').value;
        var yearSelect = document.getElementById('sel_year');
        var subjSelect = document.getElementById('sel_subject');
        var secSelect = document.getElementById('sel_section'); // New

        // A. RESET & HIDE YEAR OPTIONS
        if(yearSelect.value === "" || yearSelect.selectedOptions[0].hidden) yearSelect.value = "";
        var opts = yearSelect.getElementsByClassName("opt-level");
        for(var i=0; i<opts.length; i++) { opts[i].hidden = true; opts[i].style.display = "none"; }

        if(track == "kinder") {
            var show = yearSelect.getElementsByClassName("opt-kinder");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        } else if(track == "junior high school") {
            var show = yearSelect.getElementsByClassName("opt-jhs");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        } else if(["STEM", "ABM", "HUMSS"].includes(track)) {
            var show = yearSelect.getElementsByClassName("opt-shs");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        }

        // B. FILTER SUBJECTS AND SECTIONS
        var year = yearSelect.value;
        
        // Subjects
        var subOptions = document.getElementsByClassName('sub-opt');
        var subCount = 0;
        for (var i = 0; i < subOptions.length; i++) {
            var oTrack = subOptions[i].getAttribute('data-track');
            var oYear = subOptions[i].getAttribute('data-year');
            
            var match = (oYear == year) && (oTrack == track || (oTrack == 'Regular' && (track == 'kinder' || track == 'junior high school')));
            
            if (match) { subOptions[i].style.display = 'block'; subCount++; } 
            else { subOptions[i].style.display = 'none'; }
        }

        // Sections (New!)
        var secOptions = document.getElementsByClassName('sec-opt');
        var secCount = 0;
        for (var i = 0; i < secOptions.length; i++) {
            var oTrack = secOptions[i].getAttribute('data-track');
            var oYear = secOptions[i].getAttribute('data-year');

            if (oTrack == track && oYear == year) {
                secOptions[i].style.display = 'block'; secCount++;
            } else {
                secOptions[i].style.display = 'none';
            }
        }

        // UPDATE PLACEHOLDERS
        if(subCount === 0) {
            subjSelect.value = "";
            subjSelect.options[0].text = (track && year) ? "-- No subjects found --" : "-- Select Track & Year --";
        } else {
            subjSelect.options[0].text = "-- Choose Subject --";
        }

        if(secCount === 0) {
            secSelect.value = "";
            secSelect.options[0].text = (track && year) ? "-- No sections found --" : "-- Select Track & Year --";
        } else {
            secSelect.options[0].text = "-- Choose Section --";
        }
    }
    // ==========================================
    // 6. PROMOTION LOGIC
    // ==========================================
    function updateActionUI() {
        var level = document.getElementById('batch_level').value;
        var preview = document.getElementById('action_preview');
        var text = document.getElementById('action_text');
        var input = document.getElementById('real_action');
        var btn = document.getElementById('btn_process');

        if(level == "") {
            preview.style.display = "none";
            btn.disabled = true;
            return;
        }

        preview.style.display = "block";
        btn.disabled = false;

        // LOGIC: Who Graduates?
        // Kinder AND Grade 12 now Graduate.
        if(level == "Grade 12" || level == "Kinder") {
            text.innerText = "Mark batch as GRADUATED. Accounts will close in 3 months.";
            input.value = "graduate";
            btn.innerText = "Graduate Batch";
            btn.style.backgroundColor = "#dc3545"; // Red
        } else {
            // Everyone else promotes
            var next = "";
            var num = 0;
            
            // Simple string replacement
            if(level.includes("Grade")) {
                var parts = level.split(" "); // Split "Grade 7" into ["Grade", "7"]
                var currentNum = parseInt(parts[1]);
                var nextNum = currentNum + 1;
                next = "Grade " + nextNum;
            }

            text.innerText = "Promote all active students to " + next + ".";
            input.value = "promote";
            btn.innerText = "Promote Batch";
            btn.style.backgroundColor = "#002D72"; // Blue
        }
    }
    // 7. SECTION MANAGER LOGIC
    function deleteSection(id) {
        if(confirm("Are you sure you want to delete this section?")) {
            let fd = new FormData();
            fd.append('delete_id', id);
            
            fetch('section_manager.php', { method: 'POST', body: fd })
            .then(res => res.text())
            .then(html => {
                if(html.trim() === "DELETED") {
                    loadZone('section_manager.php'); // Refresh list
                } else {
                    alert("Error deleting section. It might be in use.");
                }
            });
        }
    }
    // FACULTY UPDATE (New Function)
    function updateInstructor(btn, id) {
        // 1. Find the row (TR)
        let row = btn.closest('tr');
        
        // 2. Find inputs inside that row
        let degree = row.querySelector('.val-degree').value;
        let years = row.querySelector('.val-years').value;
        let status = row.querySelector('.val-status').value;

        // 3. Prepare Data
        let fd = new FormData();
        fd.append('update_id', id);
        fd.append('degree', degree);
        fd.append('years_active', years);
        fd.append('status', status);

        // 4. Send
        btn.innerText = "Saving...";
        fetch('instructors.php', { method: 'POST', body: fd })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "UPDATED") {
                alert("Instructor Updated Successfully!");
                filterFaculty(); // Refresh table
            } else {
                alert("Error updating.");
                btn.innerText = "Update";
            }
        });
    }
    function deleteSubject(id) {
    if(!confirm('Are you sure you want to delete this subject?')) return;
    fetch('curriculum.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'delete_id=' + encodeURIComponent(id)
    }).then(r => r.text()).then(txt => {
        if(txt.trim() === 'DELETED') {
            alert('Subject deleted');
            if(typeof loadZone === 'function') loadZone('curriculum.php');
            else location.reload();
        } else {
            alert('Delete response: ' + txt);
        }
    }).catch(err => alert('Error: ' + err));
    }

    function filterTrack(track, btn) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    // Filter rows
    const rows = document.querySelectorAll('#subject-tbody tr[data-track]');
    rows.forEach(row => {
        const rowTrack = row.getAttribute('data-track');
        if (track === 'all' || rowTrack === track) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    }

    // Map track selections to visible year-level option classes
    function resetYear(){
        const track = document.getElementById('sel_track').value;
        const levels = document.querySelectorAll('.opt-level');
        levels.forEach(l => l.style.display = 'none');
        // show relevant options
        if(track === 'kinder') {
            document.querySelectorAll('.opt-kinder').forEach(o => o.style.display = 'block');
        } else if(track === 'junior high school') {
            document.querySelectorAll('.opt-jhs').forEach(o => o.style.display = 'block');
        } else if(track === 'STEM' || track === 'ABM' || track === 'HUMSS' || track === 'senior high school') {
            document.querySelectorAll('.opt-shs').forEach(o => o.style.display = 'block');
        }
        // reset selected year
        const selYear = document.getElementById('sel_year');
        if(selYear) selYear.value = '';
    }

    function filterOptions(){
        const track = document.getElementById('sel_track').value;
        const year = document.getElementById('sel_year').value;

        // subjects
        document.querySelectorAll('.sub-opt').forEach(opt=>{
            const optTrack = (opt.getAttribute('data-track')||'').toLowerCase();
            const optYear  = (opt.getAttribute('data-year')||'').toLowerCase();
            let show = false;
            if(track === 'kinder') show = optYear.includes('kinder');
            else if(track === 'junior high school') show = optYear.includes('grade 7')||optYear.includes('grade 8')||optYear.includes('grade 9')||optYear.includes('grade 10');
            else if(track === 'STEM' || track === 'ABM' || track === 'HUMSS' || track === 'senior high school') show = optYear.includes('grade 11')||optYear.includes('grade 12');
            // additionally check track if subject has specialized track
            if(track && optTrack && !optTrack.includes(track.toLowerCase()) && !optTrack.includes('regular')) {
                // if subject track doesn't match selected track and is not 'Regular', skip
                show = false;
            }
            // if year filter set, be stricter
            if(year) {
                show = show && optYear.includes(year.toLowerCase());
            }
            opt.style.display = show ? 'block' : 'none';
        });

        // sections
        document.querySelectorAll('.sec-opt').forEach(opt=>{
            const optTrack = (opt.getAttribute('data-track')||'').toLowerCase();
            const optYear  = (opt.getAttribute('data-year')||'').toLowerCase();
            let show = false;
            if(track === 'kinder') show = optYear.includes('kinder');
            else if(track === 'junior high school') show = optYear.includes('grade 7')||optYear.includes('grade 8')||optYear.includes('grade 9')||optYear.includes('grade 10');
            else if(track === 'STEM' || track === 'ABM' || track === 'HUMSS' || track === 'senior high school') show = optYear.includes('grade 11')||optYear.includes('grade 12');
            if(track && optTrack && !optTrack.includes(track.toLowerCase()) && optTrack !== '') show = false;
            if(year) show = show && optYear.includes(year.toLowerCase());
            opt.style.display = show ? 'block' : 'none';
        });
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function(){
        resetYear();
        filterOptions();
        // expose functions for inline onchange attributes
        window.resetYear = resetYear; window.filterOptions = filterOptions;
    });
    

    // Default Load
    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>