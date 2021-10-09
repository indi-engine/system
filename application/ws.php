<?php
// Set error reporting
error_reporting(version_compare(PHP_VERSION, '5.4.0', 'ge') ? E_ALL ^ E_NOTICE ^ E_STRICT : E_ALL ^ E_NOTICE);

// Change dir
chdir(__DIR__);

// Include func.php
include '../library/func.php';

// Log that execution reached ws.php
wslog('mypid: ' . getmypid() . ', ' . 'Reached ws.php', __DIR__);

// Require autoload.php
require_once __DIR__ . '/../vendor/autoload.php';

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
    else file_put_contents(rtrim(__DIR__, '\\/') . '/' . 'ws.err', date('Y-m-d H:i:s => ') . 'mypid: ' . getmypid() . ', ' . print_r($msg, true) . "\n", FILE_APPEND);

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
        rtrim(__DIR__, '\\/') . '/' . 'ws.' . getmypid(). '.data',
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
if (!is_file($ini = '../../www/application/config.ini')) err('No ini-file found', true);
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
if (file_exists('ws.run') && strtotime(date('Y-m-d H:i:s')) >= strtotime(file_get_contents('ws.run')) + 5)
    unlink('ws.run');

if ($run = @fopen('ws.run', 'x')) {
    fwrite($run, date('Y-m-d H:i:s'));
    fclose($run);
    err('First instance');
} else {
    err('Prevent duplicate. mypid => process will be shut down', true);
}

// If ws.pid file exists
if (file_exists('ws.pid'))

    // If it contains PID of process
    if ($PID = trim(file_get_contents('ws.pid'))) {

        // If process having such PID still running
        if (checkpid($PID)) {

            // Log that, and initiate shutting down of current process to prevent duplicate
            err('ws.pid: ' . $PID . ' => process found. mypid => process will be shut down', true);

        // Else
        }  else {

            // Backup PID value and truncate ws.pid
            $wasPID = $PID; file_put_contents('ws.pid', $PID = '');

            // Log that before going further
            err('ws.pid: ' . $wasPID . ' => proc not found => truncated. mypid => going further');
        }

    // Else if ws.pid is empty - log that before going further
    } else err('ws.pid: truncated. mypid => going further');

// Open pid-file
$pid = fopen('ws.pid', 'c');

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

// Create context
$context = stream_context_create();

// If certificate file exists - make context to be secure
if (!is_file('ws.pem')) $prot = 'tcp'; else {
    stream_context_set_option($context, 'ssl', 'local_cert', 'ws.pem');
    stream_context_set_option($context, 'ssl', 'verify_peer', false);
    $prot = 'ssl';
    if (!$ini['pem']) $ini['pem'] = true;
}

// Create socket server
$server = stream_socket_server($prot . '://0.0.0.0:' . $port . '/', $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);

// If socket server creation failed - exit
if (!$server) err('Can\'t start socket server: ' . $errstr . '(' . $errno . '), mypid => process will be shut down', true);

// Write current pid into ws.pid
fwrite($pid, getmypid());

// Log that we successfully started websocket-server
err('socket server started, ws.pid: ' . getmypid() . ' => updated');

// Session ids array
$sessidA = array();

// Languages array
$langidA = array();

// Clients' streams array
$clientA = array();

// Array, containing URL path mentioned within handshake-headers
$pathA = array();

// Meta array, containing info about what users the active streams belongs to, and what roles those users are
$channelA = array();

// RabbitMQ queues array
$queueA = array();

