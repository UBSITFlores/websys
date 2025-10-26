<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM account WHERE id = 1"; 
$result = $conn->query($query);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Academic Management System</title>
    <style>
        :root {
            --royal-blue: #002D72;
            --white: #ffffff;
        }

        .dashboard-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 1rem;
        }

        .welcome-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
        }

        .welcome-title {
            color: var(--royal-blue);
            font-size: 2rem;
            margin: 0;
        }

        @media screen and (max-width: 480px) {
            .welcome-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="welcome-card">
            <h1 class="welcome-title">Welcome, <?php echo isset($user['fname']) ? $user['fname'] : 'Student'; ?>!</h1>
        </div>
    </div>
</body>
</html>