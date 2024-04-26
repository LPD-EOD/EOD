<?php
session_name("user_session");
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Type Selection</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: dodgerblue;
            color: #fff;
            padding: 10px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .back-link {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }

        .header-img {
            max-width: 100px;
            max-height: 100px;
            margin: 0 auto;
            display: block;
        }

        .my-h1-1 {
            margin: 0;
            font-size: 24px;
        }

        .container {
            text-align: center;
            margin-top: 20px;
        }

        .user-link {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            text-decoration: none;
            font-size: 18px;
            color: #fff;
            background-color: #2ecc71;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .user-link:hover {
            background-color: #27ae60;
        }

        /* Dropdown Styles */
        .dropdown {
            display: inline-block;
            position: relative;
        }
    </style>
</head>
<body>
    <header>
        <a class="back-link" href="index.html">&#8678; Back</a>
        <img src="Logo_final.png" alt="Logo" class="header-img">
        <h1 class="my-h1-1">Specific Feedback</h1>
    </header>
    <div class="container">
        <a href="user_specific_feedback.php" class="user-link">Registered User</a>
        <div class="dropdown">
            <a href="user_signup.php" onclick="<?php $_SESSION['followed_specific_feedback'] = true; ?>" class="user-link">Non-Registered User</a>
        </div>
    </div>
</body>
</html>
