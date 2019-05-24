<?php

// Console应用配置
return [

    // 应用名称
    'appName'          => 'mix-inotify',

    // 应用版本
    'appVersion'       => '1.0.1',

    // 应用调试
    'appDebug'         => true,

    // 基础路径
    'basePath'         => str_replace(['phar://', '/'], ['', DIRECTORY_SEPARATOR], dirname(dirname(__DIR__))),

    // 运行目录路径
    'runtimePath'      => '',

    // 命令命名空间
    'commandNamespace' => 'Cli\Commands',

    // 命令
    'commands'         => [

        'monitor' => [
            'Monitor',
            'description' => "Monitor code changes and automatically restart the server",
            'options'     => [
                [['d', 'daemon'], 'description' => 'Run in the background'],
                ['dir', 'description' => 'File directory path to monitor code changes'],
                ['pidfile', 'description' => "Automatically restart the server pid file path"],
            ],
        ],

    ],

    // 组件配置
    'components'       => [

        // 错误
        'error' => [
            // 依赖引用
            'ref' => beanname(Mix\Console\Error::class),
        ],

        // 日志
        'log'   => [
            // 依赖引用
            'ref' => beanname(Mix\Log\Logger::class),
        ],

    ],

    // 依赖配置
    'beans'            => [

        [
            // 类路径
            'class'      => Mix\Console\Error::class,
            // 属性
            'properties' => [
                // 错误级别
                'level' => E_ALL,
            ],
        ],

        // 日志
        [
            // 类路径
            'class'      => Mix\Log\Logger::class,
            // 属性
            'properties' => [
                // 日志记录级别
                'levels'  => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
                // 处理器
                'handler' => [
                    // 依赖引用
                    'ref' => beanname(Mix\Log\MultiHandler::class),
                ],
            ],
        ],

        // 日志处理器
        [
            // 类路径
            'class'      => Mix\Log\MultiHandler::class,
            // 属性
            'properties' => [
                // 日志处理器集合
                'handlers' => [
                    // 标准输出处理器
                    [
                        // 依赖引用
                        'ref' => beanname(Mix\Log\StdoutHandler::class),
                    ],
                    // 文件处理器
                    [
                        // 依赖引用
                        'ref' => beanname(Mix\Log\FileHandler::class),
                    ],
                ],
            ],
        ],

        // 日志标准输出处理器
        [
            // 类路径
            'class' => Mix\Log\StdoutHandler::class,
        ],

        // 日志文件处理器
        [
            // 类路径
            'class' => Mix\Log\FileHandler::class,
        ],

    ],

];
