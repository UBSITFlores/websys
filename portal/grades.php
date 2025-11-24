<?php

if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header('Location: ../account/login.php');
    exit();
}

$fname = $_SESSION['FNAME'] ?? '';
$lname = $_SESSION['LNAME'] ?? '';

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

// Mock grades data
$mockGrades = [
    ['course' => 'Data Structures', 'midterm' => '88', 'final' => '92', 'grade' => '90'],
    ['course' => 'Web Development', 'midterm' => '85', 'final' => '87', 'grade' => '86'],
    ['course' => 'Database Management', 'midterm' => '90', 'final' => '94', 'grade' => '92'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades Records - Academic Management System</title>
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

        .grades-card {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }

        .grades-title {
            color: #002D72;
            font-size: 1.8rem;
            margin-bottom: 2rem;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
        }

        .grades-table th {
            background-color: #002D72;
            color: white;
            padding: 1rem;
            text-align: left;
        }

        .grades-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .grades-table tr:hover {
            background-color: #f5f5f5;
        }

        .grade-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: bold;
        }

        .grade-excellent {
            background-color: #d4edda;
            color: #155724;
        }

        @media screen and (max-width: 768px) {
            .grades-card {
                padding: 1rem;
            }

            .grades-title {
                font-size: 1.5rem;
            }

            .grades-table {
                font-size: 0.9rem;
            }

            .grades-table th, .grades-table td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>

    <div class="grades-card">
        <h2 class="grades-title">Grades Records</h2>
        <table class="grades-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Midterm</th>
                    <th>Final</th>
                    <th>Overall Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($mockGrades as $grade): ?>
                <tr>
                    <td><?php echo $grade['course']; ?></td>
                    <td><?php echo $grade['midterm']; ?></td>
                    <td><?php echo $grade['final']; ?></td>
                    <td><span class="grade-badge grade-excellent"><?php echo $grade['grade']; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>