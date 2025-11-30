<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Session Expired."; exit;
}
$tracks = ['kinder', 'junior high school', 'senior high school'];
?>

<div class="form-card" style="max-width: 900px;">
    <h2>Register & Enroll Student</h2>
    
    <form id="enrollForm" method="POST" autocomplete="off" onsubmit="event.preventDefault(); submitForm(this, 'register_student.php');">
        
        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:0;">I. Personal Information</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 0.5fr; gap: 15px;">
            <div class="form-group"><label>First Name</label><input type="text" name="fname" required></div>
            <div class="form-group"><label>Middle Name</label><input type="text" name="mname" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="lname" required></div>
            <div class="form-group"><label>Suffix</label><input type="text" name="suffix" placeholder="Jr."></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group"><label>Date of Birth</label><input type="date" name="birthdate" required></div>
            <div class="form-group"><label>Birthplace</label><input type="text" name="birthplace"></div>
            <div class="form-group"><label>Religion</label><input type="text" name="religion"></div>
            <div class="form-group"><label>Nationality</label><input type="text" name="nationality" value="Filipino"></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group"><label>Civil Status</label><select name="civilstatus"><option>Single</option><option>Married</option></select></div>
            <div class="form-group"><label>Gender</label><select name="gender"><option>Cisgender</option><option>Transgender</option></select></div>
            <div class="form-group"><label>Sex</label><select name="sex"><option>Male</option><option>Female</option></select></div>
        </div>

        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:20px;">II. Academic History</h3>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; margin-bottom: 15px;">
            <div class="form-group"><label>LRN</label><input type="text" name="lrn" placeholder="12-digit LRN" required></div>
            <div class="form-group"><label>Previous School</label><input type="text" name="previous_school"></div>
        </div>
        <div class="form-group">
            <label>Previous School Address</label>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; gap: 10px;">
                <input type="text" name="prev_street" placeholder="Street">
                <input type="text" name="prev_barangay" placeholder="Barangay">
                <input type="text" name="prev_city" placeholder="City">
                <input type="text" name="prev_province" placeholder="Province">
                <input type="text" name="prev_zip" placeholder="Zip">
            </div>
        </div>

        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:20px;">III. Contact & Address</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group"><label>Mobile No.</label><input type="text" name="contactno" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email"></div>
        </div>
        <div class="form-group">
            <label>Current Address</label>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; gap: 10px;">
                <input type="text" name="housenum_street" placeholder="Street">
                <input type="text" name="barangay" placeholder="Barangay">
                <input type="text" name="city" placeholder="City">
                <input type="text" name="province" placeholder="Province">
                <input type="text" name="zipcode" placeholder="Zip">
            </div>
        </div>

        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:20px; margin-bottom:15px;">IV. Family Background</h3>
        <div class="family-box">
            <label class="family-label">Father's Information</label>
            <div class="grid-3-col">
                <input type="text" name="fLname" placeholder="Last Name">
                <input type="text" name="fFname" placeholder="First Name">
                <input type="text" name="fMName" placeholder="Middle Name">
            </div>
            <div class="grid-2-col">
                <input type="text" name="fContactnum" placeholder="Contact No.">
                <input type="text" name="fOccupation" placeholder="Occupation">
            </div>
        </div>
        <div class="family-box">
            <label class="family-label">Mother's Information</label>
            <div class="grid-3-col">
                <input type="text" name="mLname" placeholder="Last Name">
                <input type="text" name="mFname" placeholder="First Name">
                <input type="text" name="mMName" placeholder="Middle Name">
            </div>
            <div class="grid-2-col">
                <input type="text" name="mContactnum" placeholder="Contact No.">
                <input type="text" name="mOccupation" placeholder="Occupation">
            </div>
        </div>
        <div class="family-box">
            <label class="family-label">Guardian's Information</label>
            <div class="grid-3-col">
                <input type="text" name="gLname" placeholder="Last Name">
                <input type="text" name="gFname" placeholder="First Name">
                <input type="text" name="gMName" placeholder="Middle Name">
            </div>
            <div class="grid-2-col">
                <input type="text" name="gContactnum" placeholder="Contact No.">
                <input type="text" name="gRelationship" placeholder="Relationship">
            </div>
        </div>

        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:20px;">V. Enrollment & Sectioning</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Track / Level</label>
                <select name="track" id="reg_track" onchange="updateRegisterGrade()" required>
                    <option value="">-- Select Track --</option>
                    <?php foreach ($tracks as $trk): ?>
                        <option value="<?php echo $trk; ?>"><?php echo ucfirst($trk); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Grade Level</label>
                <select name="grade_level" id="reg_level" onchange="fetchSections()" required>
                    <option value="">-- Select Track First --</option>
                    <option value="Kinder" class="opt-lvl opt-kinder" style="display:none;">Kindergarten</option>
                    <option value="Grade 7" class="opt-lvl opt-jhs" style="display:none;">Grade 7</option>
                    <option value="Grade 8" class="opt-lvl opt-jhs" style="display:none;">Grade 8</option>
                    <option value="Grade 9" class="opt-lvl opt-jhs" style="display:none;">Grade 9</option>
                    <option value="Grade 10" class="opt-lvl opt-jhs" style="display:none;">Grade 10</option>
                    <option value="Grade 11" class="opt-lvl opt-shs" style="display:none;">Grade 11</option>
                    <option value="Grade 12" class="opt-lvl opt-shs" style="display:none;">Grade 12</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="background:#eef2ff; padding:15px; border:1px solid #cce5ff; border-radius:5px; margin-top:15px;">
            <label style="color:#002D72;">Assigned Section</label>
            <select name="section" id="reg_section" required>
                <option value="">-- Select Grade Level First --</option>
                </select>
            <small style="color:#666;">This will auto-enroll the student in all subjects for this section.</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top:15px;">
            <div class="form-group" id="esc_box" style="display:none;">
                <label>ESC Grantee?</label>
                <select name="esc_grantee"><option value="No">No</option><option value="Yes">Yes</option></select>
            </div>
            <div class="form-group">
                <label>Sibling Enrolled?</label>
                <select name="has_sibling"><option value="0">No</option><option value="1">Yes</option></select>
            </div>
        </div>
        
        <button type="submit" class="btn-save" style="margin-top: 25px; padding: 15px;">Register & Enroll Student</button>
    </form>
</div>

<script>
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
</script>