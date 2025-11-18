<?php 
//General Design ng Portal (Same dapat sa Design 
// ng website nila pero minimalistic)

?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../account/login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use session data instead of hardcoding id = 1
$query = "SELECT * FROM account WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Management System</title>
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

        .header {
            background-color: var(--royal-blue);
            color: var(--white);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-container {
            display: flex;
            gap: 20px;
            padding: 20px;
            background-color: var(--white);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: none;
            border: none;
            color: var(--royal-blue);
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .nav-button:hover {
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .nav-button i {
            font-size: 1.2rem;
        }

        .user-info {
            color: var(--white);
            text-align: right;
        }

        /* Mobile responsive styles */
        @media screen and (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                padding: 0.5rem;
            }

            .logo {
                margin-bottom: 0.5rem;
                font-size: 1.2rem;
            }

            .user-info {
                text-align: center;
                width: 100%;
                margin-top: 0.5rem;
            }

            .nav-container {
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }

            .nav-button {
                width: 100%;
                justify-content: flex-start;
                padding: 15px;
                border-bottom: 1px solid #eee;
            }

            .nav-button:last-child {
                border-bottom: none;
            }

            .nav-button i {
                width: 25px;
            }
        }

        /* Small screen adjustments */
        @media screen and (max-width: 480px) {
            .logo {
                font-size: 1rem;
            }

            .nav-button {
                padding: 12px;
                font-size: 0.9rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="logo">University of Saint Louis</div>
        <div class="user-info">
            <div><?php echo $_SESSION['fname'] . ' ' . $_SESSION['lname']; ?></div>
            <div>Student</div>
        </div>
    </div>

    <div class="nav-container">
        <a href="dashboard.php" class="nav-button">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>
        <a href="profile.php" class="nav-button">
            <i class="fas fa-user"></i>
            Profile
        </a>
        <a href="schedule.php" class="nav-button">
            <i class="fas fa-calendar"></i>
            Schedule
        </a>
        <a href="grades.php" class="nav-button">
            <i class="fas fa-graduation-cap"></i>
            Grades Records
        </a>
        <a href="enrollment.php" class="nav-button">
            <i class="fas fa-edit"></i>
            Enrollment
        </a>
    </div>

    <div class="content">
        <!-- Content will be loaded here based on which button is clicked -->
    </div>
</body>
</html>