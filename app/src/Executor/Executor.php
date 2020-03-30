<?php

namespace App\Executor;

use Mix\Bean\BeanInjector;
use Mix\Concurrent\Timer;
use Mix\Helper\ProcessHelper;
use Mix\Log\Logger;
use Swoole\Process;

/**
 * Class Executor
 * @package App\Executor
 * @author liu,jian <coder.keda@gmail.com>
 */
class Executor
{

    /**
     * @var string
     */
    public $exec;

    /**
     * @var int
     */
    public $signal;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var int
     */
    protected $pid;

    /**
     * @var bool
     */
    protected $quit = false;

    /**
     * @var float
     */
    protected $forkTime;

    /**
     * Executor constructor.
     * @param $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct($config)
    {
        BeanInjector::inject($this, $config);
        $this->log = context()->get('log');
    }

    /**
     * 启动
     */
    public function start()
    {
        // 输出信息
        $log = $this->log;
        $log->info("executor start, exec: [{$this->exec}]");
        // fork进程
        $process = new Process(function (Process $process) {
            $args = array_values(array_filter(explode(' ', $this->exec)));
            $file = array_shift($args);
            is_file($file) and $process->exec($file, $args);
        });
        $process->start();
        $this->pid = $process->pid;

        $log->info('fork sub process, pid: {pid}', ['pid' => $this->pid]);
        $this->forkTime = static::microtime();
    }

    /**
     * Wait
     */
    public function wait()
    {
        $log = $this->log;
        while ($status = Process::wait(false)) {
            $log->info('sub process exit, pid: {pid}, exitcode: {exitcode}, stopsig: {stopsig}', ['pid' => $status['pid'], 'exitcode' => $status['code'], 'stopsig' => $status['signal']]);
        }
        // 进程终止太快处理
        if (static::microtime() - $this->forkTime < 0.5) {
            $log->warning('sub process exit too fast, sleep 2 seconds');
            // 延迟fork
            $timer = Timer::new(false);
            $timer->after(2000, function () {
                // 重新fork进程
                $this->quit or $this->start();
            });
            return;
        }
        // 重新fork进程
        $this->quit or $this->start();
    }

    /**
     * 获取当前微妙时间
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * 停止
     */
    public function stop()
    {
        $log      = $this->log;
        $pid      = $this->pid;
        $signal   = (int)$this->signal;
        $waitTime = 60 * 1000; // 超时时间
        // 标记退出
        $this->quit = true;
        // kill进程
        $log->info('executor stop');
        $log->info('signal to process, pid: {pid}, signal: {signal}', ['pid' => $pid, 'signal' => $signal]);
        ProcessHelper::kill($pid, $signal);
        // 判断执行状态
        $timer = Timer::new(false);
        $timer->tick(200, function () use ($timer, $pid, &$waitTime) {
            if (static::isRunning($pid) && $waitTime > 0) {
                $waitTime -= 200;
            } else {
                if (static::isRunning($pid)) {
                    $this->log->info('executor stop timeout, kill -9 pid: {pid}', ['pid' => $this->pid]);
                    ProcessHelper::kill($pid, SIGKILL);
                }
                $timer->clear();
            }
        });
    }

    /**
     * 重启
     */
    public function restart()
    {
        $pid    = $this->pid;
        $signal = (int)$this->signal;
        $this->log->info('signal to process, pid: {pid}, signal: {signal}', ['pid' => $pid, 'signal' => $signal]);
        ProcessHelper::kill($pid, $this->signal);
    }

    /**
     * 是否在执行
     * @param $pid
     * @return bool
     */
    protected static function isRunning($pid)
    {
        return ProcessHelper::kill($pid, 0);
    }

}
