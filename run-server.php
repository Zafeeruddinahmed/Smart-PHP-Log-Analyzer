<?php
require __DIR__ . '/vendor/autoload.php';
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server as Reactor;

class LogPusher implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $lastReadPosition = 0;
    protected $logFile;
    public function __construct($logFile) {
        $this->clients = new \SplObjectStorage;
        $this->logFile = $logFile;
    }
    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }
    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {}
    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }
    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
    public function checkLogUpdates() {
        if (!file_exists($this->logFile)) return;
        $file = fopen($this->logFile, 'r');
        fseek($file, $this->lastReadPosition);
        while (($line = fgets($file)) !== false) {
            foreach ($this->clients as $client) {
                $client->send($line);
            }
        }
        $this->lastReadPosition = ftell($file);
        fclose($file);
    }
}

$logFile = __DIR__ . '/logs/app.log';
$loop = Factory::create();
$pusher = new LogPusher($logFile);
$webSock = new Reactor('0.0.0.0:8080', $loop);
$server = new IoServer(new HttpServer(new WsServer($pusher)), $webSock, $loop);
$loop->addPeriodicTimer(1, function() use ($pusher) { $pusher->checkLogUpdates(); });
echo "WebSocket server started on ws://localhost:8080\n";
$loop->run();
