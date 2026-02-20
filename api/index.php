<?php

// Enable error reporting for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Set the working directory to the project root
chdir(__DIR__ . '/..');

// Create required directories in /tmp for Vercel
$dirs = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions',
    '/tmp/framework/views',
    '/tmp/framework/cache',
    '/tmp/framework/sessions',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Forward to the public/index.php
require __DIR__ . '/../public/index.php';
