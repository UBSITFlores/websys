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

// Mock schedule data
$mockSchedule = [
    ['course' => 'Web Systems and Technologies', 'instructor' => 'Dr. Juan Santos', 'day' => 'Monday', 'time' => '9:00 AM - 11:00 AM', 'room' => 'Room 101'],
    ['course' => 'Database Management', 'instructor' => 'Prof. Maria Cruz', 'day' => 'Tuesday', 'time' => '1:00 PM - 3:00 PM', 'room' => 'Room 205'],
    ['course' => 'Software Engineering', 'instructor' => 'Dr. Jose Reyes', 'day' => 'Wednesday', 'time' => '10:00 AM - 12:00 PM', 'room' => 'Room 302'],
    ['course' => 'Mobile App Development', 'instructor' => 'Prof. Anna Lopez', 'day' => 'Thursday', 'time' => '2:00 PM - 4:00 PM', 'room' => 'Lab 1'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - Academic Management System</title>
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

        .schedule-container {
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .schedule-title {
            color: var(--royal-blue);
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .schedule-table {
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

        @media screen and (max-width: 768px) {
            .schedule-container {
                padding: 1rem;
            }

            .schedule-title {
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
    <div class="schedule-container">
        <h1 class="schedule-title">Class Schedule</h1>
        
        <div class="schedule-table">
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($mockSchedule as $class): ?>
                    <tr>
                        <td><?php echo $class['course']; ?></td>
                        <td><?php echo $class['instructor']; ?></td>
                        <td><?php echo $class['day']; ?></td>
                        <td><?php echo $class['time']; ?></td>
                        <td><?php echo $class['room']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>