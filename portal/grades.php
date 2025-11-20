<?php
session_start();

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
    ['course' => 'Web Systems and Technologies', 'midterm' => '88', 'final' => '90', 'grade' => '1.25', 'remarks' => 'Excellent'],
    ['course' => 'Database Management', 'midterm' => '85', 'final' => '87', 'grade' => '1.50', 'remarks' => 'Very Good'],
    ['course' => 'Software Engineering', 'midterm' => '92', 'final' => '94', 'grade' => '1.00', 'remarks' => 'Excellent'],
    ['course' => 'Mobile App Development', 'midterm' => '80', 'final' => '82', 'grade' => '2.00', 'remarks' => 'Good'],
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

        .grades-container {
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .grades-title {
            color: var(--royal-blue);
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .grades-table {
            background-color: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: var(--royal-blue);
            color: var(--white);
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        .grade-excellent { color: #28a745; font-weight: bold; }
        .grade-good { color: #17a2b8; font-weight: bold; }
        .grade-satisfactory { color: #ffc107; font-weight: bold; }

        @media screen and (max-width: 768px) {
            .grades-container {
                padding: 1rem;
            }

            .grades-title {
                font-size: 1.5rem;
            }

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="header" style="background-color:var(--royal-blue); color:var(--white); padding:1rem; display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a class="back-button" href="index.php">← Home</a>
            <div class="logo">University of Saint Louis</div>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="user-info">
                <div><?php echo htmlspecialchars(trim($fname . ' ' . $lname)); ?></div>
                <div>Student</div>
            </div>
            <form action="../account/logout.php" method="post" style="margin:0;">
                <button type="submit" name="logout" class="logout-button">Logout</button>
            </form>
        </div>
    </div>

<?php 
if(isset($_POST['logout'])){
            header("Location: ../account/login.php");
            exit();
    }

?>
    <div class="grades-container">
        <h1 class="grades-title">Grades Records</h1>
        
        <div class="grades-table">
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Midterm</th>
                        <th>Final</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($mockGrades as $grade): ?>
                    <tr>
                        <td><?php echo $grade['course']; ?></td>
                        <td><?php echo $grade['midterm']; ?></td>
                        <td><?php echo $grade['final']; ?></td>
                        <td><?php echo $grade['grade']; ?></td>
                        <td class="grade-excellent"><?php echo $grade['remarks']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>