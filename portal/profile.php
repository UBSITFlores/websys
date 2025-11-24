<?php

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

$fname = $_SESSION['FNAME'] ?? '';
$lname = $_SESSION['LNAME'] ?? '';

$mockProfile = [
    'studentId' => '2024-12345',
    'email' => 'student@usaint.edu',
    'phone' => '+63 912 345 6789',
    'address' => '123 Main St, Baguio City',
    'course' => 'Bachelor of Science in Information Technology',
    'year' => '3rd Year',
    'semester' => '1st Semester'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Academic Management System</title>
    <style>
        :root {
            --royal-blue: #002D72;
            --white: #ffffff;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .back-button {
            color: var(--white);
            text-decoration: none;
            margin-right: 8px;
            padding: 6px 10px;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 4px;
            background: rgba(255,255,255,0.03);
            font-weight: 600;
        }

        .back-button:hover {
            background: rgba(255,255,255,0.08);
        }

        .profile-container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--royal-blue);
            padding-bottom: 1rem;
        }

        .profile-name {
            color: var(--royal-blue);
            font-size: 1.8rem;
            margin: 0;
        }

        .profile-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .profile-field {
            padding: 1rem;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .field-label {
            color: var(--royal-blue);
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .field-value {
            color: #333;
        }

        @media screen and (max-width: 768px) {
            .profile-container {
                padding: 1rem;
            }

            .profile-row {
                grid-template-columns: 1fr;
            }

            .profile-name {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <h1 class="profile-name"><?php echo htmlspecialchars(trim($fname . ' ' . $lname)); ?></h1>
            </div>
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Student ID</div>
                    <div class="field-value"><?php echo $mockProfile['studentId']; ?></div>
                </div>
                <div class="profile-field">
                    <div class="field-label">Email</div>
                    <div class="field-value"><?php echo $mockProfile['email']; ?></div>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Phone</div>
                    <div class="field-value"><?php echo $mockProfile['phone']; ?></div>
                </div>
                <div class="profile-field">
                    <div class="field-label">Address</div>
                    <div class="field-value"><?php echo $mockProfile['address']; ?></div>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Course</div>
                    <div class="field-value"><?php echo $mockProfile['course']; ?></div>
                </div>
                <div class="profile-field">
                    <div class="field-label">Year</div>
                    <div class="field-value"><?php echo $mockProfile['year']; ?></div>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-field">
                    <div class="field-label">Current Semester</div>
                    <div class="field-value"><?php echo $mockProfile['semester']; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>