<?php
require_once '../functions/student_function.php';

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

$studentFunc = new Student();
$student_pk = $studentFunc->getStudentId($_SESSION['ACCOUNTID']);
$p = $studentFunc->getProfile($student_pk);

if (!$p) {
    echo "<div style='padding:20px; text-align:center;'>Profile not found.</div>";
    exit();
}

// Helper to combine address
$current_addr = trim($p['housenum_street'] . ', ' . $p['barangay'] . ', ' . $p['city'] . ', ' . $p['province'] . ' ' . $p['zipcode']);
$prev_addr = trim($p['prev_street'] . ', ' . $p['prev_barangay'] . ', ' . $p['prev_city'] . ', ' . $p['prev_province']);
?>

<style>
    .profile-card { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 900px; margin: 0 auto; }
    .prof-header { display: flex; align-items: center; gap: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 20px; }
    .prof-avatar { width: 80px; height: 80px; background: #002D72; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; }
    .prof-title h1 { margin: 0; color: #002D72; font-size: 1.8rem; }
    .prof-title p { margin: 5px 0 0; color: #666; }
    
    .section-title { color: #002D72; font-size: 1.1rem; font-weight: bold; margin-top: 25px; margin-bottom: 15px; border-left: 4px solid #febb3f; padding-left: 10px; }
    
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .info-item label { display: block; font-size: 0.85rem; color: #888; font-weight: 600; text-transform: uppercase; margin-bottom: 3px; }
    .info-item div { font-size: 1rem; color: #333; font-weight: 500; border-bottom: 1px solid #f0f0f0; padding-bottom: 5px; }
    
    @media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } }
</style>

<div class="profile-card">
    <div class="prof-header">
        <div class="prof-avatar">
            <?php echo strtoupper(substr($p['fname'], 0, 1)); ?>
        </div>
        <div class="prof-title">
            <h1><?php echo htmlspecialchars($p['lname'] . ', ' . $p['fname'] . ' ' . $p['mname']); ?></h1>
            <p>Student ID: <strong><?php echo htmlspecialchars($p['account_id']); ?></strong> | <?php echo htmlspecialchars(ucfirst($p['track'])); ?></p>
        </div>
    </div>

    <div class="section-title">Academic Information</div>
    <div class="info-grid">
        <div class="info-item"><label>LRN</label><div><?php echo htmlspecialchars($p['lrn']); ?></div></div>
        <div class="info-item"><label>Grade Level</label><div><?php echo htmlspecialchars($p['grade_level']); ?></div></div>
        <div class="info-item"><label>Date Enrolled</label><div><?php echo htmlspecialchars($p['date_enrolled']); ?></div></div>
        <div class="info-item"><label>ESC Grantee</label><div><?php echo htmlspecialchars($p['esc_grantee']); ?></div></div>
    </div>

    <div class="section-title">Personal Information</div>
    <div class="info-grid">
        <div class="info-item"><label>Birthdate</label><div><?php echo htmlspecialchars($p['birthdate']); ?></div></div>
        <div class="info-item"><label>Gender</label><div><?php echo htmlspecialchars($p['gender']); ?></div></div>
        <div class="info-item"><label>Nationality</label><div><?php echo htmlspecialchars($p['nationality']); ?></div></div>
        <div class="info-item"><label>Religion</label><div><?php echo htmlspecialchars($p['religion']); ?></div></div>
        <div class="info-item"><label>Civil Status</label><div><?php echo htmlspecialchars($p['civilstatus']); ?></div></div>
        <div class="info-item"><label>Contact No.</label><div><?php echo htmlspecialchars($p['contactno']); ?></div></div>
        <div class="info-item" style="grid-column: span 2;"><label>Email</label><div><?php echo htmlspecialchars($p['email']); ?></div></div>
        <div class="info-item" style="grid-column: span 2;"><label>Current Address</label><div><?php echo htmlspecialchars($current_addr); ?></div></div>
    </div>

    <div class="section-title">Educational Background</div>
    <div class="info-grid">
        <div class="info-item"><label>Last School Attended</label><div><?php echo htmlspecialchars($p['previous_school']); ?></div></div>
        <div class="info-item"><label>School Address</label><div><?php echo htmlspecialchars($prev_addr); ?></div></div>
    </div>

    <div class="section-title">Family Information</div>
    <div class="info-grid">
        <div class="info-item"><label>Father's Name</label><div><?php echo htmlspecialchars($p['fLname'] . ', ' . $p['fFname']); ?></div></div>
        <div class="info-item"><label>Father's Contact</label><div><?php echo htmlspecialchars($p['fContactnum']); ?></div></div>
        
        <div class="info-item"><label>Mother's Name</label><div><?php echo htmlspecialchars($p['mLname'] . ', ' . $p['mFname']); ?></div></div>
        <div class="info-item"><label>Mother's Contact</label><div><?php echo htmlspecialchars($p['mContactnum']); ?></div></div>
        
        <div class="info-item"><label>Guardian's Name</label><div><?php echo htmlspecialchars($p['gLname'] . ', ' . $p['gFname']); ?></div></div>
        <div class="info-item"><label>Guardian's Contact</label><div><?php echo htmlspecialchars($p['gContactnum']); ?></div></div>
    </div>
</div>