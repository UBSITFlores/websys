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

// Mock schedule data
$mockSchedule = [
    ['time' => '8:00 AM - 9:30 AM', 'course' => 'Data Structures', 'instructor' => 'Dr. Smith', 'room' => 'Room 101'],
    ['time' => '10:00 AM - 11:30 AM', 'course' => 'Web Development', 'instructor' => 'Ms. Johnson', 'room' => 'Lab 2'],
    ['time' => '1:00 PM - 2:30 PM', 'course' => 'Database Management', 'instructor' => 'Prof. Williams', 'room' => 'Room 205'],
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

        .schedule-card {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }
        .schedule-title {
            color: #002D72;
            font-size: 1.8rem;
            margin-bottom: 2rem;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }
        .schedule-table th {
            background-color: #002D72;
            color: white;
            padding: 1rem;
            text-align: left;
        }
        .schedule-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        .schedule-table tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="schedule-container">
        <h1 class="schedule-title">Class Schedule</h1>
        
        <div class="schedule-card">
            <h2 class="schedule-title">Class Schedule</h2>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($mockSchedule as $class): ?>
                    <tr>
                        <td><?php echo $class['time']; ?></td>
                        <td><?php echo $class['course']; ?></td>
                        <td><?php echo $class['instructor']; ?></td>
                        <td><?php echo $class['room']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>