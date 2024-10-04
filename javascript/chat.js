const chatBox = document.querySelector(".chat-box");
const inputField = document.querySelector(".input-field");
const sendBtn = document.querySelector("button");

let ws = new WebSocket('ws://localhost:8080');

// Event when WebSocket connection opens
ws.addEventListener('open', () => {
    console.log("Connected to WebSocket server");
});

// Handle incoming WebSocket messages
ws.addEventListener('message', (event) => {
    const msgData = JSON.parse(event.data);

    const messageDiv = document.createElement('div');
    messageDiv.classList.add('chat', msgData.outgoing ? 'outgoing' : 'incoming', 'flex', 'items-center');

    if (!msgData.outgoing) {
        const imgElement = document.createElement('img');
        imgElement.src = `./php/images/${msgData.img}`;
        imgElement.classList.add('rounded-full', 'w-8', 'h-8', 'mr-2');
        messageDiv.appendChild(imgElement);
    }

    const detailsDiv = document.createElement('div');
    detailsDiv.classList.add('details', 'bg-gray-700', 'text-white', 'p-2', 'rounded-lg', 'max-w-xs');
    detailsDiv.textContent = msgData.msg;
    messageDiv.appendChild(detailsDiv);

    chatBox.appendChild(messageDiv);

    if (isAtBottom()) {
        scrollChatBoxToBottom();
    }
});

sendBtn.addEventListener('click', () => {
    const message = inputField.value.trim();
    if (message) {
        const msgData = {
            msg: message,
            outgoing: true,
            img: '' // Add user image path if available
        };

        ws.send(JSON.stringify(msgData));
        inputField.value = "";
    }
});

// Auto-scroll to bottom
function scrollChatBoxToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function isAtBottom() {
    return chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 10;
}

sendBtn.addEventListener('click', () => {
  const message = inputField.value.trim();
  if (message) {
      const msgData = {
          msg: message,
          outgoing: true,
          img: '' // Add user image path if available
      };

      // Send message via WebSocket
      ws.send(JSON.stringify(msgData));

      // Send message to backend for database storage
      const formData = new FormData();
      formData.append('outgoing_id', "<?php echo $_SESSION['unique_id']; ?>");
      formData.append('incoming_id', "<?php echo $user_id; ?>");
      formData.append('message', message);

      fetch('./saveMessage.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.text())
      .then(data => {
          console.log('Message saved: ', data);
      })
      .catch(error => {
          console.error('Error:', error);
      });

      inputField.value = "";  // Clear input field
  }
});
// Automatically scroll to the bottom of the chat window
function scrollToBottom() {
  const chatBox = document.querySelector(".display-chat");
  chatBox.scrollTop = chatBox.scrollHeight;
}

// Call this function after messages are loaded
scrollToBottom();
