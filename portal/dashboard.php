<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard - Academic Management System</title>
        <link rel="stylesheet" href="dashboard.css">
    </head>
    <body>
        <div class="dashboard-container">
            <div class="welcome-card">
                <h1 class="welcome-title">Welcome, <?php echo htmlspecialchars(trim($fname ?: 'Student')); ?>!</h1>
            </div>
        </div>
    </body>
</html>