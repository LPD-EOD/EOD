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

$userId = $_SESSION["user_unique_id"];

// If the user is logged in, continue displaying the dashboard
$updateSql = "UPDATE messages SET message_status = 'delivered' WHERE receiver_id = :userId AND message_status = 'sent'";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->bindParam(":userId", $userId, PDO::PARAM_INT);
$updateStmt->execute();

// ...

try {
    require_once "config.php";
    
    // Fetch the user's property names
    $userPropertyNames = [];
    $query = "SELECT property_name FROM user_property_registration WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $userPropertyNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Check if there are user property names
    if (!empty($userPropertyNames)) {
        // Initialize an empty array to store all admins for the user
        $allUserAdmins = [];

        // Loop through each property name
        foreach ($userPropertyNames as $userProperty) {
            // Fetch admins from property_registration matching the user's property name
            $propertyAdmins = [];
            $query = "SELECT user_id FROM property_registration WHERE property_name = :property_name";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':property_name', $userProperty);
            $stmt->execute();
            $propertyUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch admin details from admin_registration for matching users
            if (!empty($propertyUsers)) {
                $query = "SELECT id, first_name, last_name, unique_identifier, profession, status FROM admin_registration WHERE unique_identifier IN (".implode(",", $propertyUsers).")";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $propertyAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Fetch admins from others_property_registration matching the user's property name
            $othersPropertyAdmins = [];
            $query = "SELECT user_id FROM others_property_registration WHERE property_name = :property_name";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':property_name', $userProperty);
            $stmt->execute();
            $othersPropertyUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Fetch admin details from admin_registration for matching users
            if (!empty($othersPropertyUsers)) {
                $query = "SELECT id, first_name, last_name, unique_identifier, profession,  status FROM admin_registration WHERE unique_identifier IN (".implode(",", $othersPropertyUsers).")";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $othersPropertyAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Merge the two arrays of admins
            $mergedAdmins = array_merge($propertyAdmins, $othersPropertyAdmins);

            // Store these admins in the main array
            $allUserAdmins[$userProperty] = $mergedAdmins;
        }
    } else {
        // Handle the case where there are no user property names
        echo "No Professionals to chat with...";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

function getAverageRating($adminId, $pdo)
{
    $query = "SELECT AVG(rating) AS average_rating FROM feedback_ratings WHERE admin_id = :adminId";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':adminId', $adminId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['average_rating'] ?? 0;
}

// Function to generate star ratings with partial fill
function generateStarRating($averageRating)
{
    $fullStars = floor($averageRating); // Get the number of full stars
    $decimalPart = $averageRating - $fullStars; // Get the decimal part

    $starHtml = '';

    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $starHtml .= '<i class="fas fa-star" style="color: gold;"></i>';
    }

    // Partially filled star
    if ($decimalPart > 0) {
        $starHtml .= '<i class="fas fa-star-half-alt" style="color: gold;"></i>';
    }

    // Empty stars
    $emptyStars = 5 - $fullStars - ($decimalPart > 0 ? 1 : 0);
    for ($i = 0; $i < $emptyStars; $i++) {
        $starHtml .= '<i class="far fa-star" style="color: gold;"></i>';
    }

    return $starHtml;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="chat.css">
    <title>Chat Interface</title>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Chat Interface</h1>
            <a href="user_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </header>
    <main class="split-interface">
        <section class="admin-list">
            <h2 class="admin-list-header">Professionals</h2><br>
            <?php
            // Loop through the user's property names
            foreach ($userPropertyNames as $property) {
                // Check if there are admins for this property
                $propertyAdmins = $allUserAdmins[$property];

                if (!empty($propertyAdmins)) {
                    echo '<section class="property-container">';
                    echo '<h2 class="property admin-list-header" data-building-toggle>';
                    echo '<span class="toggle-arrow">▶</span>'; // Add a toggle arrow
                    echo $property;
                    echo '</h2>';
                    echo '<div class="property-content" style="display: none;">';
                    // Group admins by profession for this property
                    $adminsByProfession = [];
                    foreach ($propertyAdmins as $admin) {
                        $profession = $admin['profession'];
                        if (!isset($adminsByProfession[$profession])) {
                            $adminsByProfession[$profession] = [];
                        }
                        $adminsByProfession[$profession][] = $admin;
                    }

                    // List admins under each profession
                    foreach ($adminsByProfession as $profession => $admins) {
                        echo '<div class="profession">';
                        echo '<h2 class="profession-title" data-profession-toggle>' . $profession . '</h2>';
                        echo '<div class="admins">';

                        foreach ($admins as $admin) {
                            echo '<ul>';
                            echo '<li>';
                            echo '<a href="#" class="admin-link" data-admin-id="' . $admin['id'] . '" data-unique-identifier="' . $admin['unique_identifier'] . '" onclick="openChatWith(\'' . $admin['first_name'] . ' ' . $admin['last_name'] . '\', \'' . $admin['unique_identifier'] . '\')">';
                            echo $admin['first_name'] . ' ' . $admin['last_name'];

                            // Display the star rating
                            $averageRating = getAverageRating($admin['unique_identifier'], $pdo);
                            echo generateStarRating($averageRating);

                            // Determine the dot color based on admin status
                            $dotColor = ($admin['status'] == 'online') ? 'green' : 'grey';
                            echo '<span class="dot" style="background-color: ' . $dotColor . ';"></span>';
                            echo '<div class="badge" id="unreadCount_' . $admin['unique_identifier'] . '" style="color: #ffffff;"></div>';
                            echo '</a>';
                            echo '</li>';
                            echo '</ul>';
                        }

                        echo '</div>';
                        echo '</div>';
                    }

                    echo '</div>';
                    echo '</section>';
                }
            }

            $allAdminsEmpty = empty($allUserAdmins);

            if ($allAdminsEmpty) {
                echo '<p>No Professionals to chat with.....</p>';
            }
            ?>
        </section>
        <section class="chat-box" style="display: none;">
            <div class="chat-window">
                <div class="chat-header">
                    Chat with: <span class="admin-name"></span>
                    <button class="close-button" onclick="closeChat()"><i class="fas fa-times"></i></button>
                </div>
                <div class="chat-messages">
                </div>
            </div>
            <div class="message-input-container">
                <form class="message-form" enctype="multipart/form-data">
                    <div class="input-container">
                        <label for="file" class="file-upload-label"><i class="fas fa-share"></i></label>
                        <input type="file" name="file" id="file" accept=".jpg, .jpeg, .png, .gif, .pdf, .doc, .docx, .txt">
                        <p id="selected-file-name" class="selected-file">No file selected</p>
                        <textarea class="message-input" placeholder="Type your message here..."></textarea>
                        <button class="send-button" type="submit" onclick="sendMessageWithFile(); resetFileInput(); return false;"><i class="fas fa-paper-plane"></i></button>
                    </div>
                    <div class="button-container">
                        <button class="end-chat-button" onclick="endChatWithAdmin()">End Chat</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script>
        // Chat interface logic here...
        const splitInterface = document.querySelector('.split-interface');
        const adminLinks = document.querySelectorAll('.admin-link');
        const adminName = document.querySelector('.admin-name');
        const messagesContainer = document.querySelector('.chat-messages');
        var adminId = null;
        var chatRefreshInterval;

        // JavaScript to handle property and profession section toggling
        document.addEventListener('DOMContentLoaded', function () {
            const propertyHeaders = document.querySelectorAll('[data-building-toggle]');
            
            propertyHeaders.forEach(header => {
                header.addEventListener('click', function () {
                    const propertyContent = this.nextElementSibling;
                    const arrow = this.querySelector('.toggle-arrow');
                    
                    if (propertyContent.style.display === 'block') {
                        propertyContent.style.display = 'none';
                        arrow.style.transform = 'rotate(0deg)'; // Point to the right
                        this.parentElement.classList.remove('expanded');
                    } else {
                        propertyContent.style.display = 'block';
                        arrow.style.transform = 'rotate(90deg)'; // Point down
                        this.parentElement.classList.add('expanded');
                    }
                });
            });
        });

        function resetFileInput() {
            const fileInput = document.getElementById("file");
            const selectedFileName = document.getElementById("selected-file-name");

            // Reset the file input
            fileInput.value = null;
            // Update the selected file name to "No file selected"
            selectedFileName.textContent = "No file selected";
        }

        function truncateFileName(name, length) {
            if (name.length <= length) {
                return name;
            }
            const extensionIndex = name.lastIndexOf('.');
            if (extensionIndex === -1) {
                return name.substring(0, length) + '...';
            }
            const extension = name.substring(extensionIndex);
            return name.substring(0, length - 3) + '...' + extension;
        }

        document.getElementById("file").addEventListener("change", function() {
            const fileInput = document.getElementById("file");
            const selectedFileName = document.getElementById("selected-file-name");

            if (fileInput.files.length > 0) {
                selectedFileName.textContent = truncateFileName(fileInput.files[0].name, 8); // Adjust 8 to your desired length
            } else {
                selectedFileName.textContent = "No file selected";
            }
        });

        function openChatWith(adminFullName, uniqueIdentifier) {
            event.preventDefault(); // Prevent the default behavior of the anchor element

            adminName.textContent = adminFullName;
            messagesContainer.innerHTML = '';
            const chatBox = document.querySelector('.chat-box');
            chatBox.style.display = 'block';

            // Store the unique_identifier in a session variable
            sessionStorage.setItem('selectedAdminUniqueIdentifier', uniqueIdentifier);

            clearInterval(chatRefreshInterval);

            chatRefreshInterval =setInterval(fetchMessages, 100);

            // Remove the badge by clearing its content and hiding it
            const badgeElement = document.getElementById('unreadCount_' + uniqueIdentifier);
            badgeElement.textContent = ''; // Clear the badge content
            badgeElement.style.display = 'none'; // Hide the badge
        }

        function closeChat() {
            const chatBox = document.querySelector('.chat-box');
            chatBox.style.display = 'none';
            adminName.textContent = '';
            messagesContainer.innerHTML = '';

            clearInterval(chatRefreshInterval);
        }

        const professionTitles = document.querySelectorAll('.profession-title');

        professionTitles.forEach(professionTitle => {
            professionTitle.addEventListener('click', function () {
                this.parentElement.classList.toggle('active');
                const professionContent = this.nextElementSibling;
                professionContent.style.display = professionContent.style.display === 'none' ? 'block' : 'none';
            });
        });

        function fetchMessages() {
            console.log('Fetching messages...');

            const uniqueIdentifier = sessionStorage.getItem('selectedAdminUniqueIdentifier');
            var formData = new FormData();
            formData.append("admin_unique_id", uniqueIdentifier);
            formData.append("user_unique_id", <?php echo $userId; ?>);

            fetch("user_fetch_messages.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                messagesContainer.innerHTML = ""; // Clear the existing messages

                if (data.messages.length === 0) {
                    // Display a message when there are no messages
                    var noMessagesElement = document.createElement("div");
                    noMessagesElement.classList.add("no-messages");
                    noMessagesElement.textContent = "No messages to display";
                    messagesContainer.appendChild(noMessagesElement);
                } else {
                    data.messages.forEach(message => {
                        var messageContent = message.message ? message.message.trim() : "";
                        var fileURL = message.file_path ? message.file_path.trim() : "";
                        if (messageContent !== "") {
                            var messageElement = document.createElement("div");
                            messageElement.classList.add("message");

                            // Check if the message is from the current user or admin
                            if (message.sender_name === "You") {
                                messageElement.classList.add("sender-user");
                            } else {
                                messageElement.classList.add("sender-admin");
                            }

                            // Create and append sender information
                            var senderInfoContainer = document.createElement("div");
                            senderInfoContainer.classList.add("sender-info-container");

                            // Create and append profession element
                            var professionElement = document.createElement("div");
                            professionElement.classList.add("sender-profession");
                            professionElement.textContent = message.admin_profession ? message.admin_profession.trim() : "";

                            // Create and append sender name element
                            var senderNameElement = document.createElement("div");
                            senderNameElement.classList.add("sender-name");
                            senderNameElement.textContent = message.sender_name ? message.sender_name.trim() : "Unknown";

                            // Append profession and sender name to the sender info container
                            senderInfoContainer.appendChild(professionElement);
                            senderInfoContainer.appendChild(senderNameElement);

                            // Create a container for message content and status elements
                            var messageContentStatusContainer = document.createElement("div");
                            messageContentStatusContainer.classList.add("message-content-status-container");

                            // Create and append message content element
                            var messageContentElement = document.createElement("div");
                            messageContentElement.classList.add("message-content");
                            messageContentElement.textContent = messageContent;

                            // Create and append message status element
                            var messageStatusElement = document.createElement("div");
                            messageStatusElement.className = "message-status";
                            if (message.status === "sent") {
                                messageStatusElement.classList.add("fas", "fa-check"); // Font Awesome icon for sent
                            } else if (message.status === "delivered") {
                                messageStatusElement.classList.add("fas", "fa-check-double"); // Font Awesome icon for delivered
                            } else if (message.status === "read") {
                                messageStatusElement.classList.add("fas", "fa-check-double", "blue-icon"); // Font Awesome icon for read
                            }

                            // Append message content and status to the container
                            messageContentStatusContainer.appendChild(messageContentElement);
                            messageContentStatusContainer.appendChild(messageStatusElement);

                            // Append sender info container to the message element
                            messageElement.appendChild(senderInfoContainer);

                            // Append the container to the message element
                            messageElement.appendChild(messageContentStatusContainer);

                            // Append the message element to the messages container
                            messagesContainer.appendChild(messageElement);
                        }

                       // Inside the fetchMessagesForUser function
                        if (fileURL) {
                            // Create elements to display files
                            var fileContainer = document.createElement("div");
                            fileContainer.classList.add("file-container");

                            // Check if the message is from the current user or admin
                            if (message.sender_name === "You") {
                                fileContainer.classList.add("sender-user-file"); // Position right for "You"
                            } else {
                                fileContainer.classList.add("sender-admin-file"); // Position left for other senders
                            }

                            // Create a container for file content and status elements
                            var fileContentStatusContainer = document.createElement("div");
                            fileContentStatusContainer.classList.add("file-content-status-container");

                            // Check the file type and create an appropriate HTML element
                            var fileElement = document.createElement("a");
                            fileElement.href = fileURL;

                            // Extract the filename for display (first 5 characters + extension)
                            const filename = getDisplayFilename(fileURL);

                            fileElement.download = filename; // Use the extracted filename for download
                            fileElement.classList.add("downloadable-link");

                            // Create and append file status element
                            var fileStatusElement = document.createElement("div");
                            fileStatusElement.className = "file-status";
                            if (message.status === "sent") {
                                fileStatusElement.classList.add("fas", "fa-check"); // Font Awesome icon for sent
                            } else if (message.status === "delivered") {
                                fileStatusElement.classList.add("fas", "fa-check-double"); // Font Awesome icon for delivered
                            } else if (message.status === "read") {
                                fileStatusElement.classList.add("fas", "fa-check-double", "blue-icon"); // Font Awesome icon for read
                            }

                            // Create an icon element to display appropriate icon
                            var fileIcon = document.createElement("i");

                            if (isImage(fileURL)) {
                                // For images, create an image icon
                                fileIcon.classList.add("fas", "fa-image");
                                fileIcon.title = "Image";

                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link");

                                // Create a download button for images
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download Image"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                // Create an image element to display the image
                                var imageElement = document.createElement("img");
                                imageElement.src = fileURL;
                                imageElement.alt = "Received Image";
                                // Append the image and icon elements to the fileElement
                                fileElement.appendChild(imageElement);
                                fileElement.appendChild(fileIcon);

                            } else if (isPDF(fileURL)) {
                                // For PDFs, create a PDF icon
                                fileIcon.classList.add("fas", "fa-file-pdf");
                                fileIcon.title = "PDF Document";
                                // Append the PDF icon to the fileElement
                                // For PDFs, create a direct link to the PDF with a download attribute
                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.textContent = "Download PDF";
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link")
                                // Create a download button for other file types
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download File"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                fileElement.appendChild(fileIcon);
                            } else {
                                // For other file types, create a generic file icon
                                fileIcon.classList.add("fas", "fa-file");
                                fileIcon.title = "File";

                                // For other file types, create an anchor tag with a download button
                                fileElement = document.createElement("a");
                                fileElement.href = fileURL;
                                fileElement.download = fileURL.split("/").pop(); // Suggest a filename for download
                                fileElement.classList.add("downloadable-link");

                                // Create a download button for other file types
                                var downloadIcon = document.createElement("i");
                                downloadIcon.classList.add("fas", "fa-download");
                                downloadIcon.title = "Download File"; // Optional tooltip
                                if (message.sender_name !== "You") {
                                    fileElement.appendChild(downloadIcon);
                                }
                                // Append the generic file icon to the fileElement
                                fileElement.appendChild(fileIcon);
                            }

                            // Append the extracted filename for display to the container
                            fileContentStatusContainer.appendChild(document.createTextNode(filename));

                            // Append file content, status, and the icon to the container
                            fileContentStatusContainer.appendChild(fileElement);
                            fileContentStatusContainer.appendChild(fileStatusElement);

                            // Append file content and status container to the file container
                            fileContainer.appendChild(fileContentStatusContainer);

                            // Append the file container to the messages container
                            messagesContainer.appendChild(fileContainer);

                            // Add a click event listener to trigger the download
                            fileElement.addEventListener("click", function (event) {
                                event.preventDefault(); // Prevent the link from opening
                                downloadFile(this.href, this.download);
                            });
                        }
                    });
                }
                })
                .catch(error => {
                console.error("Error:", error);
            });
        }

        // Function to extract the display filename (first 5 characters + extension)
        function getDisplayFilename(fileURL) {
            const filename = fileURL.split('/').pop(); // Extract the filename from the URL
            const extensionIndex = filename.lastIndexOf('.'); // Find the last dot (for file extension)

            if (extensionIndex >= 0) {
                const firstFiveChars = filename.substring(0, Math.min(5, extensionIndex)); // Extract first 5 characters
                const extension = filename.substring(extensionIndex); // Extract the extension
                return firstFiveChars + (firstFiveChars.length < 5 ? '' : '...') + extension;
            } else {
                // If there is no file extension, use the whole filename
                return filename;
            }
        }

        function isImage(url) {
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif'];
            const extension = url.substring(url.lastIndexOf('.')).toLowerCase();
            return imageExtensions.includes(extension);
        }

        function isPDF(url) {
            const pdfExtensions = [".pdf"];
            const extension = url.substring(url.lastIndexOf(".")).toLowerCase();
            return pdfExtensions.includes(extension);
        }

        function downloadFile(url, suggestedFilename) {
            var anchor = document.createElement("a");
            anchor.href = url;
            anchor.download = suggestedFilename;
            anchor.style.display = "none";
            document.body.appendChild(anchor);
            anchor.click();
            document.body.removeChild(anchor);
        }


        
        function fetchUnreadMessageCounts() {
            // Create a new FormData object
            const formData = new FormData();
            
            formData.append('userId', <?php echo $userId; ?>);

            // Send a POST request to your PHP script that fetches unread message counts
            fetch("fetch_unread_message_counts.php", {
                method: "POST",
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                console.log("Fetch data:", data)
                // Update the badges based on the received data
                for (const senderId in data) {
                    console.log("val", senderId)
                    const badgeElement = document.getElementById(`unreadCount_${senderId}`);
                    if (badgeElement) {
                        badgeElement.innerHTML = data[senderId];

                        badgeElement.classList.add('circle-badge');
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching unread message counts:', error);
            });
        }

        // Call fetchUnreadMessageCounts every 5 seconds (adjust the interval as needed)
        setInterval(fetchUnreadMessageCounts, 300); // 5000 milliseconds = 5 seconds


        function endChatWithAdmin() {
            const uniqueIdentifier = sessionStorage.getItem('selectedAdminUniqueIdentifier');
            
            if (uniqueIdentifier) {
                // Send a request to the server to mark the chat as ended
                const formData = new FormData();
                formData.append("admin_unique_id", uniqueIdentifier);
                formData.append("user_unique_id", <?php echo $userId; ?>);

                fetch("end_chat.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json(); // Change to response.text() to get the raw response text
                })
                .then(data => {
                    if (data.success) {
                        // Chat has been successfully ended
                        closeChat(); // Close the chat box
                        // You can also update the interface to indicate that the chat has ended
                        window.location.href = `feedback_form.php?admin_id=${uniqueIdentifier}`;
                    } else {
                        // Error ending the chat
                        console.error("Failed to end chat:", data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                });
            }
        }
        function updateMessageStatus() {
            // Make an AJAX request to update the message status
            const formData = new FormData();
            formData.append("user_unique_id", <?php echo $userId; ?>);

            fetch("update_user_message_status.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                // Handle the response as needed
            })
            .catch(error => {
                console.error("Error updating message status:", error);
            });
        }
        setInterval(updateMessageStatus, 100);

        function sendMessageWithFile() {
            const fileInput = document.getElementById('file');
            const messageInput = document.querySelector('.message-input');
            const message = messageInput.value.trim();
            const file = fileInput.files[0];
            messageInput.value = '';
            fileInput.value = "";

            if (message === '' && !file) {
                return; // Don't send empty messages
            }

            // Retrieve the stored unique_identifier from the session
            const uniqueIdentifier = sessionStorage.getItem('selectedAdminUniqueIdentifier');

            if (uniqueIdentifier) {
                const formData = new FormData();
                formData.append("admin_unique_id", uniqueIdentifier);
                formData.append("user_unique_id", <?php echo $userId; ?>); // Include the user ID
                formData.append("message", message);

                // Add the file to the form data
                if (file) {
                    formData.append("file", file);
                }

                fetch("user_send_message.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json(); // Change to response.text() to get the raw response text
                })
                .then(data => {
                    console.log(data);
                    // Handle the response from the server
                    if (data.success) {
                        // Message sent successfully
                        console.log("Message sent");
                    } else {
                        // Error sending message
                        console.error("Failed to send message:", data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                })
                .finally(() => {
                    // Call fetchMessages to update the chat box after sending a message
                    fetchMessages();
                });
            }
        }

        // Array to track building names with new messages
        const buildingsWithNewMessages = [];

        function markBuildingAsNew(property) {
            if (!buildingsWithNewMessages.includes(property)) {
                buildingsWithNewMessages.push(property);
            }
        }

        function removeBuildingAsNew(property) {
            const index = buildingsWithNewMessages.indexOf(property);
            if (index !== -1) {
                buildingsWithNewMessages.splice(index, 1);
            }
        }
    </script>
</body>
</html>
