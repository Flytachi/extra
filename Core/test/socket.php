<?php

require dirname(__DIR__, 2).'/static/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;


class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            $client->send($msg);

            $mas = json_decode($msg);

            var_dump($mas);

            $type = $mas->{'type'};

            if($type == "messages"){
                $id_push = $mas->{"id"};
                $id_pull = $mas->{"id_cli"};
                $message = $mas->{"message"};
                $type = $mas->{'type'};
                $type_message = $mas->{'type_message'};
            }else{
                $type = $mas->{'type'};
            }
        }

        global $db;

        if($type == "messages"){
        $hour = date('H');

        $minute = date('i');

        $year = date('Y');

        $month = date('m');

        $day = date('d');

        $date = $year .".". $month .".". $day;

        $time = $hour .":". $minute;

        $sql = "INSERT INTO `chat` (`id`, `type_message`, `id_push`, `id_pull`, `message`, `date`, `time`) VALUES (NULL, '$type_message', '$id_push', '$id_pull', '$message', '$date', '$time')";

        echo $sql;

        $db->query($sql);
        }else{
        echo $type;
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

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    $ini['SOCKET']['PORT']
);

$server->run();
?>