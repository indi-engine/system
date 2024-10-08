<?php

// Flush Access-Control-Allow-Origin header
header('Access-Control-Allow-Origin: *');

// If request method is OPTIONS - flush headers for Indi app
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    header('Access-Control-Allow-Headers: indi-auth,x-requested-with');
    header('Access-Control-Allow-Method: POST');
    exit;
}

// Displays phpinfo if needed
if(isset($_GET['info'])){phpinfo();die();}

// Set up error reporting
error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);
ini_set('display_errors', 'Off');

// PHP 8.x compatiblity
$_SERVER['STD'] ??= null; $_SERVER['REDIRECT_STD'] ??=null; $GLOBALS['last'] = null;

// Set up STD server variable in case if multiple IndiEngine projects
// are running within same document root, and there is one project that
// is located in DOCUMENT_ROOT and others are in subfolders, so STD server
// variable is passed WITH 'REDIRECT_' prefix, which is not covered by engine
if (!$_SERVER['STD'] && $_SERVER['REDIRECT_STD']) $_SERVER['STD'] = $_SERVER['REDIRECT_STD'];

// Setup WIN constant, indicating whether we're on Windows
if (!defined('WIN')) define('WIN', preg_match('~^WIN~', PHP_OS));

// Setup DEV constant, indicating whether we're on localhost
define('DEV', preg_match('~local~', $_SERVER['HTTP_HOST']));

// Setup $_SERVER['STD'] and $_SERVER['VDR'] as php constants, for being easier accessible
define('STD', $_SERVER['STD'] ?? ''); define('VDR', '/' . $vdr = $_SERVER['VDR']);

// Setup $GLOBALS['cmsOnlyMode'] as php constant, for being easier accessible
define('COM', $GLOBALS['cmsOnlyMode'] ?? false);

// Setup PRE constant, representing total url shift for all urls in cms area
define('PRE', STD . (COM ? '' : '/admin'));

// Setup DOC constant, representing $_SERVER['DOCUMENT_ROOT'] environment variable, with no right-side slash
define('DOC', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));

// Setup DIR constant, representing the directory where current Indi Engine instance is located
define('DIR', DOC . STD);

// Setup URI constant, representing $_SERVER['REQUEST_URI'] environment variable, for short-hand accessibility
define('URI', $_SERVER['REQUEST_URI'] == '/' ? '/' : rtrim($_SERVER['REQUEST_URI'], '/'));

// Setup CMD constant, indicating that this execution was not started via process()
// In case if execution WAS started via process(), this constant will be already defined,
// so constant's value won't be overwritten by below-line definition
if (!defined('CMD')) define('CMD', false);

// Setup APP constant, indicating that this execution was initiated using Indi Engine standalone client-app
define('APP', array_key_exists('HTTP_INDI_AUTH', $_SERVER));

// Set include path. Here we add more include paths, in case if some stuff is related to front module only,
// but required to be available in admin module.
$dirs = ['', $vdr . '/public/', $vdr . '/system/'];
$subs = ['library', 'application/controllers', 'application/models']; $p = PATH_SEPARATOR;
foreach($dirs as $d) foreach($subs as $s) $inc[] = $d . $s; $inc[] = get_include_path();
set_include_path(implode($p, $inc));

// Load misc functions
require('func.php');

// Register autoloader
spl_autoload_register('autoloader');

// Set up error handlers for fatal errors, and other errors
register_shutdown_function('ehandler');
set_error_handler('ehandler');

// Performance detection. 'mt' mean 'microtime'
$mt = 0; function mt(){$m = microtime();list($mc, $s) = explode(' ', $m); $n = $s + $mc; $ret = $n - $GLOBALS['last']; $GLOBALS['last'] = $n; return $ret;} mt();

// Memory usage detection
$mu = 0; function mu(){$m = memory_get_usage(); $ret = $m - $GLOBALS['mu']; $GLOBALS['mu'] = $m; return number_format($ret);} mu();

// Load config and setup DB interface
ini('application/config.ini');
if (function_exists('geoip_country_code_by_name')
    && geoip_country_code_by_name($_SERVER['REMOTE_ADDR']) == 'GB')
        ini('lang')->admin = 'en';

// If request came from client-app - split 'Indi-Auth' header's value by ':', and set cookies
if (APP && $_ = explode(':', $_SERVER['HTTP_INDI_AUTH'])) {
    if ($_[0]) $_COOKIE['PHPSESSID'] = $_[0];
    if ($_[1] ?? 0) setcookie('i-language', $_COOKIE['i-language'] = $_[1]);
    define('CID', $_[2] ?? false);
}

// Spoof hosts for mysql and rabbitmq services if given by environment variables
foreach (['db' => 'MYSQL_HOST', 'rabbitmq' => 'RABBITMQ_HOST'] as $service => $env)
    if ($value = getenv($env)) ini()->$service->host = $value;

// Setup db connection
Indi::db(ini()->db);

// Save config and global request data to registry
Indi::post($_POST);
Indi::get($_GET);
Indi::files($_FILES);
unset($_POST, $_GET, $_FILES);

// Include l10n constants
foreach (['', VDR . '/public', VDR . '/system'] as $fraction)
    foreach (['', '/admin'] as $module)
        if ($lang = ini('lang')->{trim($module, '/') ?: 'front'} ?? 0)
            if (file_exists($file = DOC . STD . "$fraction/application/lang$module/$lang.php"))
                include_once $file;

// Dispatch uri request
if (!CMD) uri()->dispatch();