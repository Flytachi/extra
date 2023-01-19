<?php

namespace Extra\Src;

use Warframe;

class WebSocketServer 
{
    /**
     * 
     * WebSocketServer
     * 
     * @version 2.2
     */

    protected string $IP;
    protected int $PORT;
    protected $timeWorkLimit = 0;
    protected $verbose = false;
    protected $logging = false;

    protected $connection;
    protected $connects;
    protected $startTime;
    protected $resource;


    protected function handler($connect, $data)
    {
        $this->serverLog('Client send: ' . $data);
    }

    protected function handlerConnect($connect)
    {
        $this->serverLog('New connection accepted');
    }

    protected function handlerDisconnect($connect)
    {
        $this->serverLog('Connection closing');
    }

    public final function __construct() 
    {
        set_time_limit(0);
        ob_implicit_flush();

        spl_autoload_register(function ($class) {
            $class = explode("\\", $class);
            if (ROUTE_PLUGIN_SYSTEM && count($class) > 1) {
                $file = PATH_PLUGIN . "/Frame." . $class[0] . "/repository/" . $class[1] . '.php';
            } else {
                $file = PATH_APP . '/repository/' . $class[0] . '.php';
            }
            if (file_exists($file)) require $file;
        });
    }

    public final function start(): void
    {
        if ($this->logging) {
            if (!is_dir(PATH_LOG)) mkdir(PATH_LOG);
            $this->resource = fopen(PATH_LOG . '/webSocket-' . get_class($this) . '.txt', 'a');
        }
        $this->startServer();
    }

    public final function __destruct() 
    {
        if (is_resource($this->connection)) $this->stopServer();
        if ($this->logging && $this->resource) fclose($this->resource);
    }

    public final function startServer(): void
    {
        $this->serverLog('Starting the Web Server...');
        $this->serverLog('Stream: tcp://' . $this->IP . ':' . $this->PORT);
        $this->connection = stream_socket_server('tcp://' . $this->IP . ':' . $this->PORT, $errno, $errstr);
        
        if (!$this->connection) {
            $this->serverLog('Cannot start server: ' .$errstr. '(' .$errno. ')');
            die;
        }

        $this->serverLog('Server is running...');
        $this->connects = [];
        $this->startTime = time();

        while (true) {
            // $this->serverLog('Wait...');
            $read = $this->connects;
            $read[] = $this->connection;
            $write = $except = null;
            if ( !stream_select($read, $write, $except, null) ) break;

            if (in_array($this->connection, $read)) {
                if (($connect = stream_socket_accept($this->connection, -1)) && $this->handshake($connect)) {
                    $this->connects[] = $connect;
                    try {
                        $this->handlerConnect($connect);
                    } catch (\Throwable $th) {
                        $this->serverLog("WebSocketServer error 'handlerConnect':\n" . $th);
                    }
                }
                unset($read[array_search($this->connection, $read)]);
            }

            foreach ($read as $connect) { 
                $data = fread($connect, 100000);
                $decoded = self::decode($data);
                
                if (false === $decoded || 'close' === $decoded['type']) {
                    try {
                        $this->handlerDisconnect($connect);
                    } catch (\Throwable $th) {
                        $this->serverLog("WebSocketServer error 'handlerDisconnect':\n" . $th);
                    }
                    fwrite($connect, self::encode('  Closed on client demand', 'close'));
                    fclose($connect);
                    unset($this->connects[ array_search($connect, $this->connects) ]);
                    continue;
                }

                try {
                    $this->handler($connect, $decoded['payload']);
                } catch (\Throwable $th) {
                    $this->serverLog("WebSocketServer error 'handler':\n" . $th);
                }
            }

            if ($this->timeWorkLimit && time() - $this->startTime > $this->timeWorkLimit) {
                $this->serverLog('Time limit. Stopping server.');
                $this->stopServer();
                die;
            }
        }
    }

    public final function stopServer(): void
    {
        fclose($this->connection);
        if (!empty($this->connects)) {
            foreach ($this->connects as $connect) {
                if (is_resource($connect)) {
                    fwrite($connect, self::encode('  Closed on server demand', 'close'));
                    fclose($connect);
                }
            }
        }
    }

    public final function serverLog($message): void
    {
        $message = '[' . date('r') . '] ' . $message . PHP_EOL;
        if ($this->verbose) echo $message;
        if ($this->logging) fwrite($this->resource, $message);
    }

    public function send($connect, $data): void 
    {
        fwrite($connect, self::encode($data));
    }

    /**
     * Для кодирования сообщений перед отправкой клиенту
     */
    private static function encode($payload, $type = 'text', $masked = false) {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;
            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;
            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;
            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) $frameHead[$i] = chr($frameHead[$i]);
        if ($masked === true) {
            // generate a random mask:
            $mask = [];
            for ($i = 0; $i < 4; $i++) $mask[$i] = chr(rand(0, 255));
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    /**
     * Для декодирования сообщений, полученных от клиента
     */
    private static function decode($data) {
        if (!strlen($data)) return false;

        $unmaskedPayload = '';
        $decodedData = [];

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;

        // unmasked frame is received:
        if (!$isMasked) return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
        
        switch ($opcode) {
            // text frame:
            case 1:
                $decodedData['type'] = 'text';
                break;
            case 2:
                $decodedData['type'] = 'binary';
                break;
            // connection close frame:
            case 8:
                $decodedData['type'] = 'close';
                break;
            // ping frame:
            case 9:
                $decodedData['type'] = 'ping';
                break;
            // pong frame:
            case 10:
                $decodedData['type'] = 'pong';
                break;
            default:
                return ['type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)'];
        }

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) $tmp .= sprintf('%08b', ord($data[$i + 2]));
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        /**
         * We have to check for large frames here. socket_recv cuts at 1024 bytes
         * so if websocket-frame is > 1024 bytes we have to wait until whole
         * data is transferd.
         */
        if (strlen($data) < $dataLength) return false;

        if ($isMasked) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
            }
            $decodedData['payload'] = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }

        return $decodedData;
    }

    /**
     * «Рукопожатие», т.е. отправка заголовков согласно протоколу WebSocket
     */
    private function handshake($connect) {
        $info = array();

        $line = fgets($connect);
        $header = explode(' ', $line);
        $info['method'] = $header[0] ?? null;
        $info['uri'] = $header[1] ?? null;

        // считываем заголовки из соединения
        while ($line = rtrim(fgets($connect))) {
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $info[$matches[1]] = $matches[2];
            } else break;
        }

        // получаем адрес клиента
        $address = explode(':', stream_socket_get_name($connect, true));
        $info['ip'] = $address[0];
        $info['port'] = $address[1];

        if (empty($info['Sec-WebSocket-Key'])) return false;

        // отправляем заголовок согласно протоколу вебсокета
        $SecWebSocketAccept = 
            base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept:".$SecWebSocketAccept."\r\n\r\n";
        fwrite($connect, $upgrade);

        return $info;
    }

    /**
     * Отправляет сигнал всем в сети
     */
    public function sendAll($data): void
    {
        foreach ($this->connects as $conn) {
            $this->send($conn, $data);
        }
    }

    /**
     * @return string
     */
    public final function getIp(): string
    {
        return $this->IP;
    }

    /**
     * @return int
     */
    public final function getPort(): int
    {
        return $this->PORT;
    }

    public final function statusConnection(): bool 
    {
        $fp = @fsockopen($this->IP, $this->PORT, $errCode, $errStr, 1);
        if ($fp) {
            fclose($fp);
            return true;
        } else return false;
    }

}
