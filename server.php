<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "WebSocket server started\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Received message: $msg\n";

        // Decode the message into an associative array
        $data = json_decode($msg, true);
        
        // Ensure that we have the required fields
        if (isset($data['outgoing_id'], $data['incoming_id'], $data['msg'])) {
            // Store message in the database
            $this->storeMessage($data['outgoing_id'], $data['incoming_id'], $data['msg']);
            
            // Broadcast the message to all connected clients
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode($data)); // Send back the original data for consistency
                }
            }
        } else {
            echo "Invalid message format: " . print_r($data, true) . "\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Remove the connection
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function storeMessage($outgoing_id, $incoming_id, $msg) {
        // Database connection
        $conn = new mysqli("localhost", "root", "", "chat");
    
        // Check connection
        if ($conn->connect_error) {
            echo "Connection failed: " . $conn->connect_error . "\n";
            return; // Exit early on connection failure
        }
    
        // Define the status as 'unseen'
        $status = 'unseen';
        $time = date("Y-m-d H:i:s"); // Get the current time
    
        // Prepare and execute the query to store the message
        $stmt = $conn->prepare("INSERT INTO messages (outgoing_msg_id, incoming_msg_id, msg,  status) VALUES (?,  ?, ?, ?)");
        
        if (!$stmt) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error . "\n";
            return; // Exit early on prepare failure
        }
    
        $stmt->bind_param("iiss", $outgoing_id, $incoming_id, $msg, $status);
    
        if ($stmt->execute()) {
            echo "Message stored successfully.\n";
        } else {
            echo "Error storing message: (" . $stmt->errno . ") " . $stmt->error . "\n";
        }
    
        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
    
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer()
        )
    ),
    8080
);

$server->run();
