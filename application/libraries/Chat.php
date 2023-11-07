<?php
// require FCPATH . 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        try {

            $queryString = $conn->httpRequest->getUri()->getQuery();
            parse_str($queryString, $queryParams);
    
            // Access the sender_id and receiver_id
            $senderId = isset($queryParams['sender_id']) ? $queryParams['sender_id'] : null;
            $receiverId = isset($queryParams['receiver_id']) ? $queryParams['receiver_id'] : null;
    
            // Debugging information
            echo "Sender ID: {$senderId}\n";
            echo "Receiver ID: {$receiverId}\n";
    
            // Store the new connection to send messages to later
            $this->clients->attach($conn);
    
            echo "Connection established for ID: {$conn->resourceId}\n";
        } catch (Exception $e) {
            echo "Error in onOpen: " . $e->getMessage() . "\n";
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
    
        // Extract sender's ID from the connection's query parameters
        $queryString = $from->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        $senderId = isset($queryParams['sender_id']) ? $queryParams['sender_id'] : null;
    
        // Iterate through connected clients
        foreach ($this->clients as $client) {
            // Check if the client's query parameters include a receiver_id
            $clientQueryString = $client->httpRequest->getUri()->getQuery();
            parse_str($clientQueryString, $clientParams);
            $receiverId = isset($clientParams['receiver_id']) ? $clientParams['receiver_id'] : null;
    
            // Send the message only to the intended receiver
            if ($senderId !== null && $receiverId !== null && $senderId == $receiverId) {
                $client->send($msg);
            }
        }
    }
    

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}