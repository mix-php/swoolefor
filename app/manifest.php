<?php

// 应用清单
return [

    // 应用名称
    'appName'    => 'SwooleFor',

    // 应用版本
    'appVersion' => '1.1.7',

    // 应用调试
    'appDebug'   => true,

    // 基础路径
    'basePath'   => str_replace(['phar://', '/'], ['', DIRECTORY_SEPARATOR], dirname(dirname(__DIR__))),

    // 协程配置
    'coroutine'  => [
        true,
        [
            'max_coroutine' => 300000,
            'hook_flags'    => 1879048191 ^ 256, // SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_FILE,
        ],
    ],

    // 命令
    'commands'   => [

        \App\Commands\MainCommand::class,
        'description' => "\tRun your swoole application",
        'options'     => [
            [['e', 'exec'], 'description' => 'Swoole application or other script start command'],
            [['d', 'daemon'], 'description' => 'Run in the background'],
            ['no-inotify', 'description' => "Do not use the inotify extension"],
            ['watch', 'description' => "Watch code file directory"],
            ['delay', 'description' => "File change delay processing (seconds)"],
            ['ext', 'description' => "\tMonitor only changes to these extensions"],
            ['signal', 'description' => "Send this signal to the process"],
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
            'name'       => 'log',
            // 作用域
            'scope'      => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'      => \Mix\Log\Logger::class,
            // 属性注入
            'properties' => [
                // 日志记录级别
                'levels'  => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
                // 处理器
                'handler' => ['ref' => \Mix\Log\MultiHandler::class],
            ],
        ],

        // 日志处理器
        [
            // 类路径
            'class'           => \Mix\Log\MultiHandler::class,
            // 构造函数注入
            'constructorArgs' => [
                // 标准输出处理器
                ['ref' => \Mix\Log\StdoutHandler::class],
            ],
        ],

        // 日志标准输出处理器
        [
            // 类路径
            'class' => \Mix\Log\StdoutHandler::class,
        ],

        // 事件调度器
        [
            // 名称
            'name'            => 'event',
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
