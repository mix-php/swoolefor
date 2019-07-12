<?php

namespace Cli\Libraries;

use Mix\Core\Bean\AbstractObject;
use Mix\Helper\ProcessHelper;
use Mix\Log\Logger;

/**
 * Class Executor
 * @package Cli\Libraries
 */
class Executor extends AbstractObject
{

    /**
     * @var string
     */
    public $cmd;

    /**
     * @var int
     */
    public $signal;

    /**
     * @var int
     */
    protected $_pid;

    /**
     * @var bool
     */
    protected $_quit = false;

    /**
     * 启动
     */
    public function start()
    {
        // 输出信息
        $logger = Logger::make(app()->get("log"));
        $logger->info("executor start, cmd: [{$this->cmd}]");
        xgo(function () {
            $logger = Logger::make(app()->get("log"));
            // fork进程
            $descriptorspec = [
                0 => ["pipe", "r"], // 标准输入，子进程从此管道中读取数据
                1 => ["pipe", "w"], // 标准输出，子进程向此管道中写入数据
                2 => ["pipe", "w"], // 标准错误
            ];
            $process        = proc_open($this->cmd, $descriptorspec, $pipes);
            $status         = proc_get_status($process);
            $this->_pid     = $status['pid'];
            $logger->info('fork sub process, pid: {pid}', ['pid' => $this->_pid]);
            // 等待进程停止
            do {
                stream_get_contents($pipes[2]);
                $status = proc_get_status($process);
            } while ($status['running']);
            // 获取最新状态
            $logger->info('sub process exit, pid: {pid}, exitcode: {exitcode}, termsig: {termsig}, stopsig: {stopsig}', ['pid' => $this->_pid, 'exitcode' => $status['exitcode'], 'termsig' => $status['termsig'], 'stopsig' => $status['stopsig']]);
            // 重新fork进程
            $this->_quit or $this->start();
        });
    }

    /**
     * 停止
     */
    public function stop()
    {
        $logger   = Logger::make(app()->get("log"));
        $pid      = $this->_pid;
        $signal   = (int)$this->signal;
        $waitTime = 60 * 1000000; // 超时时间
        // 标记退出
        $this->_quit = true;
        // kill进程
        $logger->info('executor stop');
        $logger->info('signal to process, pid: {pid}, signal: {signal}', ['pid' => $pid, 'signal' => $signal]);
        ProcessHelper::kill($pid, $signal);
        while (static::isRunning($pid) && $waitTime > 0) {
            $interval = 100000;
            usleep($interval);
            $waitTime -= $interval;
        }
        if (static::isRunning($pid)) {
            $logger->info('executor stop timeout, kill -9 pid: {pid}', ['pid' => $this->_pid]);
            ProcessHelper::kill($pid, SIGKILL);
        }
    }

    /**
     * 重启
     */
    public function restart()
    {
        $pid    = $this->_pid;
        $signal = (int)$this->signal;
        Logger::make(app()->get("log"))->info('signal to process, pid: {pid}, signal: {signal}', ['pid' => $pid, 'signal' => $signal]);
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
