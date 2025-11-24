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
            min-height: 60vh;
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
            color: #002D72;
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
            <h1 class="welcome-title">Welcome, <?php echo htmlspecialchars(trim($fname ?: 'Student')); ?>!</h1>
        </div>
    </div>
</body>
</html>