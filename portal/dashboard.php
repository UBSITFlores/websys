<?php
session_start();

// Ensure the user is logged in (prefer session values set at login)
if (!isset($_SESSION['ACCOUNTID'])) {
    header('Location: ../account/login.php');
    exit();
}

$fname = $_SESSION['FNAME'] ?? '';
$lname = $_SESSION['LNAME'] ?? '';

// If session name values are missing, fetch from DB using account_id
if ($fname === '' || $lname === '') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "portal";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        error_log('DB connection failed in dashboard.php: ' . $conn->connect_error);
    } else {
        // account_id is expected to be the identifier stored in session
        $query = "SELECT fname, lname FROM account WHERE account_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('s', $_SESSION['ACCOUNTID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            if ($user) {
                $fname = $user['fname'] ?? $fname;
                $lname = $user['lname'] ?? $lname;
            }
            $stmt->close();
        }
        $conn->close();
    }
}
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

        /* Remove default page margins so header touches the viewport edges */
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

    <div class="dashboard-container">
        <div class="welcome-card">
            <h1 class="welcome-title">Welcome, <?php echo htmlspecialchars(trim($fname ?: 'Student')); ?>!</h1>
        </div>
    </div>
</body>
</html>