<?php

namespace SwooleFor\Commands;

use SwooleFor\Libraries\Executor;
use SwooleFor\Libraries\Monitor;
use SwooleFor\Forms\MainForm;
use Mix\Console\CommandLine\Flag;
use Mix\Core\Coroutine\Channel;
use Mix\Core\Event;
use Mix\Helper\ProcessHelper;

/**
 * Class MainCommand
 * @package SwooleFor\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class MainCommand
{

    /**
     * 主函数
     */
    public function main()
    {
        // 获取参数
        $argv = [
            'exec'   => Flag::string(['e', 'exec'], ''),
            'daemon' => (int)Flag::bool(['d', 'daemon'], false),
            'watch'  => Flag::string('watch', ''),
            'delay'  => Flag::string('delay', '3'),
            'ext'    => Flag::string('ext', 'php,json'),
            'signal' => Flag::string('signal', (string)SIGTERM),
        ];
        // 使用模型
        $model = new MainForm($argv);
        $model->setScenario('main');
        if (!$model->validate()) {
            println($model->getError());
            exit;
        }
        // 守护处理
        if ($model->daemon) {
            ProcessHelper::daemon();
        }
        // Swoole 判断
        if (!extension_loaded('swoole') || version_compare(swoole_version(), '4.4') < 0) {
            println('Need swoole extension >= v4.4 to run, install: https://www.swoole.com/');
            exit;
        }
        // Inotify 判断
        if (!extension_loaded('inotify')) {
            println('Need inotify extension to run, install: http://pecl.php.net/package/inotify');
            exit;
        }
        // 欢迎信息
        static::welcome();
        // 执行
        xgo(function () use ($model) {
            $quit = new Channel();
            // 捕获信号
            ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], function ($signal) use ($quit) {
                $quit->push(true);
                ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], null);
            });
            // 启动执行器
            $executor = new Executor([
                'exec'   => $model->exec,
                'signal' => $model->signal,
            ]);
            $executor->start();
            // 启动监控器
            $monitor = new Monitor([
                'dir'      => $model->watch ?: Monitor::dir($model->exec),
                'delay'    => $model->delay,
                'ext'      => Monitor::ext($model->ext),
                'executor' => $executor,
            ]);
            $monitor->start();
            // 监听退出
            $quit->pop();
            $monitor->stop();
            $executor->stop();
        });
        Event::wait();
    }

    /**
     * 欢迎信息
     */
    protected static function welcome()
    {
        $appVersion    = app()->appVersion;
        $swooleVersion = SWOOLE_VERSION;
        echo <<<EOL
   _____                     __     ______          
  / ___/      ______  ____  / /__  / ____/___  _____
  \__ \ | /| / / __ \/ __ \/ / _ \/ /_  / __ \/ ___/
 ___/ / |/ |/ / /_/ / /_/ / /  __/ __/ / /_/ / /    
/____/|__/|__/\____/\____/_/\___/_/    \____/_/  Version: {$appVersion} Swoole: {$swooleVersion}


EOL;
    }

}
