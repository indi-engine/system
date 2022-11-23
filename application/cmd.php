<?php

// Setup CMD constant, indicating that this execution was started via command line
define('CMD', true);

// Get uri
if (isset($argv[1])) $uri = $argv[1]; else exit('No command given');

// Get this directory with 'right' slashes
$_dir_ = str_replace('\\', '/', __DIR__);

// Change current working directory to project root
chdir(realpath($_dir_ . '/../../../..'));

// Get vendor name
preg_match('~^(?<DOCUMENT_ROOT>.+?)/(?<VDR>vendor/.+?)/~', $_dir_, $m);

// Get filepath of a temporary file, containing environment variables
if (isset($argv[2])) {

    // If 2nd arg is not a path of an existsing file - exit
    if (!is_file($tmp = $argv[2])) exit("File \"$tmp\" does not exist");

    // Extract environment variables
    extract(json_decode(file_get_contents($tmp = $argv[2]), true));

    // Delete temporary file
    unlink($tmp);
}

// Provide default values for environment variables at least expected by Indi Engine
$default = [
    'DOCUMENT_ROOT' => $m['DOCUMENT_ROOT'],
    'REQUEST_METHOD' => 'GET',
    'HTTP_HOST' => 'localhost',
    'VDR' => $m['VDR'],
    'STD' => ''
];

// Apply those
foreach ($default as $key => $value)
    if (!isset($_SERVER[$key]) || !$_SERVER[$key])
        $_SERVER[$key] = $value;

// Boot
include 'index.php';

// If $method is not defined in 'someSection/someAction'-format - assume it's action in CmdController
if (!preg_match('~^([a-zA-Z0-9]+)/([a-zA-Z0-9]+)$~', $uri)) $uri = 'cmd/' . $uri;

// Dispatch method
uri()->dispatch($uri, $args ?? []);
