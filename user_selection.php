<?php
require_once 'config.php';

// Ensure that the username is set in the URL
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Fetch the unique_identifier from the users table
    $stmt = $pdo->prepare("SELECT unique_identifier FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result = $stmt->fetch();

    // Check if the user exists and has a unique_identifier
    if ($result && isset($result['unique_identifier'])) {
        $unique_identifier = $result['unique_identifier'];
    } else {
        echo "Error: User not found or doesn't have a unique_identifier.";
        exit;
    }
} else {
    echo "Error: Username not provided in the URL.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Selection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        p {
            text-align: center;
            color: #777;
        }
        ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
        }
        li {
            margin: 10px 0;
        }
        a {
            text-decoration: none;
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_GET['username']); ?>!</h1>
    <p>Welcome to User Selection</p>
    <p>Choose an option:</p>
    <ul>
        <li><a href="register_building.php?unique_identifier=<?php echo $unique_identifier; ?>">Register a Building</a></li>
        <li><a href="evaluation_question.php">Submit General Evaluation</a></li>
    </ul>
</body>
</html>