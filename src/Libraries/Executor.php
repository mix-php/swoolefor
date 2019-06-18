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
    public $stopSignal;

    /**
     * @var int
     */
    public $stopWait;

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
        xgo(function () {
            $logger = Logger::make(app()->get("log"));

            $descriptorspec = [
                0 => ["pipe", "r"], // 标准输入，子进程从此管道中读取数据
                1 => ["pipe", "w"], // 标准输出，子进程向此管道中写入数据
                2 => ["pipe", "w"], // 标准错误
            ];
            $process        = proc_open($this->cmd, $descriptorspec, $pipes);
            $status         = proc_get_status($process);
            $this->_pid     = $status['pid'];
            $logger->info('fork process, pid: {pid}, cmd: [{cmd}]', ['pid' => $this->_pid, 'cmd' => $this->cmd]);

            // 等待进程停止
            while (proc_get_status($process)['running']) {
                stream_get_contents($pipes[2]);
            }

            // 获取最新状态
            $status = proc_get_status($process);
            $logger->info('process exit, pid: {pid}, exitcode: {exitcode}', ['pid' => $this->_pid, 'exitcode' => $status['exitcode']]);

            $this->_quit or $this->start(); // 退出后，重启
        });
    }

    /**
     * 停止
     */
    public function stop()
    {
        $this->_quit = true;
        $this->kill($this->_pid);
    }

    /**
     * 重启
     */
    public function restart()
    {
        Logger::make(app()->get("log"))->info('process restart, kill pid: {pid}', ['pid' => $this->_pid]);
        $this->kill($this->_pid);
    }

    /**
     * kill进程
     * @param $pid
     */
    protected function kill($pid)
    {
        ProcessHelper::kill($pid, SIGTERM);
        $waitTime = $this->stopWait * 1000000;
        while (static::isRunning($pid) && $waitTime > 0) {
            $ms = 100000;
            usleep($ms);
            $waitTime -= $ms;
        }
        if (static::isRunning($pid)) {
            Logger::make(app()->get("log"))->info('kill timeout, kill -9 pid: {pid}', ['pid' => $this->_pid]);
            ProcessHelper::kill($pid, SIGKILL);
        }
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
