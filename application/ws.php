<?php
// Set error reporting
error_reporting(version_compare(PHP_VERSION, '5.4.0', 'ge') ? E_ALL ^ E_NOTICE ^ E_STRICT : E_ALL ^ E_NOTICE);

// Define project document root
define('DOC', rtrim(__DIR__, '\\/') . '/../../../..');

// Define paths to pid and run files
define('PID', DOC . '/application/ws.pid');
define('RUN', DOC . '/application/ws.run');

// Change dir
chdir(__DIR__);

// Include func.php
include '../library/func.php';

// Log that execution reached ws.php
wslog('mypid: ' . getmypid() . ', ' . 'Reached ws.php');

// Require autoload.php
require_once DOC . '/vendor/autoload.php';

// Declare PhpAmqLib usage
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Error logging
 *
 * @param null $msg
 * @param bool $exit
 * @return mixed
 */
function err($msg = null, $exit = false) {

    // Skip E_NOTICE
    if ($msg === 8) return true;

    // Log errors
    if (func_num_args() >= 4) err(func_get_arg(1) . '[' . func_get_arg(0) . '] at ' . func_get_arg(2) . ' on line ' . func_get_arg(3));
    else file_put_contents(DOC . '/application/ws.err', date('Y-m-d H:i:s => ') . 'mypid: ' . getmypid() . ', ' . print_r($msg, true) . "\n", FILE_APPEND);

    // Exit
    if ($exit === true) exit;
}

/**
 * Log $data into ws.<pid>.data file
 *
 * @param $data
 */
function logd($data) {

    // If $data is not scalar - do export
    if (!is_scalar($data)) $data = var_export($data, true);

    // Do log, with millisecond-precise timestamp
    file_put_contents(
        DOC . '/application/ws.' . getmypid(). '.data',
        date('Y-m-d H:i:s') . substr(explode(' ', microtime())[0], 1, 4) . ' => ' . $data . "\n",
        FILE_APPEND
    );
}

/**
 * Shutdown function, for use as a shutdown handler
 */
function shutdown() {

    // Erase pid-file and release the lock
    if ($GLOBALS['pid']) {
        ftruncate($GLOBALS['pid'], 0);
        flock($GLOBALS['pid'], LOCK_UN);
    }

    // Close server stream
    if ($GLOBALS['server']) fclose($GLOBALS['server']);

    // Log shutdown
    err('ws.pid: ' . ($GLOBALS['PID'] ?: 'truncated') . '. mypid => shutdown', false);
}

// Register shutdown handler functions
register_shutdown_function('shutdown');

// Set error handler
set_error_handler('err');

// Do some checks for ini-file
if (!is_file($ini = DOC . '/application/config.ini')) err('No ini-file found', true);
if (!$ini = parse_ini_file($ini, true)) err('Ini-file found but parsing failed', true);
if (!array_key_exists('ws', $ini)) err('No [ws] section found in ini-file', true);

// If ini's 'rabbit' section exists
if ($ini['rabbitmq']['enabled']) {

    // Prepare rabbit
    $rabbit = new AMQPStreamConnection($ini['rabbitmq']['host'], $ini['rabbitmq']['port'], $ini['rabbitmq']['user'], $ini['rabbitmq']['pass']);
    $rabbit = $rabbit->channel();

} else $rabbit = false;

// Do further checks
if (!$ini = $ini['ws']) err('[ws] section found, but it is empty', true);
if (!array_key_exists('port', $ini)) err('No socket port specified in ini-file', true);
if (!$port = (int) $ini['port']) err('Invalid socket port specified in ini-file', true);

// If last execution of ws.php was initiated less than 5 seconds ago - prevent duplicate
if (file_exists(RUN) && strtotime(date('Y-m-d H:i:s')) >= strtotime(file_get_contents(RUN)) + 5)
    unlink(RUN);

if ($run = @fopen(RUN, 'x')) {
    fwrite($run, date('Y-m-d H:i:s'));
    fclose($run);
    err('First instance');
} else {
    err('Prevent duplicate. mypid => process will be shut down', true);
}

