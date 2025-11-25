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
    <style>

        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f5f6fa; color: #232b47; }
        

        .header { padding: 20px 24px; background: #002D72; color: #fff; font-size: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: #fff; font-size: 14px; text-decoration: none; opacity: 0.8; }
        .header a:hover { opacity: 1; }


        .container { display: flex; min-height: calc(100vh - 69px); }
        

        .sidebar-right { width: 240px; background: #001f52; color: #fff; padding: 20px 10px; flex-shrink: 0; }
        .sidebar-right button {
            width: 100%; margin-bottom: 8px; padding: 12px 15px; 
            background: transparent; border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 6px; color: #cfd8dc; font-size: 14px; 
            text-align: left; cursor: pointer; transition: 0.2s;
        }
        .sidebar-right button:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar-right button.active { background: #febb3f; color: #001f52; font-weight: bold; border-color: #febb3f; }


        .content-zone { flex: 1; padding: 40px; overflow-y: auto; }


        .form-card { max-width: 600px; margin: 0 auto; background: #fff; padding: 35px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-card h2 { color: #002D72; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-top: 0; font-size: 1.5rem; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #444; font-size: 0.95rem; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px 12px; border: 1.5px solid #dfe1e5; border-radius: 6px; font-size: 1rem; color: #333; box-sizing: border-box;
        }
        .btn-save {
            width: 100%; background: #002D72; color: white; padding: 12px; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: 0.2s;
        }
        .btn-save:hover { background: #004099; }

        .welcome-box { text-align: center; margin-top: 60px; }
        .welcome-box h1 { color: #002D72; font-size: 2.5rem; margin-bottom: 10px; }
        .welcome-icon { font-size: 4rem; margin-top: 30px; opacity: 0.5; }
    </style>
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
            <button onclick="loadZone('create_class.php', this)">Create New Class</button>
            <button onclick="loadZone('view_classes.php', this)">Master Class List</button> 
            <button onclick="loadZone('enroll-student-ajax.php', this)">Register New Student</button>
            <button onclick="loadZone('enroll_subject.php', this)">Enroll Student to Subject</button>
            <button onclick="loadZone('assign_instructor.php', this)">Assign Instructor</button>
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
//checker if id is valid
    function verifyStudent() {
        var id = document.getElementById("stu_id_input").value;
        var resultDiv = document.getElementById("check_result");
        var btn = document.getElementById("btn_enroll");
        var sectionList = document.getElementById("final_section");
        var trackDisplay = document.getElementById("display_track");
        var trackHidden = document.getElementById("filter_track");
        
        var strandBox = document.getElementById("strand_container");
        var strandFilter = document.getElementById("filter_strand");
        var yearFilter = document.getElementById("filter_year");

        if(id.trim() == ""){
            if(resultDiv) resultDiv.style.display = "none";
            return;
        }

        var formData = new FormData();
        formData.append("student_id", id);

        fetch("get_student_info.php", { method: "POST", body: formData })
        .then(function(response){ return response.text(); })
        .then(function(data){
            if(data.includes("|")) {
                var parts = data.split("|");
            
                resultDiv.style.display = "block";
                resultDiv.style.background = "#d4edda";
                resultDiv.style.color = "#155724";
                resultDiv.innerHTML = "✅ Found: " + parts[1];

                var track = parts[2].toLowerCase().trim();
                trackDisplay.value = track;
                trackHidden.value = track;

                yearFilter.value = "";
                var opts = document.getElementsByClassName("opt-level");
                for(var i=0; i<opts.length; i++) { opts[i].style.display = "none"; } 

                if(track == "kinder") {
                    var show = document.getElementsByClassName("opt-kinder");
                    for(var i=0; i<show.length; i++) show[i].style.display = "block";
                    if(strandBox) strandBox.style.display = "none";
                } 
                else if(track == "junior high school") {
                    var show = document.getElementsByClassName("opt-jhs");
                    for(var i=0; i<show.length; i++) show[i].style.display = "block";
                    if(strandBox) strandBox.style.display = "none";
                }
                else if(track == "senior high school") {
                    var show = document.getElementsByClassName("opt-shs");
                    for(var i=0; i<show.length; i++) show[i].style.display = "block";
                    if(strandBox) strandBox.style.display = "block"; 
                }

                btn.disabled = false;
                btn.style.opacity = "1";
                btn.style.cursor = "pointer";
                sectionList.disabled = false;
                
                filterClasses();
            } else {

                resultDiv.style.display = "block";
                resultDiv.style.background = "#f8d7da";
                resultDiv.style.color = "#721c24";
                resultDiv.innerHTML = "❌ ID Not Found";
                btn.disabled = true;
                sectionList.disabled = true;
            }
        });
    }

    function filterClasses(){
        var trackVal = document.getElementById("filter_track").value; 
        var yearVal = document.getElementById("filter_year").value;
        var subjVal = document.getElementById("filter_subject").value;
        var strandVal = document.getElementById("filter_strand") ? document.getElementById("filter_strand").value : "";
        
        var options = document.getElementsByClassName("sec-opt");
        var selectBox = document.getElementById("final_section");
        var visibleCount = 0;

        for(var i=0; i < options.length; i++){
            var opt = options[i];
            var matchTrack = (trackVal == "") || (opt.getAttribute("data-track") == trackVal);
            var matchYear  = (yearVal == "")  || (opt.getAttribute("data-year") == yearVal);
            var matchSubj  = (subjVal == "")  || (opt.getAttribute("data-code") == subjVal);
            var matchStrand = (strandVal == "") || (opt.getAttribute("data-strand") == strandVal);

            if(trackVal == "senior high school") {
                 if(matchTrack && matchYear && matchSubj && matchStrand){
                    opt.style.display = "block";
                    visibleCount++;
                } else {
                    opt.style.display = "none";
                }
            } else {
                if(matchTrack && matchYear && matchSubj){
                    opt.style.display = "block";
                    visibleCount++;
                } else {
                    opt.style.display = "none";
                }
            }
        }

        if(visibleCount > 0){
            selectBox.options[0].text = "-- Choose Class (" + visibleCount + " available) --";
        } else {
            selectBox.options[0].text = "-- No classes match filters --";
            selectBox.value = "";
        }
    }

//Instructor
    function assign_updateYear() {
        var trackVal = document.getElementById("filter_track").value;
        var yearSelect = document.getElementById("filter_year");
        
        yearSelect.value = ""; 
        
        var opts = yearSelect.getElementsByClassName("opt-level");
        for(var i=0; i<opts.length; i++) {
            opts[i].hidden = true; 
            opts[i].style.display = "none"; 
        }

        if(trackVal == "kinder") {
            var show = yearSelect.getElementsByClassName("opt-kinder");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        } 
        else if(trackVal == "junior high school") {
            var show = yearSelect.getElementsByClassName("opt-jhs");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        }
        else if(trackVal == "senior high school") {
            var show = yearSelect.getElementsByClassName("opt-shs");
            for(var i=0; i<show.length; i++) { show[i].hidden = false; show[i].style.display = "block"; }
        }
        assign_filterAll();
    }

    function assign_filterAll() {
        var trackVal = document.getElementById("filter_track").value; 
        var yearVal = document.getElementById("filter_year").value;
        var subjVal = document.getElementById("filter_subject").value;
        
        var subjOpts = document.getElementsByClassName("subj-opt");
        for(var i=0; i<subjOpts.length; i++){
            var sOpt = subjOpts[i];
            var sTrack = sOpt.getAttribute("data-track");
            
            if(trackVal == "" || sTrack == trackVal) {
                sOpt.hidden = false; sOpt.style.display = "block";
            } else {
                sOpt.hidden = true; sOpt.style.display = "none";
            }
        }

        var options = document.getElementsByClassName("sec-opt");
        var selectBox = document.getElementById("final_section");
        var visibleCount = 0;

        for(var i=0; i < options.length; i++){
            var opt = options[i];
            var matchTrack = (trackVal == "") || (opt.getAttribute("data-track") == trackVal);
            var matchYear  = (yearVal == "")  || (opt.getAttribute("data-year") == yearVal);
            var matchSubj  = (subjVal == "")  || (opt.getAttribute("data-code") == subjVal);

            if(matchTrack && matchYear && matchSubj){
                opt.hidden = false; opt.style.display = "block";
                visibleCount++;
            } else {
                opt.hidden = true; opt.style.display = "none";
            }
        }
        
        if(visibleCount > 0){
            selectBox.options[0].text = "-- Choose Class (" + visibleCount + " available) --";
        } else {
            selectBox.options[0].text = "-- No classes match filters --";
            selectBox.value = "";
        }

        var instList = document.getElementById("instructor_list");
        if(instList) {
            var instructors = instList.getElementsByTagName("option");
            for(var j=0; j < instructors.length; j++){
                var inst = instructors[j];
                if(inst.value == "") continue; 
                var iTrack = inst.getAttribute("data-track");
                if(trackVal == "" || iTrack == trackVal){
                    inst.hidden = false; inst.style.display = "block";
                } else {
                    inst.hidden = true; inst.style.display = "none";
                }
            }
            if(instList.selectedOptions.length > 0 && instList.selectedOptions[0].hidden){
                instList.value = "";
            }
        }
    }
    window.onload = function() {
        loadZone('welcome.php', null);
    };
    </script>
</body>
</html>