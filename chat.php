<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "chat");

if (!$conn) {
    echo "Database not connected Successfully: " . mysqli_connect_error();
    exit;
}

$messages = []; // Initialize $messages array

$user_id = mysqli_real_escape_string($conn, $_GET['user_id'] ?? '');

// Fetch incoming user details
$sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$user_id}");
$row = mysqli_fetch_assoc($sql) ?: [];

// Fetch outgoing user details (logged in user)
$outgoing_id = $_SESSION['unique_id'];
$sql1 = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$outgoing_id}");
$row1 = mysqli_fetch_assoc($sql1) ?: [];

// Fetch previous messages between two users
$sql_messages = "SELECT * FROM messages WHERE 
                (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$user_id}) 
                OR 
                (outgoing_msg_id = {$user_id} AND incoming_msg_id = {$outgoing_id}) 
                ORDER BY msg_id ASC";
$query_messages = mysqli_query($conn, $sql_messages);

// Populate $messages array only if there are results
if (mysqli_num_rows($query_messages) > 0) {
    while ($message = mysqli_fetch_assoc($query_messages)) {
        $messages[] = $message;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .chat { display: flex; margin-bottom: 10px; }
        .outgoing { justify-content: flex-end; }
        .incoming { justify-content: flex-start; }
        .chat img { width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; }
        .chat.outgoing img { order: 2; margin-left: 10px; margin-right: 0; }
        .chat-area { flex-grow: 1; overflow-y: auto; }
        .scrollable { scrollbar-width: none; -ms-overflow-style: none; }
        .scrollable::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col">
    <header class="flex items-center justify-between p-4 border-b border-gray-700 bg-gray-800">
        <a href="users.php" class="text-gray-400 hover:text-gray-300">
            <i class="fas fa-arrow-left"></i>
        </a>
        <img class="w-10 h-10 rounded-full border-2 border-blue-500" src="./php/images/<?php echo !empty($row['img']) ? htmlspecialchars($row['img']) : 'default.png'; ?>" alt="">
        <div class="details flex-grow flex flex-col justify-center ml-4">
            <span class="text-lg font-semibold"><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname'] ?? ''); ?></span>
            <p class="text-gray-400"><?php echo htmlspecialchars($row['status'] ?? ''); ?></p>
        </div>
    </header>

    <main class="flex-grow flex flex-col overflow-hidden">
        <div class="chat-box flex-grow p-4 overflow-y-auto scrollable" id="messageDiv">
            <?php 
                if (!empty($messages)) {
                    foreach ($messages as $msg) {
                        if ($msg['outgoing_msg_id'] == $outgoing_id) {
                            echo '<div class="chat outgoing">';
                            echo '<div class="bg-blue-500 text-white p-2 rounded-lg">' . htmlspecialchars($msg['msg']) . '</div>';
                            echo '<img src="./php/images/' . htmlspecialchars($row1['img']) . '" alt="Outgoing User Image" class="rounded-full">';
                            echo '</div>';
                        } else {
                            echo '<div class="chat incoming">';
                            echo '<img src="./php/images/' . htmlspecialchars($row['img']) . '" alt="Incoming User Image" class="rounded-full">';
                            echo '<div class="bg-gray-700 text-white p-2 rounded-lg">' . htmlspecialchars($msg['msg']) . '</div>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<p class="text-gray-400 text-center">No messages found</p>';
                }
            ?>
        </div>
    </main>

    <footer class="flex p-4 border-t border-gray-700 bg-gray-800">
        <form class="typing-area flex w-full" id="messageForm">
            <input type="text" name="outgoing_id" value="<?php echo $_SESSION['unique_id']; ?>" hidden>
            <input type="text" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
            <input type="text" class="input-field flex-grow p-2 bg-gray-700 text-white rounded-l-lg focus:outline-none" placeholder="Type a message here..." id="messageInput">
            <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-r-lg" id="sendBtn">
                <i class="fab fa-telegram-plane"></i>
            </button>
        </form>
    </footer>

    <script>
        const ws = new WebSocket('ws://localhost:8080/');
        const chatBox = document.querySelector('.chat-box');
        const inputField = document.querySelector('.input-field');
        const sendBtn = document.getElementById('sendBtn');

        // Hidden form inputs
        const outgoingInput = document.querySelector('input[name="outgoing_id"]');
        const incomingInput = document.querySelector('input[name="incoming_id"]');

        ws.addEventListener('open', () => {
            console.log('Connected to WebSocket server');
        });

        ws.addEventListener('message', (event) => {
            console.log("Received message: ", event.data);
            const msgData = JSON.parse(event.data);
            displayMessage(msgData.msg, msgData.outgoing_id, msgData.incoming_id);
        });

        // Send message when clicking the button
        sendBtn.addEventListener('click', sendMessage);

        // Send message when pressing 'Enter'
        inputField.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent default "Enter" behavior
                sendMessage();
            }
        });

        function sendMessage() {
            const message = inputField.value;
            if (message) {
                const outgoingId = outgoingInput.value;
                const incomingId = incomingInput.value;

                const messageData = { msg: message, outgoing_id: outgoingId, incoming_id: incomingId };

                // Display the message immediately in the sender's chat box
                displayMessage(message, outgoingId, incomingId);

                // Send the message to the WebSocket server
                ws.send(JSON.stringify(messageData));

                // Clear the input field after sending
                inputField.value = '';
            } else {
                console.warn("Message cannot be empty.");
            }
        }

        // Function to display message
        function displayMessage(msg, outgoingId, incomingId) {
            const isOutgoing = outgoingId == outgoingInput.value;

            const messageDiv = document.createElement('div');
            messageDiv.className = `chat ${isOutgoing ? 'outgoing' : 'incoming'}`;

            if (isOutgoing) {
                messageDiv.innerHTML = `
                    <div class="bg-blue-500 text-white p-2 rounded-lg max-w-xs">${msg}</div>
                    <img src="./php/images/<?php echo htmlspecialchars($row1['img']); ?>" alt="Outgoing User Image" class="rounded-full">
                `;
            } else {
                messageDiv.innerHTML = `
                    <img src="./php/images/<?php echo htmlspecialchars($row['img']); ?>" alt="Incoming User Image" class="rounded-full">
                    <div class="bg-gray-700 text-white p-2 rounded-lg max-w-xs">${msg}</div>
                `;
            }

            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to the latest message
        }

        // Scroll to the bottom of the chat when entering the chat box
        window.onload = () => {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>
</body>
</html>
