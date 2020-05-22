<?php

// 应用清单
return [

    // 应用名称
    'appName'    => 'SwooleFor',

    // 应用版本
    'appVersion' => '1.2.0',

    // 应用调试
    'appDebug'   => false,

    // 基础路径
    'basePath'   => str_replace(['phar://', '/'], ['', DIRECTORY_SEPARATOR], dirname(dirname(__DIR__))),

    // 协程配置
    'coroutine'  => [
        false,
        [
            'max_coroutine' => 300000,
            'hook_flags'    => 1879048191 ^ 256, // SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_FILE,
        ],
    ],

    // 命令
    'commands'   => [

        \App\Commands\MainCommand::class,
        'usage'   => "\tRun your swoole application",
        'options' => [
            [['e', 'exec'], 'usage' => 'Swoole application or other script start command'],
            [['d', 'daemon'], 'usage' => 'Run in the background'],
            ['no-inotify', 'usage' => "Do not use the inotify extension"],
            ['watch', 'usage' => "Watch code file directory"],
            ['delay', 'usage' => "File change delay processing (seconds)"],
            ['ext', 'usage' => "\tMonitor only changes to these extensions"],
            ['signal', 'usage' => "Send this signal to the process"],
        ],

    ],

    // 依赖配置
    'beans'      => [

        // 错误
        [
            // 名称
            'name'            => 'error',
            // 作用域
            'scope'           => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'           => \Mix\Console\Error::class,
            // 构造函数注入
            'constructorArgs' => [
                // 错误级别
                E_ALL,
                // 日志
                ['ref' => 'log'],
            ],
        ],

        // 日志
        [
            // 名称
            'name'            => 'log',
            // 作用域
            'scope'           => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'           => \Mix\Monolog\Logger::class,
            // 构造函数注入
            'constructorArgs' => [
                // name
                'MIX',
                // handlers
                [new \Mix\Monolog\Handler\ConsoleHandler],
                // processors
                [new \Monolog\Processor\PsrLogMessageProcessor],
            ],
        ],

        // 事件调度器
        [
            // 名称
            'name'            => 'dispatcher',
            // 作用域
            'scope'           => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'           => \Mix\Event\EventDispatcher::class,
            // 构造函数注入
            'constructorArgs' => [
                \App\Listeners\CommandListener::class,
            ],
        ],

    ],

];
