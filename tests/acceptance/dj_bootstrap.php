<?php

require_once __DIR__.'/../../vendor/autoload.php';

// Set timezone
date_default_timezone_set('UTC');

// Set error reporting
error_reporting(E_ALL);

// Load environment variables
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__.'/../..');
$dotenv->load();
