<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require FCPATH . 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class Chatserver extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->library('chat'); // Load the 'chat' library
    }

    public function index()
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this->chat // Access the library as 'Chat' (without parentheses)
                )
            ),
            8080
        );

        $server->run();
    }
}

