<?php

namespace SwooleFor\Monitor;

use Mix\Bean\BeanInjector;
use Mix\Concurrent\Timer;
use Mix\Log\Logger;
use SwooleFor\Executor\Executor;
use SwooleFor\Helper\MonitorHelper;

/**
 * Class FileScanMonitor
 * @package SwooleFor\Monitor
 * @author liu,jian <coder.keda@gmail.com>
 */
class FileScanMonitor
{

    /**
     * @var string
     */
    public $dir;

    /**
     * @var int
     */
    public $delay;

    /**
     * @var array
     */
    public $ext;

    /**
     * @var Executor
     */
    public $executor;

    /**
     * @var Timer
     */
    public $timer;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var bool
     */
    protected $quit = false;

    /**
     * @var array
     */
    protected $hash = [];

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
        if (!$this->dir) {
            return;
        }
        // 输出信息
        $log = $this->log;
        $log->info("monitor start");
        $log->info("watch: {$this->dir}");
        $log->info("delay: {$this->delay}s");
        $log->info("ext: " . implode(',', $this->ext));
        // 初始化扫描
        $this->scan(true);
        // 定时扫描
        $timer = Timer::new();
        $timer->tick($this->delay * 1000, function () {
            if ($this->scan()) {
                $this->log->info("file scan: file or directory changes");
                $this->executor->restart();
            }
        });
        $this->timer = $timer;
    }

    /**
     * 扫描
     * @param bool $init
     * @return bool
     */
    protected function scan($init = false)
    {
        $files  = MonitorHelper::files($this->dir, $this->ext);
        $update = function () use ($files) {
            $this->hash = [];
            foreach ($files as $file) {
                $this->hash[$file] = md5_file($file);
            }
        };
        // init
        if ($init) {
            call_user_func($update);
            return true;
        }
        // update
        if (count($this->hash) != count($files)) {
            call_user_func($update);
            return true;
        }
        foreach ($files as $file) {
            if (!isset($this->hash[$file])) {
                call_user_func($update);
                return true;
            } elseif ($this->hash[$file] != md5_file($file)) {
                call_user_func($update);
                return true;
            }
        }
        return false;
    }

    /**
     * 停止
     */
    public function stop()
    {
        $this->log->info("monitor stop");
        $this->timer->clear();
    }

}
