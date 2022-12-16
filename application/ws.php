<?php
// Set error reporting
error_reporting(version_compare(PHP_VERSION, '5.4.0', 'ge') ? E_ALL ^ E_NOTICE ^ E_STRICT : E_ALL ^ E_NOTICE);

// Define project document root
define('DOC', rtrim(__DIR__, '\\/') . '/../../../..');

// Change dir
chdir(__DIR__);

// Include func.php
include '../library/func.php';

// Require autoload.php
require_once DOC . '/vendor/autoload.php';

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

// Set error handler
set_error_handler('err');

// Do some checks for ini-file
if (!is_file($ini = DOC . '/application/config.ini')) err('No ini-file found', true);
if (!$ini = parse_ini_file($ini, true)) err('Ini-file found but parsing failed', true);

// If ini's 'rabbit' section exists
if (!$ini['rabbitmq']['enabled'])
    err('Can\'t start \'queue.deleted\' events listener server: rabbitmq is not enabled, mypid => process will be shut down', true);

// Set no time limit
set_time_limit(0);

// Ignore user about
ignore_user_abort(1);

// Prepare rabbit
$rabbit = (new PhpAmqpLib\Connection\AMQPStreamConnection(
    $ini['rabbitmq']['host'],
    $ini['rabbitmq']['port'],
    $ini['rabbitmq']['user'],
    $ini['rabbitmq']['pass']
))->channel();

// Declare queue that we'll be working with
$rabbit->queue_declare($ini['db']['name'], false, false, true);

// Make sure event of type 'queue.deleted' will be sent to our queue by 'amq.rabbitmq.event' exchange
$rabbit->queue_bind($queue, 'amq.rabbitmq.event', 'queue.deleted');

// Log that we successfully started websocket-server
err('\'queue.deleted\' events listener server started, ws.pid: ' . getmypid() . ' => updated');

// Start serving
while (true) {

    // While at least 1 unprocessed event of type 'queue.deleted' is available
    while ($msg = $rabbit->basic_get($queue)) {

        // Get channel token, which is used as the queue name
        $channel = $msg->get_properties()['application_headers']->getNativeData()['name'];

        // Split by --
        list ($dbname, $channel) = explode('--', $channel);

        // If it was queue from this instance of Indi Engine
        if ($dbname == $ini['db']['name']) {

            // Call Admin_RealtimeController->closeAction()
            cmd('realtime/closetab', [$channel]);

            // Acknowledge the queue about that message is processed
            $rabbit->basic_ack($msg->getDeliveryTag());

        // Else
        } else {

            // Reject message
            $rabbit->basic_nack($msg->getDeliveryTag());
        }
    }

    // Wait 200ms
    usleep(200000);
}