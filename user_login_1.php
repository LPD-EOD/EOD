<?php
session_name("user_session");
session_start();

require_once "config.php";

if (isset($_SESSION["user_id"])) {
    header("Location: user_specific_feedback.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = array();
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Validate and sanitize the email and password inputs (add your own validation code here if needed)
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    $password = filter_var($password, FILTER_SANITIZE_STRING);

    // Prepare the SQL statement to select the user's password hash, unique_identifier, and evaluation_completed based on the given username
    $stmt = $pdo->prepare("SELECT password, id, unique_identifier, evaluation_completed FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $password_hash = $result['password'];
        $unique_identifier = $result['unique_identifier'];
        $evaluation_completed = $result['evaluation_completed'];
        $id = $result["id"];


        if (password_verify($password, $password_hash)) {
            header("Location: user_specific_feedback.php");

            // Update the user's status to "online" in the database
            $updateStatusStmt = $pdo->prepare("UPDATE users SET status = 'online' WHERE unique_identifier = :unique_identifier");
            $updateStatusStmt->execute(['unique_identifier' => $unique_identifier]);

            // Authentication successful, set the session variables and redirect to the determined URL
            session_start();

            $_SESSION["id"] = $id;
            $_SESSION["user_id"] = $username;
            $_SESSION["user_unique_id"] = $unique_identifier; // Store the unique_identifier in session
            $_SESSION["user_completed_evaluation"] = $evaluation_completed;
            $_SESSION["show_evaluation_modal"] = false;

            $updateSql = "UPDATE messages SET message_status = 'delivered' WHERE receiver_id = :userId AND message_status = 'sent'";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(":userId", $unique_identifier, PDO::PARAM_INT);
            $updateStmt->execute();

            exit();
        } else {
            $errors["error"] = 'Error: Invalid username or password.';
        }
    } else {
        // User with the given username not found, show an error message or redirect to the login page with an error message
        // You can customize this error message to fit your requirements.
        $errors["error"] = 'Error: Invalid username or password.';
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Building User Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #82c91e;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 400px;
            margin: 150px auto;
            padding: 30px;
            background-color: #3ca614;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); background-color: #3ca614; }
            50% { transform: translateY(-10px); background-color: #82c91e; }
        }
        h1 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: bold;
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
            background-color: #82c91e;
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
            background-color: #3ca614;
            border-color: #3ca614;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #82c91e;
            border-color: #82c91e;
        }
        .forgot-password {
            font-size: 14px;
            margin-top: 20px;
            text-align: center;
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
            background-color: #ff3333;
            color: #fff;
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
        <h1>Welcome to the End-Users Login</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="form-group form-control-wrapper">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <span class="toggle-password-btn" id="togglePassword">Show Password</span>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="link-container">
            <p class="forgot-password"><a href="user_forgot_password.php">Forgot Password?</a></p>
            <p class="registration-link"><a href="user_signup.php">New registration</a></p>
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
