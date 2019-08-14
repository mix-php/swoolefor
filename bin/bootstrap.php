#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Mix\Concurrent\Coroutine;

// Coroutine
if (extension_loaded('swoole')) {
    Coroutine::enableHook(SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_FILE);
    Coroutine::set([
        'max_coroutine' => 300000,
    ]);
}

// Run application
$config = require __DIR__ . '/../config/main.php';
(new Mix\Console\Application($config))->run();
