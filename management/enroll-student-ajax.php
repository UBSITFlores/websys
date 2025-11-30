<?php
session_start();
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] !== 'management') {
    http_response_code(403); echo "Session Expired."; exit;
}

$tracks = ['kinder', 'junior high school', 'senior high school'];
?>

<div class="form-card">
    <h2>Register & Enroll Student</h2>
    
    <form id="enrollForm" method="POST" autocomplete="off" onsubmit="event.preventDefault(); submitForm(this, 'register_student.php');">
        
        <!-- SECTION 1: Personal Information -->
        <div class="form-section">
            <h3>I. Personal Information</h3>
            
            <div class="grid-4col">
                <div class="form-group"><label>First Name</label><input type="text" name="fname" required></div>
                <div class="form-group"><label>Middle Name</label><input type="text" name="mname" required></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="lname" required></div>
                <div class="form-group"><label>Suffix</label><input type="text" name="suffix" placeholder="Jr."></div>
            </div>

            <div class="grid-4col">
                <div class="form-group"><label>Date of Birth</label><input type="date" name="birthdate" required></div>
                <div class="form-group"><label>Birthplace</label><input type="text" name="birthplace"></div>
                <div class="form-group"><label>Religion</label><input type="text" name="religion"></div>
                <div class="form-group"><label>Nationality</label><input type="text" name="nationality" value="Filipino"></div>
            </div>

            <div class="grid-2col">
                <div class="form-group"><label>Civil Status</label>
                    <select name="civilstatus">
                        <option>Single</option><option>Married</option>
                    </select>
                </div>
                <div class="form-group"><label>Gender</label>
                    <select name="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Academic History -->
        <div class="form-section">
            <h3>II. Academic History</h3>
            
            <div class="grid-2col">
                <div class="form-group"><label>LRN</label><input type="text" name="lrn" placeholder="12-digit LRN" required></div>
                <div class="form-group"><label>Previous School</label><input type="text" name="previous_school"></div>
            </div>
            <div class="form-group">
                <label>Previous School Address</label>
                <div class="address-grid">
                    <input type="text" name="prev_street" placeholder="Street">
                    <input type="text" name="prev_barangay" placeholder="Barangay">
                    <input type="text" name="prev_city" placeholder="City">
                    <input type="text" name="prev_province" placeholder="Province">
                    <input type="text" name="prev_zip" placeholder="Zip">
                </div>
            </div>
        </div>

        <!-- SECTION 3: Contact & Address -->
        <div class="form-section">
            <h3>III. Contact & Address</h3>
            
            <div class="grid-2col">
                <div class="form-group"><label>Mobile No.</label><input type="text" name="contactno" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email"></div>
            </div>
            <div class="form-group">
                <label>Current Address</label>
                <div class="address-grid">
                    <input type="text" name="housenum_street" placeholder="Street">
                    <input type="text" name="barangay" placeholder="Barangay">
                    <input type="text" name="city" placeholder="City">
                    <input type="text" name="province" placeholder="Province">
                    <input type="text" name="zipcode" placeholder="Zip">
                </div>
            </div>
        </div>

        <!-- SECTION 4: Family Background -->
        <div class="form-section">
            <h3>IV. Family Background</h3>
            
            <div class="family-subsection">
                <label class="family-label">Father's Information</label>
                <div class="grid-3-col">
                    <div class="form-group"><label>Last Name</label><input type="text" name="fLname" placeholder=""></div>
                    <div class="form-group"><label>First Name</label><input type="text" name="fFname" placeholder=""></div>
                    <div class="form-group"><label>Middle Name</label><input type="text" name="fMName" placeholder=""></div>
                </div>
                <div class="grid-2col">
                    <div class="form-group"><label>Contact No.</label><input type="text" name="fContactnum" placeholder=""></div>
                    <div class="form-group"><label>Occupation</label><input type="text" name="fOccupation" placeholder=""></div>
                </div>
            </div>

            <div class="family-subsection">
                <label class="family-label">Mother's Information</label>
                <div class="grid-3-col">
                    <div class="form-group"><label>Maiden Last Name</label><input type="text" name="mLname" placeholder=""></div>
                    <div class="form-group"><label>First Name</label><input type="text" name="mFname" placeholder=""></div>
                    <div class="form-group"><label>Middle Name</label><input type="text" name="mMName" placeholder=""></div>
                </div>
                <div class="grid-2col">
                    <div class="form-group"><label>Contact No.</label><input type="text" name="mContactnum" placeholder=""></div>
                    <div class="form-group"><label>Occupation</label><input type="text" name="mOccupation" placeholder=""></div>
                </div>
            </div>

            <div class="family-subsection">
                <label class="family-label">Guardian's Information</label>
                <div class="grid-3-col">
                    <div class="form-group"><label>Last Name</label><input type="text" name="gLname" placeholder=""></div>
                    <div class="form-group"><label>First Name</label><input type="text" name="gFname" placeholder=""></div>
                    <div class="form-group"><label>Middle Name</label><input type="text" name="gMName" placeholder=""></div>
                </div>
                <div class="grid-2col">
                    <div class="form-group"><label>Contact No.</label><input type="text" name="gContactnum" placeholder=""></div>
                    <div class="form-group"><label>Relationship</label><input type="text" name="gRelationship" placeholder=""></div>
                </div>
            </div>
        </div>

        <!-- SECTION 5: Enrollment & Sectioning -->
        <div class="form-section">
            <h3>V. Enrollment & Sectioning</h3>
            
            <div class="grid-2col">
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
                        <option value="Kinder" class="opt-lvl opt-kinder">Kindergarten</option>
                        <option value="Grade 7" class="opt-lvl opt-jhs">Grade 7</option>
                        <option value="Grade 8" class="opt-lvl opt-jhs">Grade 8</option>
                        <option value="Grade 9" class="opt-lvl opt-jhs">Grade 9</option>
                        <option value="Grade 10" class="opt-lvl opt-jhs">Grade 10</option>
                        <option value="Grade 11" class="opt-lvl opt-shs">Grade 11</option>
                        <option value="Grade 12" class="opt-lvl opt-shs">Grade 12</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Assigned Section</label>
                <select name="section" id="reg_section" required>
                    <option value="">-- Select Grade Level First --</option>
                </select>
                <small>This will auto-enroll the student in all subjects for this section.</small>
            </div>

            <div class="grid-2col">
                <div class="form-group" id="esc_box">
                    <label>ESC Grantee?</label>
                    <select name="esc_grantee"><option value="No">No</option><option value="Yes">Yes</option></select>
                </div>
                <div class="form-group">
                    <label>Sibling Enrolled?</label>
                    <select name="has_sibling"><option value="0">No</option><option value="1">Yes</option></select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-save btn-submit-large">Register & Enroll Student</button>
    </form>
</div>