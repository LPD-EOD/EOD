<?php
session_name("admin_session");
session_start();

require_once "config.php";

// Initialize variables
$selected_dashboard = ""; // You can set this to 'developer_dashboard' or 'admin_dashboard' based on your requirements.

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]) {
    // Check the session to determine the user's dashboard
    $selected_dashboard = $_SESSION['dashboard'];
    if ($selected_dashboard === 'developer_dashboard') {
        header('Location: developer_dashboard.php');
        exit();
    } elseif ($selected_dashboard === 'manager_dashboard') {
        header('Location: manager_dashboard.php');
        exit();
    } else {
        header('location: admin_dashboard.php');
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();
    $email_or_username = $_POST["email_or_username"];
    $password = $_POST["password"];

    // Validate and sanitize the input (add your own validation code here if needed)
    $email_or_username = filter_var($email_or_username, FILTER_SANITIZE_STRING);
    $password = filter_var($password, FILTER_SANITIZE_STRING);

    // Determine if the provided input contains "@" to decide whether it's an email
    $is_email = (strpos($email_or_username, '@') !== false);

    // Initialize the SQL statement and the parameters array
    $sql = "";
    $params = array(':email_or_username' => $email_or_username);

    if ($is_email) {
        // Input appears to be an email, validate it as an email
        if (filter_var($email_or_username, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT email, password, profession, unique_identifier FROM admin_registration WHERE email = :email_or_username";
        } else {
            $errors['error'] = "Invalid email format.";
        }
    } else {
        // Input is assumed to be a username
        $sql = "SELECT username, password, profession, unique_identifier FROM admin_registration WHERE username = :email_or_username";
    }

    if (!empty($sql)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email_or_username = $is_email ? $result['email'] : $result['username'];
            $password_hash = $result['password'];
            $profession = $result['profession'];
            $unique_identifier = $result['unique_identifier'];

            if (password_verify($password, $password_hash)) {
                $_SESSION["logged_in"] = true;
                $_SESSION["email_or_username"] = $email_or_username;
                if ($profession === 'Developer') {
                    $_SESSION["dashboard"] = 'developer_dashboard';
                } elseif ($profession === 'Property Manager') {
                    $_SESSION["dashboard"] = 'manager_dashboard';
                } else {
                    $_SESSION["dashboard"] = 'admin_dashboard';
                }

                $updateStatusStmt = $pdo->prepare("UPDATE admin_registration SET status = 'online' WHERE unique_identifier = :unique_identifier");
                $updateStatusStmt->execute(['unique_identifier' => $unique_identifier]);

                $_SESSION["admin_id"] = $email_or_username;
                $_SESSION["admin_unique_id"] = $unique_identifier;

                $updateSql = "UPDATE messages SET message_status = 'delivered' WHERE receiver_id = :userId AND message_status = 'sent'";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(":userId", $unique_identifier, PDO::PARAM_INT);
                $updateStmt->execute();

                // Redirect to the appropriate dashboard based on the profession
                if ($_SESSION["dashboard"] == 'developer_dashboard') {
                    header("Location: developer_dashboard.php");
                    exit();
                } elseif ($_SESSION["dashboard"] == 'manager_dashboard') {
                    header("Location: manager_dashboard.php");
                    exit();
                } else {
                    header("Location: admin_dashboard.php");
                    exit();
                }
            } else {
                $errors["error"] = 'Error: Invalid email/username or password.';
            }
        } else {
            $errors["error"] = 'Error: Invalid email/username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Professionals Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #355E3B;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 400px;
            margin: 150px auto;
            padding: 30px;
            background-color: #98FB98;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); background-color: #27ae60; }
            50% { transform: translateY(-10px); background-color: #2ecc71; }
        }
        h1 {
            text-align: center;
            color: #fff;
        }
        .form-group label {
            font-weight: bold;
            color: #fff;
        }
        .form-control-wrapper {
            position: relative;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #fff;
            border-radius: 5px;
            margin-bottom: 15px;
            color: #fff;
            background-color: #27ae60;
        }
        .toggle-password-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: black;
        }
        .btn-primary {
            background-color: #27ae60;
            border-color: #27ae60;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        .link-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .forgot-password a, .registration-link a {
            color: #fff;
            text-decoration: none;
        }
        .error-message {
            background-color: #FF0000;
            color: #FFFFFF;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 10px;
        }
        .home-link {
            position: absolute;
            top: 10px;
            left: 10px;
            text-decoration: none;
            font-size: 35px;
            color: #fff;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.html" class="home-link"><i class="fas fa-home"></i></a>
    </header>
    <?php if (!empty($errors['error'])) : ?>
        <div class="error-message"><?php echo $errors['error']; ?></div>
    <?php endif; ?>
    <div class="container">
        <h1>Welcome to the Professionals Login</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <div class="form-group">
                <label for="email_or_username">Email or Username:</label>
                <input type="text" name="email_or_username" id="email_or_username" class="form-control" required>
            </div>

            <div class="form-group form-control-wrapper">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <span class="toggle-password-btn" id="togglePassword">Show Password</span>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="link-container">
            <p class="forgot-password"><a href="admin_forgot_password.php">Forgot Password?</a></p>
            <p class="registration-link"><a href="admin_signup.php">New registration</a></p>
        </div>
    </div>
    <script>
        const passwordField = document.getElementById('password');
        const togglePasswordButton = document.getElementById('togglePassword');

        togglePasswordButton.addEventListener('click', function() {
            togglePasswordVisibility(passwordField, togglePasswordButton);
        });

        function togglePasswordVisibility(inputField, toggleButton) {
            if (inputField.type === 'password') {
                inputField.type = 'text';
                toggleButton.textContent = 'Hide Password';
            } else {
                inputField.type = 'password';
                toggleButton.textContent = 'Show Password';
            }
        }
    </script>
</body>
</html>
