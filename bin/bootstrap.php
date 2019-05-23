<?php

/**
 * console 入口文件
 */

require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/main.php';
(new Mix\Console\Application($config))->run();
