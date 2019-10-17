<?php

// Autoload
require __DIR__ . '/../vendor/autoload.php';

// Run application
$config = require __DIR__ . '/../app/manifest.php';
(new Mix\Console\Application($config))->run();
