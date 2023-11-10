<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require FCPATH . 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server as Reactor;

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
                    new Chat()
                )
            ),
            22
        );
    
        $server->run();
    }

    // public function index()
    // {
    //     // Create a React event loop
    //     $loop = Factory::create();

    //     // Create a WebSocket server using WssServer
    //     $webSocketServer = new WsServer($this->chat);

    //     // Wrap the WebSocket server with HttpServer for compatibility
    //     $httpServer = new HttpServer($webSocketServer);

    //     // Create a React socket server using your live server's domain
    //     $server = new Reactor('wss://staging.burgeon.app:443', $loop);

    //     // Bind the server to the event loop
    //     $server->listen(443, '0.0.0.0');

    //     // Create an IoServer using the WebSocket server and React server
    //     $ioServer = new IoServer($httpServer, $server, $loop);

    //     $ioServer->run();
    // }
}
