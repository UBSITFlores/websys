<?php

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

$fname = $_SESSION['FNAME'] ?? '';
$lname = $_SESSION['LNAME'] ?? '';

// If names missing, try to fetch from DB
if ($fname === '' || $lname === '') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "portal";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if (!$conn->connect_error) {
        $query = "SELECT fname,lname FROM account WHERE account_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('s', $_SESSION['ACCOUNTID']);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            if ($user) {
                $fname = $user['fname'] ?? $fname;
                $lname = $user['lname'] ?? $lname;
            }
            $stmt->close();
        }
        $conn->close();
    }
}

// Mock enrollment data
$mockEnrollment = [
    ['code' => 'CS101', 'course' => 'Data Structures', 'units' => 3, 'status' => 'Enrolled'],
    ['code' => 'CS102', 'course' => 'Web Development', 'units' => 4, 'status' => 'Enrolled'],
    ['code' => 'CS103', 'course' => 'Database Management', 'units' => 3, 'status' => 'Enrolled'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment - Academic Management System</title>
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

        .enrollment-card {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }

        .enrollment-title {
            color: #002D72;
            font-size: 1.8rem;
            margin-bottom: 2rem;
        }

        .enrollment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .enrollment-table th {
            background-color: #002D72;
            color: white;
            padding: 1rem;
            text-align: left;
        }

        .enrollment-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .enrollment-table tr:hover {
            background-color: #f5f5f5;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }

        @media screen and (max-width: 768px) {
            .enrollment-card {
                padding: 1rem;
            }

            .enrollment-title {
                font-size: 1.5rem;
            }

            .enrollment-table {
                font-size: 0.9rem;
            }

            .enrollment-table th, .enrollment-table td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="enrollment-card">
        <h2 class="enrollment-title">Enrollment</h2>
        <table class="enrollment-table">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Units</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($mockEnrollment as $enrollment): ?>
                <tr>
                    <td><?php echo $enrollment['code']; ?></td>
                    <td><?php echo $enrollment['course']; ?></td>
                    <td><?php echo $enrollment['units']; ?></td>
                    <td><span class="status-badge"><?php echo $enrollment['status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>