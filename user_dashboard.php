<?php
session_name("user_session");
session_start();
require_once "config.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // If the user is not logged in, redirect them to the login page
    header("Location: user_login.php");
    exit();
}

// Check if it's time to show the evaluation modal
$userId = $_SESSION["id"]; // Adjust this based on your session variable


$query = "SELECT title, first_name, last_name FROM users WHERE id = :userId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':userId', $userId);
$stmt->execute();
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if any of the profile fields are empty or null
$incompleteProfile = false;
foreach ($userProfile as $field) {
    if (empty($field) || is_null($field)) {
        $incompleteProfile = true;
        break;
    }
}

$userId = $_SESSION["user_unique_id"];

// Check for unanswered invites in the user_invites table
$unansweredInvites = false;
try {
    $query = "SELECT COUNT(*) FROM user_invites WHERE user_id = :userId AND invite_answered = 0";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $unansweredInvitesCount = $stmt->fetchColumn();

    // If there are unanswered invites, set the $unansweredInvites variable to true
    if ($unansweredInvitesCount > 0) {
        $unansweredInvites = true;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>End-user Dashboard</title>
    <!-- Add your CSS styling here or link to an external CSS file -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #B8BBBE;
        }

        /* Header Styles */
        header {
            background-color: white;
            color: black;
            text-align: center;
            padding: 20px 0;
            border: 2px solid black;
        }

        .chat-button {
            cursor: pointer;
            font-size: 24px;
            margin-right: 20px;
            color: #fff;
        }

        .chat-button:hover {
            color: #00bcd4;
        }

        .chart-link {
            text-decoration: none;
            color: #fff;
            display: flex;
            align-items: center;
        }

        .chart-link:hover {
            color: #00bcd4;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-container h1 {
            font-size: 24px;
        }

        .icon-container {
            display: flex;
            align-items: center;
        }

        .icon-container a {
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
            font-size: 20px;
        }

        .logout-link {
            text-decoration: none;
            color: black;
            font-size: 24px;
            margin-right: 20px;
            padding-left: 50px;
        }

        .logout-link:hover {
            color: #ff0000; /* Change the color on hover */
        }

        /* Styling for the notification icon and badge */
        .notification-icon {
            position: relative;
            cursor: pointer;
            margin-right: 10px;
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
        }

        .header-img {
            width: 80px;
            height: 70px;
        }

        /* Styling for the notification dropdown */
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 40px; /* Adjust this value to position the dropdown as needed */
            right: 0;
            width: 300px; /* Adjust the width as needed */
            background-color: #fff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
            z-index: 999;
            max-height: 300px; /* Set a max height and add overflow-y for scroll if needed */
            overflow-y: auto;
        }

        /* Styling for individual notifications in the dropdown */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .notification-item:last-child {
            border-bottom: none; /* Remove border for the last item */
        }

        /* Styling for notification text and timestamp */
        #notificationList {
            font-weight: bold;
            color: black;
        }

        /* CSS for notification items */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* CSS for timestamps */
        .timestamp {
            font-size: 12px;
            color: #888;
        }

        a {
            text-decoration: none;
        }

        .ies {
            color: white;
            text-align: center;
        }

        .ies-container {
            display: inline-block;
            background-color: #5386B6;
            padding: 10px;
            margin-left: 80px;
            margin-top: 20px;
        }

        .first-compartment {
            border: 2px solid black;
            background-color: #D2D1E3;
        }

        .second-compartment-heading {
            text-align: center;
            text-decoration: underline;
        }

       /* CSS for the image grid layout */
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            justify-items: center;
            align-items: center;
            margin: 10px;
        }

        .image-grid img {
            cursor: pointer;
            max-width: 220px;
            max-height: 220px;
            width: 100%;
            height: 100%;
        }

        /* CSS for the displayed image and details */
        .displayed-image-container {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: flex-start; /* Align items at the start of the flex container */
            margin-top: 20px;
        }

        .image-details {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }

        .image-details-container {
            display: flex;
            flex-direction: row;
            margin-left: 20px; /* Add margin between image and details */
            gap: 20px;
        }

        .displayed-image {
            max-width: 100%;
            max-height: 300px; /* Adjust the height as needed */
            margin-top: 20px;
        }

        .image-details-text {
            flex: 1; /* Allow the text to take up remaining space */
            background: linear-gradient(45deg, #ffcc00, #ff6699); /* Gradient colors, adjust as needed */
            padding: 20px; /* Add padding for better visual appeal */
            border-radius: 8px; /* Add rounded corners */
            margin-top: 20px;
        }

        .image-texts {
            margin-bottom: 10px; /* Add margin between detail texts */
        }

        /* Updated styles for feedback form container */
        .feedback-form-container {
            background-color: #fce4ec; /* Lavender Pink */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            width: 50%; /* Occupy full width */
        }

        .feedback-toggle {
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
            width: 100%; /* Occupy full width */
        }

        .feedback-form-heading {
            color: #e91e63; /* Pink */
            font-size: 2rem; /* Adjust the font size as needed */
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #673ab7; /* Deep Purple */
        }

        .label {
            font-size: 1rem; /* Adjust the font size as needed */
            font-weight: normal; /* Use normal font weight */
            color: #673ab7; /* Deep Purple */
            margin-bottom: 8px; /* Add margin between label and text */
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            box-sizing: border-box;
            border: 2px solid #ec407a; /* Pink */
            border-radius: 4px;
            font-size: 1rem; /* Adjust the font size as needed */
        }

        button {
            background-color: #4caf50; /* Green */
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.2rem; /* Adjust the font size as needed */
        }

        button:hover {
            background-color: #45a049; /* Darker Green */
        }

        .feedback-toggle p {
            color: #e91e63; /* Pink */
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .arrow-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .arrow {
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-bottom: 15px solid #e91e63; /* Pink */
            margin: 5px;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        /* Add a class to hide the feedback form by default */
        .feedback-form-container.hidden {
            display: none;
        }
        .header-img {
            width: 80px;
            height: 70px;
        }

        .navbar-toggle {
            font-size: 24px;
            cursor: pointer;
            margin-left: 20px;
            color: #fff;
        }

        .navbar-expanded ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .navbar-expanded ul li {
            border-bottom: 1px solid #555;
        }

        .navbar-expanded ul li:last-child {
            border-bottom: none;
        }

        .navbar-expanded ul li a {
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            color: #fff;
        }

        .navbar-expanded ul li a i {
            margin-right: 10px;
        }

        .navbar-expanded {
            position: fixed;
            top: 100px;
            right: -300px;
            width: 150px;
            height: calc(160px - 60px);
            background-color: white;
            transition: right 0.3s ease-in-out;
            overflow-y: auto;
            z-index: 1000;
        }
        .navbar-icon {
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            margin: 0 10px;
        }

        .navbar-expanded.show {
            right: 0;
        }

        /* Hover styles */
        .navbar-expanded ul li:hover {
            background-color: #555;
        }

        .navbar-expanded ul li a:hover {
            color: #fff;
        }

        .navbar-toggle {
            cursor: pointer;
            font-size: 24px;
            color: #fff;
            margin-right: 20px;
        }

        .navbar-toggle:hover {
            color: #00bcd4;
        }

        /* Center the modal */
        .modal {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
            overflow: auto;
        }
        .modal-content {
            background-color: #f4f4f4;
            max-width: 400px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            overflow-y: auto;
            max-height: 80%;
        }
        .modal h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #075e54;
        }
        .modal p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }
        .modal select,
        .modal input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            color: #333;
        }
        .modal button {
            background-color: #25D366;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        .modal button:hover {
            background-color: #128C7E;
        }

        /* Style for dropdowns */
        .custom-dropdown {
            position: relative;
            margin-bottom: 20px;
        }
        .custom-dropdown-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            color: #333;
            background-color: #fff;
            cursor: pointer;
        }
        .custom-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #f4f4f4;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 4px 4px;
            z-index: 1;
            max-height: 150px;
            overflow-y: auto;
        }
        .custom-dropdown-content label {
            display: block;
            padding: 10px;
            cursor: pointer;
            color: #333;
        }
        .custom-dropdown-content label:hover {
            background-color: #e0e0e0;
        }

        .close-button-1 {
            background-color: #25D366;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            padding: 10px;
            margin: 10px;
            cursor: pointer;
            width: 50px;
        }

        .close-button-1:hover {
            background-color: #128C7E;
        }

        /* Styling for the notification icon and badge */
        .notification-icon {
            position: relative;
            cursor: pointer;
        }

        .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
        }

        /* Styling for the notification dropdown */
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 40px; /* Adjust this value to position the dropdown as needed */
            right: 0;
            width: 300px; /* Adjust the width as needed */
            background-color: #fff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
            z-index: 999;
            max-height: 300px; /* Set a max height and add overflow-y for scroll if needed */
            overflow-y: auto;
        }

        /* Styling for individual notifications in the dropdown */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .notification-item:last-child {
            border-bottom: none; /* Remove border for the last item */
        }

        /* Styling for notification text and timestamp */
        #notificationList {
            font-weight: bold;
            color: black;
        }

        /* CSS for notification items */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* CSS for timestamps */
        .timestamp {
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<!-- ... Existing code ... -->
<body>
    <header>
        <div class="header-container">
            <h1>User Dashboard</h1>
            <img src="Logo_final.png" alt="Logo" class="header-img"> 
            <div class="icon-container">
                <div class="chat-button">
                    <a href="chat.php"><i class="fas fa-comment-alt" style="color:#835237; font-size:40px;"></i></a>
                </div>

                <div class="header-item">
                    <a class="color" href="evaluation_question.php"> <!-- Add this code -->
                        <i class="fas fa-star" style="font-size: 24px; color: #FFD700;"></i>
                        <span style="color: black;">Evaluation</span>
                    </a>
                </div>
                <!-- Notifications Section -->
                <div class="notifications">
                    <!-- Notification Bell Icon -->
                    <div class="notification-icon" id="notificationIcon">
                        <i class="fas fa-bell"></i>
                        <!-- You can add a badge to show the number of unread notifications -->
                        <span class="badge" id="notificationBadge">0</span>
                    </div>

                    <!-- Notification Dropdown (Initially Hidden) -->
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div id="notificationList">
                            <!-- Notifications will be dynamically added here -->
                        </div>
                    </div>
                </div>
                <div class="navbar-toggle" onclick="toggleNavbar()">
                    <i class="fas fa-cog" style="color: black;"></i>
                </div>
            </div>
        </div>
        <nav class="navbar-expanded" id="navbar">
            <ul>
                <li><a href="user_profile.php" class="navbar-icon" style="color: red;"><i class="fas fa-user" style="color: black"></i>Profile</a></li>
                <li><a href="user_logout.php" class="navbar-icon" style="color: red;"><i class="fas fa-sign-out-alt" style="color: black"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php
            // Display a message with a link to complete the profile if it's incomplete
            if ($incompleteProfile) {
                echo "<div class='profile-incomplete-message'>Your profile is incomplete. <a href='complete_profile.php'>Complete Profile</a></div>";
            }
        ?>
        <div class="first-compartment">
            <a href="requirement_queries.php" class="ies-link">
                <div class="ies-container">
                    <h1 class="ies">Requirements & Queries</h1>
                </div>
            </a><br>
            <a href="user_register_property.php">
                <div class="ies-container" style="background-color:#454343; <?php echo $unansweredInvites ? '' : 'margin-bottom: 25px;'; ?>">
                    <h1 class="ies">Select New Property</h1>
                </div>
            </a><br>
            <a href="feedback_answer.php" style="<?php echo $unansweredInvites ? '' : 'display: none;'; ?>">
                <div class="ies-container" style="background-color:#ADA160; margin-bottom: 25px;">
                    <h1 class="ies">You have Unanswered Feedback</h1>
                </div>
            </a>
        </div>
        <h1 class="second-compartment-heading">New Listing (Click for more details)</h1>
        <div class="second-compartment">
        </div>
    </main>
    <script>
        // Function to toggle the notification dropdown
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Function to fetch notifications for a specific user
        function fetchNotifications() {
            // Create a new FormData object to send the user ID
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);

            // Send the AJAX request
            fetch('fetch_notifications.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                // Display notifications in the dropdown
                displayNotifications(data);
                
                // Update the badge count based on unread notifications
                const unreadNotifications = data.filter(notification => notification.read_status === 0);
                const badge = document.getElementById('notificationBadge');
                badge.textContent = unreadNotifications.length;
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        setInterval(fetchNotifications, 5000);
        // Attach click event to the notification icon (assuming you have a userId variable)
        const notificationIcon = document.getElementById('notificationIcon');
        notificationIcon.addEventListener('click', () => {
            toggleNotificationDropdown();
            markAllNotificationsAsRead();
            fetchNotifications(); // Pass the user ID to the fetchNotifications function
        });

        // Close the dropdown when clicking outside of it
        document.addEventListener('click', event => {
            if (!event.target.closest('.notifications')) {
                const dropdown = document.getElementById('notificationDropdown');
                dropdown.style.display = 'none';
            }
        });


        function displayNotifications(notifications) {
            const notificationsContainer = document.getElementById('notificationList');
            
            // Clear previous notifications
            notificationsContainer.innerHTML = '';

            if (notifications.length === 0) {
                // Display a message if there are no notifications with text color set to black
                notificationsContainer.innerHTML = '<p style="color: black;">No notifications.</p>';
            } else {
                // Loop through the notifications and create HTML elements to display them
                notifications.forEach(notification => {
                    const notificationDiv = document.createElement('div');
                    notificationDiv.classList.add('notification-item'); // Add a class for styling

                    // Set the text color to black for notification messages
                    notificationDiv.innerHTML = `
                        <p style="color: black;">${notification.message}</p>
                        <p class="timestamp">${notification.created_at}</p>
                    `;
                    
                    // Add an event listener to mark the notification as read when clicked
                    notificationDiv.addEventListener('click', () => {
                        // Update the read status to 1 (read)
                        markNotificationAsRead(notification.id);
                        
                        // Redirect or perform other actions as needed when a notification is clicked
                    });
                    
                    notificationsContainer.appendChild(notificationDiv);
                });
            }
        }

        function markAllNotificationsAsRead() {
            // Create a new FormData object to send the user ID
            const formData = new FormData();
            formData.append('userId', <?php echo $userId; ?>);

            // Send the AJAX request to mark all notifications as read
            fetch('mark_notification_as_read.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                // Handle the response, e.g., update the UI or perform other actions
                if (data.success) {
                    // All notifications have been marked as read; you can update the UI here if needed
                } else {
                    // Handle errors if necessary
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        // Call the fetch functions to load queries when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchNotifications();
        });

        // Function to fetch and display recent images in a grid layout
        function fetchRecentImages() {
            fetch('fetch_recent_images.php')
                .then(response => response.json())
                .then(data => {
                    const secondCompartment = document.querySelector('.second-compartment');
                    secondCompartment.innerHTML = '';

                    if (data.length > 0) {
                        const gridContainer = document.createElement('div');
                        gridContainer.className = 'image-grid';
                        secondCompartment.appendChild(gridContainer);

                        data.forEach(image => {
                            const imageElement = document.createElement('img');
                            imageElement.src = image.image_path;
                            imageElement.alt = image.apartment_type;

                            // Add a click event to display image details
                            imageElement.addEventListener('click', () => {
                                displayImageDetails(image);
                            });

                            gridContainer.appendChild(imageElement);
                        });
                    } else {
                        secondCompartment.textContent = 'No recent images found.';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function displayImageDetails(image) {
            const secondCompartment = document.querySelector('.second-compartment');
            secondCompartment.innerHTML = `
                <div class="displayed-image-container">
                    <button id="closeButton" class="close-button"><i class="fas fa-times"></i></button>
                    <div class="image-details-container">
                        <img src="${image.image_path}" alt="${image.apartment_type}" class="displayed-image">
                        <div class="image-details">
                            <div class="image-details-text">
                                <h2 class="feedback-form-heading">Image Details</h2>
                                <div class="image-texts">
                                    <h2 class="label">Title:</h2>
                                    <p style="color: red;">${image.apartment_type}</p>
                                </div>
                                <div class="image-texts">
                                    <h2 class="label">Details:</h2>
                                    <p style="color: red;">${image.other_details}</p>
                                </div>
                                <div class="image-texts">
                                    <h2 class="label">Created at:</h2>
                                    <p style="color: red;">${image.created_at}</p>
                                </div>
                                <p class="disclaimer">Acknowledgement: The images used on this platform are for demonstration only and are downloaded from Centaline Property Agency Limited website.</p>
                                <!-- Feedback toggle -->
                                <div id="feedbackToggle" class="feedback-toggle">
                                    <p>Click for Feedback</p>
                                    <div class="arrow-container">
                                        <div class="arrow"></div>
                                        <div class="arrow"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Feedback form container -->
                            <div id="feedbackForm" class="feedback-form-container hidden">
                                <h2 class="feedback-form-heading">Feedback Form</h2>
                                <form id="imageDetailsForm">
                                    <label for="gmail">Gmail:</label>
                                    <input type="text" id="gmail" name="gmail" placeholder="Enter your Gmail">

                                    <label for="text">Text:</label>
                                    <textarea id="text" name="text" placeholder="Enter your text"></textarea>

                                    <button type="button" id="submitButton">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add styles for the disclaimer text
            const disclaimerStyle = `
                .disclaimer {
                    font-style: italic;
                    color: black;
                    font-size: 12px;
                    margin-top: 10px;
                }
            `;

            // Create a style element and append it to the head
            const styleElement = document.createElement('style');
            styleElement.innerHTML = disclaimerStyle;
            document.head.appendChild(styleElement);

            // Add an event listener to close the image details
            const closeButton = document.getElementById('closeButton');
            closeButton.addEventListener('click', () => {
                fetchRecentImages(); // Reload recent images when closing
            });

            const submitButton = document.getElementById('submitButton');
            submitButton.addEventListener('click', () => {
                const gmailValue = document.getElementById('gmail').value;
                const textValue = document.getElementById('text').value;

                // Get the property_id and user_id from the image object and other relevant source
                const propertyId = image.property_id;
                const userId = <?php echo $userId ?>;

                // Call the handleSubmitForm function with all values
                handleSubmitForm(propertyId, userId, gmailValue, textValue);
            });

            // Add an event listener to toggle the feedback form visibility
            const feedbackToggle = document.getElementById('feedbackToggle');
            const feedbackFormContainer = document.getElementById('feedbackForm');

            feedbackToggle.addEventListener('click', () => {
                feedbackFormContainer.classList.toggle('hidden');
                // Update the text based on the current state
                const feedbackToggleText = feedbackFormContainer.classList.contains('hidden') ? 'Click for Feedback' : 'Click to Close Feedback';
                feedbackToggle.querySelector('p').textContent = feedbackToggleText;
            });
        }

        // Function to handle form submission
        function handleSubmitForm(propertyId, userId, gmailValue, textValue) {

            // Optionally, you can clear the form fields after submission
            document.getElementById('gmail').value = '';
            document.getElementById('text').value = '';

            fetch('save_feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ property_id: propertyId, user_id: userId, gmail: gmailValue, text: textValue }),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Call the fetchRecentImages function to load recent images when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchRecentImages();
        });

        function toggleNavbar() {
            const navbar = document.getElementById("navbar");
            navbar.classList.toggle("show");
        }

        function closeNavbar() {
            document.getElementById("navbar-collapsed").style.width = "0";
        }
    </script>
</body>
</html>
