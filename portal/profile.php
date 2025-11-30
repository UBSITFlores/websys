<?php
require_once '../functions/student_function.php';

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

$studentFunc = new Student();
$student_pk = $studentFunc->getStudentId($_SESSION['ACCOUNTID']);
$profile = $studentFunc->getProfile($student_pk);

// If profile is missing (maybe admin registered them but didn't fill details), show default
if (!$profile) {
    echo "<div style='padding:20px; text-align:center;'>Profile details not found. Please contact the registrar.</div>";
    exit();
}

// Helper: Combine address parts
$full_address = $profile['housenum_street'] . ', ' . $profile['barangay'] . ', ' . $profile['city'] . ', ' . $profile['province'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-card">
            
            <div class="profile-header">
                <h1 class="profile-name">
                    <?php echo htmlspecialchars($profile['fname'] . ' ' . $profile['mname'] . ' ' . $profile['familyname']); ?>
                </h1>
                <div style="color:#666; margin-top:5px;">
                    ID: <?php echo htmlspecialchars($profile['account_id']); ?>
                </div>
            </div>

            <div class="section-title">Academic Information</div>
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Track / Strand</div>
                    <div class="field-value"><?php echo htmlspecialchars($profile['track']); ?></div>
                </div>
                <div class="profile-field">
                    <div class="field-label">Date Enrolled</div>
                    <div class="field-value"><?php echo htmlspecialchars($profile['date_enrolled']); ?></div>
                </div>
            </div>

            <div class="section-title">Personal Information</div>
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Email</div>
                    <div class="field-value"><?php echo htmlspecialchars($profile['email']); ?></div>
                </div>
                <div class="profile-field">
                    <div class="field-label">Contact No.</div>
                    <div class="field-value"><?php echo htmlspecialchars($profile['contactno']); ?></div>
                </div>
            </div>
            
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Birthdate</div>
                    <div class="field-value"><?php echo htmlspecialchars($profile['birthdate']); ?></div>
                </div>
                <div class="profile-field">
                    <div class="field-label">Gender / Sex</div>
                    <div class="field-value">
                        <?php echo htmlspecialchars($profile['sex']); ?> 
                        <span style="font-size:0.8em; color:#888;">(<?php echo htmlspecialchars($profile['gender']); ?>)</span>
                    </div>
                </div>
            </div>

            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Address</div>
                    <div class="field-value"><?php echo htmlspecialchars($full_address); ?></div>
                </div>
                <div class="profile-field">
                    <div class="field-label">Nationality</div>
                    <div class="field-value"><?php echo htmlspecialchars($profile['nationality']); ?></div>
                </div>
            </div>

            <div class="section-title">Guardian Information</div>
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Guardian Name</div>
                    <div class="field-value">
                        <?php echo htmlspecialchars($profile['gFname'] . ' ' . $profile['gLname']); ?>
                    </div>
                </div>
                <div class="profile-field">
                    <div class="field-label">Relationship</div>
                    <div class="field-value"><?php echo htmlspecialchars($profile['gRelationship']); ?></div>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Guardian Contact</div>
                    <div class="field-value"><?php echo htmlspecialchars($profile['gContactnum']); ?></div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>