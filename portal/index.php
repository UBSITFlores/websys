<?php
session_start();

// Check logout FIRST before anything else
if(isset($_POST['logout'])){
    session_destroy();
    header("Location: ../account/logout.php");
    exit();
}

// Check if user is logged in and has the student role
if (!isset($_SESSION['ACCOUNTID']) || ($_SESSION['ROLE'] ?? '') !== 'student') {
    header("Location: ../account/login.php");
    exit();
}

// Prefer session-stored name values; fall back to DB if missing
$fname = $_SESSION['FNAME'] ?? '';
$lname = $_SESSION['LNAME'] ?? '';

// If names are missing in session, try to fetch from DB using account_id
if ($fname === '' || $lname === '') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "portal";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        error_log('DB connection failed in portal/index.php: ' . $conn->connect_error);
    } else {
        $query = "SELECT * FROM account WHERE account_id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $_SESSION['ACCOUNTID']);
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

// Get the page to display (default is dashboard)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// UPDATED: Added 'payment' to the allowed list
$allowed_pages = ['dashboard', 'profile', 'schedule', 'grades', 'enrollment', 'payment'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Management System</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="logo">University of Saint Louis</div>
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="user-info">
                <div><?php echo htmlspecialchars(trim($fname . ' ' . $lname)); ?></div>
                <div>Student</div>
            </div>
            <form method="post" style="margin:0;">
                <button type="submit" name="logout" class="logout-button">Logout</button>
            </form>
        </div>
    </div>

    <div class="nav-container">
        <a href="?page=dashboard" class="nav-button <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>
        <a href="?page=profile" class="nav-button <?php echo $page === 'profile' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            Profile
        </a>
        <a href="?page=schedule" class="nav-button <?php echo $page === 'schedule' ? 'active' : ''; ?>">
            <i class="fas fa-calendar"></i>
            Schedule
        </a>
        <a href="?page=grades" class="nav-button <?php echo $page === 'grades' ? 'active' : ''; ?>">
            <i class="fas fa-graduation-cap"></i>
            Grades Records
        </a>
        <a href="?page=enrollment" class="nav-button <?php echo $page === 'enrollment' ? 'active' : ''; ?>">
            <i class="fas fa-edit"></i>
            Enrollment
        </a>
        <a href="?page=payment" class="nav-button <?php echo ($page == 'payment') ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i>
            Accounts
        </a>
    </div>

    <div class="content-area">
        <?php
        // Load the appropriate page content
        switch($page) {
            case 'dashboard':
                include 'dashboard.php';
                break;
            case 'profile':
                include 'profile.php';
                break;
            case 'schedule':
                include 'schedule.php';
                break;
            case 'grades':
                include 'grades.php';
                break;
            case 'enrollment':
                include 'enrollment.php';
                break;
            // UPDATED: Added case for payment
            case 'payment':
                include 'payment.php';
                break;
        }
        ?>
    </div>
</body>
</html>