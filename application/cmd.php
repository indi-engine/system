<?php
// Setup CMD constant, indicating that this execution was started via Indi::cmd()
define('CMD', true);

// Get method
$method = $argv[1];

// Get filepath of a temporary file, containing environment variables
$tmp = $argv[2];

// Extract environment variables
extract(json_decode(file_get_contents($tmp), true));

// Delete temporary file
unlink($tmp);

// Boot
include('index.php');

// Dispatch method
uri()->dispatch((COM ? '' : '/admin') . '/cmd/' . $method . '/', $args);
