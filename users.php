<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "chat");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$logged_in_user_id = $_SESSION['unique_id']; // Assuming the logged-in user ID is stored in the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white h-screen">
    <div class="max-w-full h-full flex flex-col mx-auto overflow-hidden">

        <!-- Header -->
        <header class="flex justify-between items-center p-4 bg-gray-800">
            <h1 class="text-2xl font-semibold">Chats</h1>
            <button class="p-2 rounded-full bg-gray-700 hover:bg-gray-600">
                <i class="fas fa-edit"></i>
            </button>
        </header>

        <!-- Search Bar -->
        <div class="p-4 users">
            <div class="search flex items-center">
                <input type="text" id="searchBar" placeholder="Search..." class="w-full p-2 bg-gray-700 text-white rounded focus:outline-none">
                <button id="searchBtn" class="ml-2 p-2 bg-gray-700 text-white rounded">Search</button>
            </div>
        </div>

        <?php
$logged_in_user_id = $_SESSION['unique_id']; // Assuming session contains the logged-in user's ID

$sql = "
    SELECT u.unique_id, u.fname, u.lname, u.img, 
           (SELECT m.msg 
            FROM messages m 
            WHERE (m.outgoing_msg_id = u.unique_id AND m.incoming_msg_id = $logged_in_user_id) 
               OR (m.outgoing_msg_id = $logged_in_user_id AND m.incoming_msg_id = u.unique_id) 
            ORDER BY m.time DESC LIMIT 1) AS latest_message,
           (SELECT m.status 
            FROM messages m 
            WHERE (m.outgoing_msg_id = u.unique_id AND m.incoming_msg_id = $logged_in_user_id) 
               OR (m.outgoing_msg_id = $logged_in_user_id AND m.incoming_msg_id = u.unique_id) 
            ORDER BY m.time DESC LIMIT 1) AS latest_status,
           (SELECT DATE_FORMAT(m.time, '%l:%i %p') 
            FROM messages m 
            WHERE (m.outgoing_msg_id = u.unique_id AND m.incoming_msg_id = $logged_in_user_id) 
               OR (m.outgoing_msg_id = $logged_in_user_id AND m.incoming_msg_id = u.unique_id) 
            ORDER BY m.time DESC LIMIT 1) AS latest_time
    FROM users u
    WHERE u.unique_id != $logged_in_user_id
    ORDER BY u.fname ASC;
";

$result = mysqli_query($conn, $sql);
?>

<!-- HTML to Display the Users List -->
<div class="flex-1 overflow-y-auto">
    <ul id="usersList" class="users-list">
        <?php
        $output = "";

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $latestMessage = $row['latest_message'] ? htmlspecialchars($row['latest_message']) : 'No messages yet';
                $latestStatus = $row['latest_status'] ? $row['latest_status'] : 'seen';
                $messageClass = ($latestStatus === 'unseen') ? 'font-bold' : '';

                $output .= '<li class="flex items-center justify-between px-4 py-3 hover:bg-gray-800 cursor-pointer" onclick="openChat(' . htmlspecialchars($row['unique_id']) . ')">
                    <div class="flex items-center space-x-3">
                        <img src="./php/images/' . htmlspecialchars($row['img']) . '" alt="User Image" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <span class="text-lg font-semibold">' . htmlspecialchars($row['fname'] . " " . $row['lname']) . '</span>
                            <p class="text-sm text-gray-400 ' . $messageClass . '">' . $latestMessage . '</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-400">' . $row['latest_time'] . '</div>
                </li>';
            }
        } else {
            $output .= '<p class="text-center text-gray-500 py-5">No users are available to chat</p>';
        }

        echo $output;
        ?>
    </ul>
</div>



        <!-- Bottom Navigation -->
        <div class="flex items-center justify-around bg-gray-800 py-3">
            <button class="flex flex-col items-center text-sm text-blue-500">
                <i class="fas fa-comments text-xl"></i>
                <span>Chats</span>
            </button>
            <button class="flex flex-col items-center text-sm text-gray-400">
                <i class="fas fa-users text-xl"></i>
                <span>People</span>
            </button>
            <button class="flex flex-col items-center text-sm text-gray-400">
                <i class="fas fa-book-open text-xl"></i>
                <span>Stories</span>
            </button>
        </div>
    </div>

    <script>
        // Example function for opening chat (needs implementation)
        function openChat(userId) {
            window.location = 'chat.php?user_id=' + userId;
        }

        // Search functionality while typing
        document.getElementById('searchBar').addEventListener('input', function () {
            let searchValue = this.value.toLowerCase();
            let users = document.querySelectorAll('#usersList li');

            users.forEach(user => {
                const name = user.querySelector('span').textContent.toLowerCase();
                if (name.includes(searchValue)) {
                    user.style.display = 'flex';
                } else {
                    user.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
