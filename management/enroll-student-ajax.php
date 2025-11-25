<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403);
    echo "Session Expired. Please Login Again.";
    exit;
}

$tracks = ['kinder', 'junior high school', 'senior high school'];
?>


<div class="form-card" style="max-width: 900px;"> <h2>Register New Student Profile</h2>
    
    <form id="enrollForm" method="POST" autocomplete="off" onsubmit="event.preventDefault(); submitForm(this, 'register_student.php');">
        
        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:0;">I. Personal Information</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 0.5fr; gap: 15px;">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="fname" required>
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="mname" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="lname" required>
            </div>
            <div class="form-group">
                <label>Suffix</label>
                <input type="text" name="suffix" placeholder="Jr.">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="birthdate" required>
            </div>
            <div class="form-group">
                <label>Birthplace</label>
                <input type="text" name="birthplace">
            </div>
            <div class="form-group">
                <label>Religion</label>
                <input type="text" name="religion">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Civil Status</label>
                <select name="civilstatus">
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nationality</label>
                <input type="text" name="nationality" value="Filipino">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="Cisgender">Cisgender</option>
                    <option value="Transgender">Transgender</option>
                </select>
            </div>
            <div class="form-group">
                <label>Sex</label>
                <select name="sex">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
        </div>

        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:20px;">II. Contact & Address</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Mobile Number</label>
                <input type="text" name="contactno" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email">
            </div>
        </div>

        <div class="form-group">
            <label>Current Address (House #, Street, Barangay, City, Province, Zip)</label>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; gap: 10px;">
                <input type="text" name="housenum_street" placeholder="Street">
                <input type="text" name="barangay" placeholder="Barangay">
                <input type="text" name="city" placeholder="City">
                <input type="text" name="province" placeholder="Province">
                <input type="text" name="zipcode" placeholder="Zip">
            </div>
        </div>

        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:20px;">III. Family Background</h3>

        <div style="background:#fafaff; padding:10px; border-radius:5px; margin-bottom:10px;">
            <label style="font-weight:bold; color:#666;">Father's Information</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <input type="text" name="fLname" placeholder="Last Name">
                <input type="text" name="fFname" placeholder="First Name">
                <input type="text" name="fMName" placeholder="Middle">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top:5px;">
                <input type="text" name="fContactnum" placeholder="Contact No.">
                <input type="text" name="fOccupation" placeholder="Occupation">
            </div>
        </div>

        <div style="background:#fafaff; padding:10px; border-radius:5px; margin-bottom:10px;">
            <label style="font-weight:bold; color:#666;">Mother's Information</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <input type="text" name="mLname" placeholder="Maiden Last Name">
                <input type="text" name="mFname" placeholder="First Name">
                <input type="text" name="mMName" placeholder="Middle">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top:5px;">
                <input type="text" name="mContactnum" placeholder="Contact No.">
                <input type="text" name="mOccupation" placeholder="Occupation">
            </div>
        </div>

        <div style="background:#fafaff; padding:10px; border-radius:5px; margin-bottom:10px;">
            <label style="font-weight:bold; color:#666;">Guardian's Information</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                <input type="text" name="gLname" placeholder="Last Name">
                <input type="text" name="gFname" placeholder="First Name">
                <input type="text" name="gMName" placeholder="Middle">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top:5px;">
                <input type="text" name="gContactnum" placeholder="Contact No.">
                <input type="text" name="gRelationship" placeholder="Relationship">
            </div>
        </div>

        <h3 style="color:#002D72; font-size:1.1rem; border-bottom:1px solid #eee; margin-top:20px;">IV. Enrollment Details</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Track / Level</label>
                <select name="track" required>
                    <option value="">-- Select Track --</option>
                    <?php foreach ($tracks as $trk): ?>
                        <option value="<?php echo $trk; ?>"><?php echo ucfirst($trk); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date Enrolled</label>
                <input type="date" name="date_enrolled" value="<?php echo date('Y-m-d'); ?>" readonly>
            </div>
        </div>
        
        <button type="submit" class="btn-save" style="margin-top: 25px; padding: 15px;">
            Register Student Profile
        </button>

    </form>
</div>