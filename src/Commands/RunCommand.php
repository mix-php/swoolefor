<?php

namespace Cli\Commands;

use Cli\Libraries\Executor;
use Cli\Libraries\Monitor;
use Cli\Models\RunForm;
use Mix\Console\CommandLine\Flag;
use Mix\Core\Coroutine;
use Mix\Core\Coroutine\Channel;
use Mix\Core\Event;
use Mix\Helper\ProcessHelper;

/**
 * Class RunCommand
 * @package Cli\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class RunCommand
{

    /**
     * 主函数
     */
    public function main()
    {
        // hook协程
        Coroutine::enableHook();
        // 获取参数
        $argv = [
            'cmd'        => Flag::string(['c', 'cmd'], ''),
            'daemon'     => (int)Flag::bool(['d', 'daemon'], false),
            'noNotify'   => (int)Flag::bool('no-notify', false),
            'interval'   => Flag::string('interval', '2'),
            'stopSignal' => Flag::string('stop-signal', (string)SIGTERM),
            'stopWait'   => Flag::string('stop-wait', '5'),
        ];
        // 使用模型
        $model             = new RunForm();
        $model->attributes = $argv;
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
                'cmd'        => $model->cmd,
                'stopSignal' => $model->stopSignal,
                'stopWait'   => $model->stopWait,
            ]);
            $executor->start();
            // 启动监控器
            $monitor = new Monitor([
                'dir'      => Monitor::dir($model->cmd),
                'interval' => $model->interval,
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

}
