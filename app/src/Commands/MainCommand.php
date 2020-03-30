<?php

namespace App\Commands;

use App\Executor\Executor;
use App\Helper\MonitorHelper;
use App\Monitor\FileScanMonitor;
use App\Monitor\InotifyMonitor;
use App\Forms\MainForm;
use Mix\Console\CommandLine\Flag;
use Mix\Helper\ProcessHelper;

/**
 * Class MainCommand
 * @package App\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class MainCommand
{

    /**
     * @var FileScanMonitor|InotifyMonitor
     */
    public $monitor;

    /**
     * @var Executor
     */
    public $executor;

    /**
     * 主函数
     */
    public function main()
    {
        // 获取参数
        $argv = [
            'exec'      => Flag::string(['e', 'exec'], ''),
            'noInotify' => (int)Flag::bool('no-inotify', false),
            'watch'     => Flag::string('watch', ''),
            'delay'     => Flag::string('delay', '3'),
            'ext'       => Flag::string('ext', 'php,json'),
            'signal'    => Flag::string('signal', (string)SIGTERM),
        ];
        // 使用模型
        $model = new MainForm($argv);
        $model->setScenario('main');
        if (!$model->validate()) {
            println($model->getError());
            return;
        }
        // Swoole 判断
        if (!extension_loaded('swoole') || version_compare(swoole_version(), '4.4') < 0) {
            println('Error: need swoole extension >= v4.4 to run, install: https://www.swoole.com/');
            return;
        }
        // Inotify 判断
        if (!$model->noInotify && !extension_loaded('inotify')) {
            println('Error: need inotify extension to run, install: http://pecl.php.net/package/inotify');
            println('Tip: use \'--no-inotify\' to switch to file scan');
            return;
        }
        // 欢迎信息
        static::welcome($model->noInotify);
        // 捕获信号
        ProcessHelper::signal([SIGCHLD], function ($signal) {
            $this->executor and $this->executor->wait();
        }, false);
        ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->monitor and $this->monitor->stop();
            $this->executor and $this->executor->stop();
            ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], null);
        }, false);
        // 启动执行器
        $executor = $this->executor = new Executor([
            'exec'   => $model->exec,
            'signal' => $model->signal,
        ]);
        $executor->start();
        // 启动监控器
        $class = InotifyMonitor::class;
        if ($model->noInotify) {
            $class = FileScanMonitor::class;
        }
        /** @var InotifyMonitor|FileScanMonitor $monitor */
        $monitor = $this->monitor = new $class([
            'dir'      => $model->watch ?: MonitorHelper::dir($model->exec),
            'delay'    => $model->delay,
            'ext'      => MonitorHelper::ext($model->ext),
            'executor' => $executor,
        ]);
        $monitor->start();
    }

    /**
     * 欢迎信息
     */
    protected static function welcome($noInotify)
    {
        $appVersion    = app()->appVersion;
        $swooleVersion = SWOOLE_VERSION;
        $monitor       = $noInotify ? 'file scan' : 'inotify';
        echo <<<EOL
   _____                     __     ______          
  / ___/      ______  ____  / /__  / ____/___  _____
  \__ \ | /| / / __ \/ __ \/ / _ \/ /_  / __ \/ ___/
 ___/ / |/ |/ / /_/ / /_/ / /  __/ __/ / /_/ / /    
/____/|__/|__/\____/\____/_/\___/_/    \____/_/  Version: {$appVersion}, Swoole: {$swooleVersion}, Use: {$monitor}


EOL;
    }

}