// Start serving
while (true) {

    // Clone clients' steams
    $listenA = $clientA;

    // Temporarily add server-stream sockets
    $listenA[] = $server;

    // Define/reset stream_select args
    $write = $except = array();

    // Check if something interesting happened
    $changed = stream_select($listenA, $write, $except, $rabbit ? 0 : null, $rabbit ? 200000 : null);

    // If something wrong happened - break
    if (!$rabbit && !$changed) break;

    // If server got new client
    if (in_array($server, $listenA)) {

        // Accept client's stream
        if (($clientI = stream_socket_accept($server, -1)) && $info = handshake($clientI)) {

            // Write empty json
            fwrite($clientI, encode('{}'));

            // Add to collection
            $clientA[$index = $info['Sec-WebSocket-Key'] ?: count($clientA)] = $clientI;

            // Log channel id of accepted client
            if ($ini['log']) logd('accepted: ' . $index);

            // If  session id detected, and `realtime` entry of `type` = "session" found
            if (preg_match('~PHPSESSID=([^; ]+)~', $info['Cookie'], $sessid)) {

                // Log headers
                if ($ini['log']) logd($info);

                // Remember session id
                $sessidA[$index] = $sessid[1];

                // Log
                if ($ini['log']) logd('identified: ' . $index);

                // Get language
                preg_match('~i-language=([a-zA-Z\-]{2,5})~', $info['Cookie'], $langid);

                // Remember language
                $langidA[$index] = $langid[1];

                // Init curl
                $ch = curl_init();

                // If RabbitMQ is turned on
                if ($rabbit) {

                    // Declare queue
                    $rabbit->queue_declare($index, false, false, true, false);

                    // Collect client socket
                    $queueA[$index] = $clientI;
                }

                // Log
                if ($ini['log']) logd('?newtab init: ' . $index);

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
                $pathA[$index] = rtrim(parse_url($info['uri'], PHP_URL_PATH), '/');

                // Set opts
                curl_setopt_array($ch, [
                    CURLOPT_URL => $info['Origin'] . $pathA[$index] . '/realtime/?newtab',
                    CURLOPT_HTTPHEADER => [
                        'Indi-Auth: ' . implode(':', [$sessid[1], $langid[1], $index]),
                        'Cookie: ' . $info['Cookie'] . rif($prevcid, '; prevcid=$1'),
                    ]
                ]);

                // Exec and close curl
                curl_exec($ch); curl_close($ch);

                // Log
                if ($ini['log']) logd('?newtab done: ' . $index);
            }
        }

        // Remove server's socket from the list of sockets to be listened
        unset($listenA[array_search($server, $listenA)]);
    }

    // Foreach client stream
    foreach($listenA as $index => $clientI) {

        // Read data
        $binary = fread($clientI, 10000);

        // If no data
        if (!$binary) {

            // Log that channel is going to be closed
            if ($ini['log']) logd('nobinary: ' . $index);

            // Close client's stream
            close($clientI,$channelA, $index,$ini,$sessidA,$langidA,$rabbit,$queueA,$clientA, $pathA);

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
                close($clientI,$channelA, $index,$ini,$sessidA,$langidA,$rabbit,$queueA,$clientA, $pathA);

                // Goto next stream
                continue 2;
            }

            // Here we skip messages having 'type' not 'text'
            if ($data['type'] != 'text') continue 2;

            // Convert json-decoded payload into an array
            $data = json_decode($data['payload'], true);

            // Write data to clients
            write($data, $index, $channelA, $clientA, $ini);

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
            write($data, $index, $channelA, $clientA, $ini);

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
function handshake($clientI) {

    // Client's stream info
    $info = array();

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
    if (empty($info['Sec-WebSocket-Key'])) return false;

    // Prepare value for 'Sec-WebSocket-Accept' header
    $SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

    // Prepare full headers list
    $upgrade = implode("\r\n", array(
        'HTTP/1.1 101 Web Socket Protocol Handshake',
        'Upgrade: websocket',
        'Connection: Upgrade',
        'Sec-WebSocket-Accept: ' . $SecWebSocketAccept
    )) . "\r\n\r\n";

    // Write upgrade headers into client's stream
    fwrite($clientI, $upgrade);

    // Return request info
    return $info;
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

    // Frame head
    $fh = array();

    // Get payload length
    $payloadLength = strlen($payload);

    // Frame types
    $typeA = array('text' => 129, 'close' => 136, 'ping' => 137, 'pong' => 138);

    // Start setting up frame head
    $fh[0] = $typeA[$type];

    // If payload length is greater than 65kb
    if ($payloadLength > 65535) {

        // Setup payload binary length
        $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);

        // Finish setting up frame head
        $fh[1] = ($masked === true) ? 255 : 127; for ($i = 0; $i < 8; $i++) $fh[$i + 2] = bindec($payloadLengthBin[$i]);
        if ($fh[2] > 127) return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');

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
        $mask = array(); for ($i = 0; $i < 4; $i++) $mask[$i] = chr(rand(0, 255));

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
    $decoded = array('payload' => '');

    // Detect opcode using first byte
    $fbb = sprintf('%08b', ord($data[0]));
    $opcode = bindec(substr($fbb, 4, 4));

    // Detect whether or not data is masked using second byte binary
    $sbb = sprintf('%08b', ord($data[1]));
    $masked = $sbb[0] == '1';

    // Detect payload length
    $payloadLength = ord($data[1]) & 127;

    // If unmasked frame data received - return error msg
    if (!$masked) return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');

    // Try to detect frame type, or return error msg
    $typeA = array(1 => 'text', 2 => 'binary', 8 => 'close', 9 => 'ping', 10 => 'pong');
    if (!$decoded['type'] = $typeA[$opcode])
        return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');

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
 * @param $channelA
 * @param $clientA
 * @param $ini
 */
function write($data, $index, &$channelA, &$clientA, $ini) {

    // If some user connected
    if ($data['type'] == 'open') {

        // Get user's role id and self id
        list($rid, $uid) = explode('-', $data['uid']);

        // Organize channels
        if (!is_array($channelA[$rid])) $channelA[$rid] = array();
        if (!is_array($channelA[$rid][$uid])) $channelA[$rid][$uid] = array();
        $channelA[$rid][$uid][$index] = $index;

        // Log that open-message is received
        if ($ini['log']) logd('open: ' . $data['uid'] . '-' . $index);

        // Write message into channel
        fwrite($clientA[$channelA[$rid][$uid][$index]], encode(json_encode(array('type' => 'opened', 'cid' => $index))));

    // Else if previously connected user pings the server
    } else if ($data['type'] == 'ping') {

        // Get user's role id and self id
        list($rid, $uid) = explode('-', $data['uid']);

        // If logging is On - do log ping
        if ($ini['log']) logd('ping: ' . json_encode($data));

        // Change type to 'pong'
        $data['type'] = 'pong';

        // Write pong-message into channel
        fwrite($clientA[$channelA[$rid][$uid][$data['cid']]], encode(json_encode($data)));

    // Else if message type is 'notice' or 'reload'
    } else if ($data['type'] == 'notice' || $data['type'] == 'reload') {

        // If logging is On - do log
        if ($ini['log'] && $data['row']) file_put_contents('ws.' . $data['row'] . '.rcv.msg', date('Y-m-d H:i:s') . ' => ' . print_r($data, true) . "\n", FILE_APPEND);

        // Walk through roles, that recipients should have
        // If there are channels already exist for recipients, having such role
        foreach ($data['to'] as $rid => $uidA) if ($channelA[$rid]) {

            // If all recipients having such role should be messaged
            // Send message to all recipients having such role
            if ($data['to'][$rid] === true) foreach ($channelA[$rid] as $uid => $byrole)
                foreach ($channelA[$rid][$uid] as $cid) {

                    // If logging is On - do log
                    if ($ini['log']) file_put_contents('ws.' . $data['row'] . '.snt.msg', date('Y-m-d H:i:s => ') . print_r($data + compact('rid', 'uid', 'cid'), true) . "\n", FILE_APPEND);

                    // Write message into channel
                    fwrite($clientA[$channelA[$rid][$uid][$cid]], encode(json_encode($data)));
                }

            // Else if we have certain list of recipients
            // Send message to certain recipients
            else foreach ($data['to'][$rid] as $uid)
                if ($channelA[$rid][$uid])
                    foreach ($channelA[$rid][$uid] as $cid) {

                        // If logging is On - do log
                        if ($ini['log']) file_put_contents('ws.' . $data['row'] . '.snt.msg', date('Y-m-d H:i:s => ') . print_r($data + compact('rid', 'uid', 'cid'), true) . "\n", FILE_APPEND);

                        // Write message into channel
                        fwrite($clientA[$channelA[$rid][$uid][$cid]], encode(json_encode($data)));
                    }
        }

    // Else if message type is 'realtime'
    } else if ($data['type'] == 'realtime' || $data['type'] == 'F5' || $data['type'] == 'menu') {

        // If recepient channel is given as `true` - send message to all recepients
        if ($data['to'] === true) foreach ($clientA as $clientI) fwrite($clientI, encode(json_encode($data)));

        // Else if recepient channel id is given and channel exists - write message into that channel
        else if ($clientA[$data['to']]) fwrite($clientA[$data['to']], encode(json_encode($data)));
    }
}

/**
 * Close client socket
 *
 * @param $clientI
 * @param $channelA
 * @param $index
 * @param $ini
 * @param $sessidA
 * @param $langidA
 * @param $rabbit
 * @param $queueA
 * @param $clientA
 */
function close(&$clientI, &$channelA, $index, &$ini, &$sessidA, &$langidA, &$rabbit, &$queueA, &$clientA, &$pathA) {

    // Close client's current stream
    fclose($clientI);

    // Unset meta info, related to current stream
    foreach ($channelA as $rid => $byrid)
        foreach ($channelA[$rid] as $uid => $byuid) {
            if (isset($channelA[$rid][$uid][$index])) {

                // Remove channel from channels registry
                unset($channelA[$rid][$uid][$index]);

                // Log that channel was closed
                if ($ini['log']) logd('close: ' . $rid . '-' . $uid . '-' . $index);

                // If session id detected, and `realtime` entry of `type` = "session" found
                if (array_key_exists($index, $sessidA)) {

                    // Init curl
                    $ch = curl_init();

                    // Log that Indi Engine is going to be notified about closed channel
                    if ($ini['log']) logd('?closetab init: ' . $rid . '-' . $uid . '-' . $index);

                    // Set opts
                    curl_setopt_array($ch, [
                        CURLOPT_URL => ($ini['pem'] ? 'https' : 'http') . '://' . $ini['socket'] . $pathA[$index] . '/realtime/?closetab',
                        CURLOPT_HTTPHEADER => [
                            'Indi-Auth: ' . implode(':', [$sessidA[$index], $langidA[$index], $index]),
                            'Cookie: ' . 'PHPSESSID=' . $sessidA[$index] . '; i-language=' . $langidA[$index]
                        ]
                    ]);

                    // Exec and close curl
                    curl_exec($ch); curl_close($ch);

                    // Log that Indi Engine is notified about closed channel
                    if ($ini['log']) logd('?closetab done: ' . $rid . '-' . $uid . '-' . $index);

                    // Drop session id and language id
                    unset($sessidA[$index], $langidA[$index], $pathA[$index]);

                    // If queue exists
                    if (isset($queueA[$index])) {

                        // Delete queue
                        $rabbit->queue_delete($index);

                        // Unset queue dict
                        unset($queueA[$index]);
                    }
                }
            }
        }

    // Unset current stream
    unset($clientA[$index]); echo 'close';
}

// Shutdown
shutdown();