// If ws.pid file exists
if (file_exists(PID))

    // If it contains PID of process
    if ($PID = trim(file_get_contents(PID))) {

        // If process having such PID still running
        if (checkpid($PID)) {

            // Log that, and initiate shutting down of current process to prevent duplicate
            err('ws.pid: ' . $PID . ' => process found. mypid => process will be shut down', true);

        // Else
        }  else {

            // Backup PID value and truncate ws.pid
            $wasPID = $PID; file_put_contents(PID, $PID = '');

            // Log that before going further
            err('ws.pid: ' . $wasPID . ' => proc not found => truncated. mypid => going further');
        }

    // Else if ws.pid is empty - log that before going further
    } else err('ws.pid: truncated. mypid => going further');

// Open pid-file
$pid = fopen(PID, 'c');

// Try to lock pid-file
$flock = flock($pid, LOCK_SH | LOCK_EX | LOCK_NB, $wouldblock);

// If opening of pid-file failed, or locking failed and locking wouldn't be blocking - thow exception
if ($pid === false || (!$flock && !$wouldblock))
    err('Error opening or locking lock file, may be caused by pid-file permission restrictions', true);

// Else if pid-file was already locked - exit
else if (!$flock && $wouldblock) err('Another instance is already running; terminating.', true);

// Set no time limit
set_time_limit(0);

// Ignore user about
ignore_user_abort(1);

// Create socket server
$server = stream_socket_server('tcp://0.0.0.0:' . $port . '/', $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);

// If socket server creation failed - exit
if (!$server) err('Can\'t start socket server: ' . $errstr . '(' . $errno . '), mypid => process will be shut down', true);

// Write current pid into ws.pid
fwrite($pid, getmypid());

// Log that we successfully started websocket-server
err('socket server started, ws.pid: ' . getmypid() . ' => updated');

// Session ids array
$sessidA = [];

// Languages array
$langidA = [];

// Clients' streams array
$clientA = [];

// Array, containing URL path mentioned within handshake-headers
$pathA = [];

// Array, containing hostname mentioned within handshake-headers
$hostA = [];

// RabbitMQ queues array
$queueA = [];

// Start serving
while (true) {

    // Clone clients' steams
    $listenA = $clientA;

    // Temporarily add server-stream sockets
    $listenA[] = $server;

    // Define/reset stream_select args
    $write = $except = [];

    // Check if something interesting happened
    $changed = stream_select($listenA, $write, $except, $rabbit ? 0 : null, $rabbit ? 200000 : null);

    // If something wrong happened - break
    if (!$rabbit && !$changed) break;

    // If server got new client
    if (in_array($server, $listenA)) {

        // Accept client's stream, but if handshake is not successful - close the client stream
        if ($clientI = stream_socket_accept($server, -1))
            if (!handshake($clientI, $ini,$clientA,$sessidA,$langidA, $rabbit,$queueA,$pathA,$hostA))
                fclose($clientI);

        // Remove server's socket from the list of sockets to be listened
        unset($listenA[array_search($server, $listenA)]);
    }

    // Foreach client stream
    foreach ($listenA as $index => $clientI) {

        // Read data
        $binary = fread($clientI, 10000);

        // If no data
        if (!$binary) {

            // Log that channel is going to be closed
            if ($ini['log']) logd('nobinary: ' . $index);

            // Close client's stream
            close($clientI, $index,$ini,$sessidA,$langidA,$rabbit,$queueA,$clientA, $pathA, $hostA);

            // Goto next stream
            continue;
        }

        // Log incoming data
        echo '--fread--' . "\n";
        echo $log = 'chl:' . $index . ', len: ' . strlen($binary) . ', raw:' . $binary;
        if ($ini['log']) logd($log);

        // Do
        do {

            // Decode data. If data is multi-framed - $binary - is the binary data representing the remaining frames
            list($data, $binary) = decode($binary);

            // Log decoded frame
            if ($ini['log']) logd('chl:' . $index . ', obj:' . json_encode($data));

            // If message type is  'close'
            if ($data['type'] == 'close') {

                // Log that channel is going to be closed
                if ($ini['log']) logd('type=close: ' . $index);

                // Close client's stream
                close($clientI, $index,$ini,$sessidA,$langidA,$rabbit,$queueA,$clientA, $pathA, $hostA);

                // Goto next stream
                continue 2;
            }

            // Here we skip messages having 'type' not 'text'
            if ($data['type'] != 'text') continue 2;

            // Convert json-decoded payload into an array
            $data = json_decode($data['payload'], true);

            // Write data to clients
            write($data, $index, $clientA, $ini);

        // While binary data remaining
        } while ($binary);
    }

    // If RabbitMQ is turned on
    if ($rabbit) foreach ($queueA as $index => $clientI) {

        // While at least 1 message is available within queue
        while ($msg = $rabbit->basic_get($index)) {

            // Convert json-decoded payload into an array
            $data = json_decode($msg->body, true);

            // Log that message's body
            logd('mq: ' . $msg->body);

            // Write data to clients
            write($data, $index, $clientA, $ini);

            // Acknowledge the queue about that message is picked
            $rabbit->basic_ack($msg->getDeliveryTag());
        }
    }
}

