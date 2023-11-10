<?php

use Ratchet\Client\Connector;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Factory;

class YourApi_Controller extends CI_Controller {

    public function sage_feedback_post() {
        $sender_id = $_POST['sender_id'];
        $receiver_id = $_POST['receiver_id'];
        $message = $_POST['message'];
        $shared_id = $_POST['shared_id'];
    
        if (!empty($message)) {
            $data = array(
                'receiver_id' => $receiver_id,
                'sender_id' => $sender_id,
                'message' => $message,
                'shared_id' => $shared_id
            );
    
            try {
                $this->common_model->insert_array('sage_feedback', $data);
    
                $insertedId = $this->db->insert_id();
                $created_at = date('Y-m-d H:i:s');
    
                $data['id'] = $insertedId;
                $data['created_at'] = $created_at;
    
                // Send the message to the WebSocket server
                $this->sendWebSocketMessage($sender_id, $receiver_id, $message);
    
                $response = [
                    'status' => 200,
                    'message' => 'Feedback submitted successfully',
                    'data' => $data
                ];
                $this->set_response($response, REST_Controller::HTTP_OK);
            } catch (Exception $e) {
                $response = [
                    'status' => 500,
                    'message' => 'Internal Server Error: ' . $e->getMessage()
                ];
                $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response = [
                'status' => 400,
                'message' => 'Feedback not submitted. Message is empty.'
            ];
            $this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    private function sendWebSocketMessage($senderId, $receiverId, $message) {
        // Parameters for the WebSocket URL
        $webSocketUrl = "ws://localhost:8080?sender_id=$senderId&receiver_id=$receiverId";

        $loop = Factory::create();
    
        $connector = new Connector($loop);
        $connector($webSocketUrl)->then(function($connection) use ($message) {
            // WebSocket connection is established
            echo "WebSocket connection is established\n";
    
            // Send the message to the server
            $connection->send($message);
        }, function($e) {
            // An error occurred during the WebSocket connection
            echo "WebSocket connection error: " . $e->getMessage() . "\n";
        });
    
        $loop->run();
    }
}
