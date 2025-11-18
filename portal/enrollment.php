<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mock enrollment data
$mockEnrollment = [
    ['course' => 'Web Systems and Technologies', 'units' => '3', 'status' => 'Enrolled', 'dateEnrolled' => '2024-08-15'],
    ['course' => 'Database Management', 'units' => '3', 'status' => 'Enrolled', 'dateEnrolled' => '2024-08-15'],
    ['course' => 'Software Engineering', 'units' => '4', 'status' => 'Enrolled', 'dateEnrolled' => '2024-08-16'],
    ['course' => 'Mobile App Development', 'units' => '3', 'status' => 'Enrolled', 'dateEnrolled' => '2024-08-16'],
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

        .enrollment-container {
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .enrollment-title {
            color: var(--royal-blue);
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .enrollment-table {
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

        .status-badge {
            background-color: #28a745;
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        @media screen and (max-width: 768px) {
            .enrollment-container {
                padding: 1rem;
            }

            .enrollment-title {
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
    <div class="enrollment-container">
        <h1 class="enrollment-title">Course Enrollment</h1>
        
        <div class="enrollment-table">
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th>Date Enrolled</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($mockEnrollment as $enrollment): ?>
                    <tr>
                        <td><?php echo $enrollment['course']; ?></td>
                        <td><?php echo $enrollment['units']; ?></td>
                        <td><span class="status-badge"><?php echo $enrollment['status']; ?></span></td>
                        <td><?php echo $enrollment['dateEnrolled']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>