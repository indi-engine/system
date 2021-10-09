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
error_reporting(version_compare(PHP_VERSION, '5.4.0', 'ge') ? E_ALL ^ E_NOTICE ^ E_STRICT : E_ALL ^ E_NOTICE);
ini_set('display_errors', 'On');

// Set up STD server variable in case if multiple IndiEngine projects
// are running within same document root, and there is one project that
// is located in DOCUMENT_ROOT and others are in subfolders, so STD server
// variable is passed WITH 'REDIRECT_' prefix, which is not covered by engine
if (!$_SERVER['STD'] && $_SERVER['REDIRECT_STD']) $_SERVER['STD'] = $_SERVER['REDIRECT_STD'];

// Setup $_SERVER['STD'] as php constant, for being easier accessible
define('STD', $_SERVER['STD']);

// Setup $GLOBALS['cmsOnlyMode'] as php constant, for being easier accessible
define('COM', $GLOBALS['cmsOnlyMode']);

// Setup PRE constant, representing total url shift for all urls in cms area
define('PRE', STD . (COM ? '' : '/admin'));

// Setup DOC constant, representing $_SERVER['DOCUMENT_ROOT'] environment variable, with no right-side slash
define('DOC', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));

// Setup URI constant, representing $_SERVER['REQUEST_URI'] environment variable, for short-hand accessibility
define('URI', $_SERVER['REQUEST_URI'] == '/' ? '/' : rtrim($_SERVER['REQUEST_URI'], '/'));

// Setup CMD constant, indicating that this execution was not started via Indi::cmd()
// In case if execution WAS started via Indi::cmd(), this constant will be already defined,
// so constant's value won't be overwritten by below-line definition
define('CMD', false);

// Setup APP constant, indicating that this execution was initiated using Indi Engine standalone client-app
define('APP', array_key_exists('HTTP_INDI_AUTH', $_SERVER));

// Set include path. Here we add more include paths, in case if some stuff is related to front module only,
// but required to be available in admin module.
$dirs = array('../www/', (COM || preg_match('~^' . preg_quote(STD, '~') . '/admin\b~', URI) ? '' : '../coref/'), '../core/');
$subs = array('library', 'application/controllers', 'application/models'); $p = PATH_SEPARATOR;
foreach($dirs as $d) if ($d) foreach($subs as $s) $inc[] = $d . $s; $inc[] = get_include_path();
set_include_path(implode($p, $inc));

// Load misc functions
require('func.php');

// Require vendor
if (file_exists('vendor/autoload.php')) require_once('vendor/autoload.php');

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
Indi::ini('application/config.ini');
if (function_exists('geoip_country_code_by_name')
    && geoip_country_code_by_name($_SERVER['REMOTE_ADDR']) == 'GB')
        Indi::ini('lang')->admin = 'en';

// If request came from client-app - split 'Indi-Auth' header's value by ':', and set cookies
if (APP && $_ = explode(':', $_SERVER['HTTP_INDI_AUTH'])) {
    if ($_[0]) $_COOKIE['PHPSESSID'] = $_[0];
    if ($_[1]) setcookie('i-language', $_COOKIE['i-language'] = $_[1]);
    define('CID', $_[2] ?: false);
}

Indi::cache();
Indi::db(Indi::ini()->db);

// Save config and global request data to registry
Indi::post($_POST);
Indi::get($_GET);
Indi::files($_FILES);
unset($_POST, $_GET, $_FILES);

// Dispatch uri request
if (!CMD) Indi::uri()->dispatch();