// Close server stream
fclose($server);

/**
 * Do a handshake for accepted client
 *
 * @param $clientI
 * @return array|bool
 */
function handshake($clientI, $ini, &$clientA, &$sessidA, &$langidA, $rabbit, &$queueA, &$pathA, &$hostA) {

    // Client's stream info
    $info = [];

    // Get request's 1st line
    $line = fgets($clientI);

    // If server is running not in secure mode, but client is trying
    // to connect via wss:// - return false todo: send TLS ALERT
    if (bin2hex($line[0]) == 16) return false;

    // Get request's method and URI
    $hdr = explode(' ', $line); $info['method'] = $hdr[0]; $info['uri'] = $hdr[1];

    // Get headers
    while ($line = rtrim(fgets($clientI)))
        if (preg_match('/\A(\S+): (.*)\z/', $line, $m))
            $info[$m[1]] = $m[2]; else break;

    // Get client's addr
    $addr = explode(':', stream_socket_get_name($clientI, true)); $info['ip'] = $addr[0]; $info['port'] = $addr[1];

    // If no 'Sec-WebSocket-Key' header provided - return false
    if (!$index = $info['Sec-WebSocket-Key']) return false;

    // Log attempt
    if ($ini['log']) logd('identified: ' . $index);

    // Prepare value for 'Sec-WebSocket-Accept' header
    $SecWebSocketAccept = base64_encode(pack('H*', sha1($index . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

    // If session is not detected - return false
    if (!session($info,$sessidA, $index, $ini,$langidA, $rabbit,$queueA,$pathA,$hostA)) {

        // Write 401 Unauthorized header
        fwrite($clientI, 'HTTP/1.1 401 Unauthorized'. "\r\n\r\n");

        // Return false
        return false;
    }

    // Prepare full headers list
    $upgrade = implode("\r\n", [
        'HTTP/1.1 101 Web Socket Protocol Handshake',
        'Upgrade: websocket',
        'Connection: Upgrade',
        'Sec-WebSocket-Accept: ' . $SecWebSocketAccept
    ]) . "\r\n\r\n";

    // Write upgrade headers into client's stream
    fwrite($clientI, $upgrade);

    // Write empty json
    fwrite($clientI, encode('{}'));

    // Add to collection
    $clientA[$index] = $clientI;

    // Log channel id of accepted client
    if ($ini['log']) logd('accepted: ' . $index);

    // Log headers
    if ($ini['log']) logd('handshake: ' . var_export($info, 1));

    // Return true
    return true;
}

/**
 * Check session
 *
 * @param $info
 * @param $sessidA
 * @param $index
 * @param $ini
 * @param $langidA
 * @param $rabbit
 * @param $queueA
 * @param $pathA
 * @param $hostA
 * @return bool
 */
function session($info, &$sessidA, $index, $ini, &$langidA, $rabbit, &$queueA, &$pathA, &$hostA) {

    // If session id is NOT detected - return false
    if (!preg_match('~PHPSESSID=([^; ]+)~', $info['Cookie'], $sessid)) return false;

    // Get language
    preg_match('~i-language=([a-zA-Z\-]{2,5})~', $info['Cookie'], $langid);

    // Init curl
    $ch = curl_init();

    // Log
    if ($ini['log']) logd('opentab init: ' . $index);

    // Build url
    $prevcid = '';

    // If query string given in $info['path'] and ?prev=xxx param is there
    if ($q = parse_url($info['uri'], PHP_URL_QUERY)) {

        // Get query params
        parse_str($q, $a);

        // Append prev cid
        if (isset($a['prevcid'])) $prevcid = $a['prevcid'];
    }

    // Get path, as different Indi Engine instances can run on different dirs within same domain
    $path = rtrim(parse_url($info['uri'], PHP_URL_PATH), '/');

    // Stip '/index.php' from path (workaround for apache mod_proxy_wstunnel)
    $path = str_replace('/index.php', '', $path);

    // Stip port from Origin
    $host = preg_replace('~:[0-9]+$~', '', $info['Origin']);

    // Set opts
    curl_setopt_array($ch, [
        CURLOPT_URL => $host . $path . '/realtime/opentab/',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => [
            'Indi-Auth: ' . implode(':', [$sessid[1], $langid[1], $index]),
            'Cookie: ' . $info['Cookie'] . rif($prevcid, '; prevcid=$1'),
        ]
    ]);

    // Exec and get output and/or error, if any
    $out = curl_exec($ch); $err = curl_error($ch);

    // Log output and/or error
    logd('curl reponse: ' . $out);

    // Close curl
    curl_close($ch);

    // If curl error occured
    if ($err) {

        // Log
        if ($ini['log']) logd('opentab failed: ' . $index . ' => curl error: ' . $err);

        // Return false
        return false;
    }

    // If realtime-entry of type=channel was successfully created
    if (json_decode($out)->success) {

        // If RabbitMQ is turned on
        if ($rabbit) {

            // Declare queue
            $rabbit->queue_declare($index, false, false, true, false);

            // Collect client socket
            $queueA[$index] = $clientI;
        }

        // Remember session id, language, host and path
        $sessidA[$index] = $sessid[1];
        $langidA[$index] = $langid[1];
        $hostA  [$index] = $host;
        $pathA  [$index] = $path;

        // Log
        if ($ini['log']) logd('opentab done: ' . $index . ' => ' . $host . $path . '/realtime/opentab/');

        // Return true
        return true;

    // Else
    } else {

        // Log
        if ($ini['log']) logd('opentab failed: ' . $index . ' => session expired');

        // Return false
        return false;
    }
}

/**
 * Encode the message before being sent to client
 *
 * @param $payload
 * @param string $type
 * @param bool $masked
 * @return array|string
 */
function encode($payload, $type = 'text', $masked = false) {

    // Stringify $payload
    if (is_array($payload)) $payload = json_encode($payload);

    // Frame head
    $fh = [];

    // Get payload length
    $payloadLength = strlen($payload);

    // Frame types
    $typeA = ['text' => 129, 'close' => 136, 'ping' => 137, 'pong' => 138];

    // Start setting up frame head
    $fh[0] = $typeA[$type];

    // If payload length is greater than 65kb
    if ($payloadLength > 65535) {

        // Setup payload binary length
        $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);

        // Finish setting up frame head
        $fh[1] = ($masked === true) ? 255 : 127; for ($i = 0; $i < 8; $i++) $fh[$i + 2] = bindec($payloadLengthBin[$i]);
        if ($fh[2] > 127) return ['type' => '', 'payload' => '', 'error' => 'frame too large (1004)'];

    // Else if payload length is greater than 125b
    } else if ($payloadLength > 125) {

        // Setup payload binary length
        $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);

        // Finish setting up frame head
        $fh[1] = ($masked === true) ? 254 : 126;
        $fh[2] = bindec($payloadLengthBin[0]);
        $fh[3] = bindec($payloadLengthBin[1]);

    // Else
    } else $fh[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;

    // Convert frame head to string
    foreach (array_keys($fh) as $i) $fh[$i] = chr($fh[$i]);

    // If masked
    if ($masked === true) {

        // Generate mask:
        $mask = []; for ($i = 0; $i < 4; $i++) $mask[$i] = chr(rand(0, 255));

        // Append
        $fh = array_merge($fh, $mask);
    }

    // Stringify frame head
    $frame = implode('', $fh);

    // Append payload
    for ($i = 0; $i < $payloadLength; $i++) $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];

    // Return
    return $frame;
}

/**
 * Decode websocket data
 *
 * @param $data
 * @return array|bool
 */
function decode($data) {

    // Decoded data array
    $decoded = ['payload' => ''];

    // Detect opcode using first byte
    $fbb = sprintf('%08b', ord($data[0]));
    $opcode = bindec(substr($fbb, 4, 4));

    // Detect whether or not data is masked using second byte binary
    $sbb = sprintf('%08b', ord($data[1]));
    $masked = $sbb[0] == '1';

    // Detect payload length
    $payloadLength = ord($data[1]) & 127;

    // If unmasked frame data received - return error msg
    if (!$masked) return ['type' => '', 'payload' => '', 'error' => 'protocol error (1002)'];

    // Try to detect frame type, or return error msg
    $typeA = [1 => 'text', 2 => 'binary', 8 => 'close', 9 => 'ping', 10 => 'pong'];
    if (!$decoded['type'] = $typeA[$opcode])
        return ['type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)'];

    // If payload length is 126
    if ($payloadLength === 126) {

        // Get mask
        $mask = substr($data, 4, 4);

        // Setup payload offset
        $payloadOffset = 8;

        // Detect data length
        $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;

    // If payload length is 127
    } else if ($payloadLength === 127) {

        // Get mask
        $mask = substr($data, 10, 4);

        // Setup payload offset
        $payloadOffset = 14;

        // Detect data length
        $tmp = ''; for ($i = 0; $i < 8; $i++) $tmp .= sprintf('%08b', ord($data[$i + 2]));
        $dataLength = bindec($tmp) + $payloadOffset; unset($tmp);

    // Else
    } else {

        // Get mask
        $mask = substr($data, 2, 4);

        // Setup payload offset
        $payloadOffset = 6;

        // Detect data length
        $dataLength = $payloadLength + $payloadOffset;
    }

    // We have to check for large frames here. socket_recv cuts at 1024 bytes
    // so if websocket-frame is > 1024 bytes we have to wait until whole
    // data is transferred.
    if (strlen($data) < $dataLength) return false;

    // Unmask payload
    for ($i = $payloadOffset; $i < $dataLength; $i++)
        if (isset($data[$i]))
            $decoded['payload'] .= $data[$i] ^ $mask[($i - $payloadOffset) % 4];

    // Return decoded data and remaining binary data
    return [$decoded, substr($data, $dataLength)];
}

/**
 * Write data to clients
 *
 * @param $data
 * @param $index
 * @param $clientA
 * @param $ini
 */
function write($data, $index, &$clientA, $ini) {

    // If some user connected
    if ($data['type'] == 'open') {

        // Log that open-message is received
        if ($ini['log']) logd('open: ' . $data['uid'] . '-' . $index);

        $data = ['type' => 'opened', 'cid' => $index];

    // Else if previously connected user pings the server
    } else if ($data['type'] == 'ping') {

        // If logging is On - do log ping
        if ($ini['log']) logd('ping: ' . json_encode($data));

        // Change type to 'pong'
        $data['type'] = 'pong';
    }

    // Write message into that channel
    fwrite($clientA[$index], encode($data));
}

/**
 * Close client socket
 *
 * @param $clientI
 * @param $index
 * @param $ini
 * @param $sessidA
 * @param $langidA
 * @param $rabbit
 * @param $queueA
 * @param $clientA
 * @param $pathA
 * @param $hostA
 */
function close(&$clientI, $index, &$ini, &$sessidA, &$langidA, &$rabbit, &$queueA, &$clientA, &$pathA, &$hostA) {

    // Close client's current stream
    fclose($clientI);

    // If queue exists
    if (isset($queueA[$index])) {

        // Delete queue
        $rabbit->queue_delete($index);

        // Unset queue dict
        unset($queueA[$index]);
    }

    // Unset current stream
    unset($clientA[$index]); echo 'close';

    // Log that channel was closed
    if ($ini['log']) logd('close: ' . $index);

    // Init curl
    $ch = curl_init();

    // Log that Indi Engine is going to be notified about closed channel
    if ($ini['log']) logd('closetab init: ' . $index);

    // Set opts
    curl_setopt_array($ch, [
        CURLOPT_URL => $hostA[$index] . $pathA[$index] . '/realtime/closetab/',
        CURLOPT_HTTPHEADER => [
            'Indi-Auth: ' . implode(':', [$sessidA[$index], $langidA[$index], $index]),
            'Cookie: ' . 'PHPSESSID=' . $sessidA[$index] . '; i-language=' . $langidA[$index]
        ]
    ]);

    // Exec and get output and/or error, if any
    $out = curl_exec($ch); $err = curl_error($ch);

    // Log output and/or error
    logd('curl reponse: ' . $out); logd('curl error: ' . $err);

    // Close curl
    curl_close($ch);

    // Log
    if ($ini['log']) logd('closetab done: ' . $index . ' => ' . $hostA[$index] . $pathA[$index] . '/realtime/closetab/');

    // Drop session id and language id
    unset($sessidA[$index], $langidA[$index], $pathA[$index], $hostA[$index]);
}

// Shutdown
shutdown();