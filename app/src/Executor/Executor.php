<?php

namespace App\Executor;

use Mix\Bean\BeanInjector;
use Mix\Concurrent\Timer;
use Mix\Helper\ProcessHelper;
use Mix\Log\Logger;

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
     * @var Timer
     */
    protected $timer;

    /**
     * Executor constructor.
     * @param $config
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
        xgo(function () use ($log) {
            // fork进程
            $descriptorspec = [
                0 => ["pipe", "r"], // 标准输入，子进程从此管道中读取数据
                1 => ["pipe", "w"], // 标准输出，子进程向此管道中写入数据
                2 => ["pipe", "w"], // 标准错误
            ];
            $process        = proc_open($this->exec, $descriptorspec, $pipes);
            $status         = proc_get_status($process);
            $this->pid      = $status['pid'];

            // 获取真实pid (在 ubuntu 系统 proc_open 会 sh -c 中转执行命令，因此 proc_get_status 获取不到真实的 pid)
            $this->timer and $this->timer->clear();
            $timer = Timer::new();
            $timer->tick(1000, function () use ($status, $log, $timer) {
                $output = null;
                exec(sprintf('ps --ppid %s', $status['pid']), $output);
                $output = array_filter($output);
                if (!empty($output[1])) {
                    preg_match_all('/[0-9]+/', $output[1], $matches);
                    if (isset($matches[0][0])) {
                        $this->pid = $matches[0][0];
                        $log->info('scan to real sub process pid: {pid}', ['pid' => $this->pid]);
                        $timer->clear();
                    }
                }
            });
            $this->timer = $timer;

            $log->info('fork sub process, pid: {pid}', ['pid' => $this->pid]);
            $forkTime = static::microtime();
            // 等待进程终止
            do {
                stream_get_contents($pipes[2]);
                $status = proc_get_status($process);
            } while ($status['running']);
            $log->info('sub process exit, pid: {pid}, exitcode: {exitcode}, termsig: {termsig}, stopsig: {stopsig}', ['pid' => $this->pid, 'exitcode' => $status['exitcode'], 'termsig' => $status['termsig'], 'stopsig' => $status['stopsig']]);
            // 进程终止太快处理
            if (static::microtime() - $forkTime < 0.5) {
                $log->warning('sub process exit too fast, sleep 2 seconds');
                sleep(2);
            }
            // 重新fork进程
            $this->quit or $this->start();
        });
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
        $waitTime = 60 * 1000000; // 超时时间
        // 标记退出
        $this->quit = true;
        // kill进程
        $log->info('executor stop');
        $log->info('signal to process, pid: {pid}, signal: {signal}', ['pid' => $pid, 'signal' => $signal]);
        ProcessHelper::kill($pid, $signal);
        while (static::isRunning($pid) && $waitTime > 0) {
            $interval = 100000;
            usleep($interval);
            $waitTime -= $interval;
        }
        if (static::isRunning($pid)) {
            $log->info('executor stop timeout, kill -9 pid: {pid}', ['pid' => $this->pid]);
            ProcessHelper::kill($pid, SIGKILL);
        }
        $this->timer and $this->timer->clear();
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
