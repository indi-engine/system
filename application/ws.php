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

// If ini's 'rabbit' section exists
if (!$ini['rabbitmq']['enabled'])
    err('Can\'t start \'queue.deleted\' events listener server: rabbitmq is not enabled, mypid => process will be shut down', true);

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

// Write current pid into ws.pid
fwrite($pid, getmypid());

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

// Shutdown
shutdown();