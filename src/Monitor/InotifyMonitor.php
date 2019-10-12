<?php

namespace SwooleFor\Monitor;

use Mix\Bean\BeanInjector;
use Mix\Log\Logger;
use SwooleFor\Executor\Executor;
use SwooleFor\Helper\MonitorHelper;

/**
 * Class InotifyMonitor
 * @package SwooleFor\Monitor
 * @author liu,jian <coder.keda@gmail.com>
 */
class InotifyMonitor
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
     * @var Logger
     */
    protected $log;

    /**
     * @var resource
     */
    protected $notify;

    /**
     * @var bool
     */
    protected $quit = false;

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
        xgo(function () use ($log) {
            // 监听全部目录
            $folders      = MonitorHelper::folders($this->dir);
            $this->notify = $notify = inotify_init();
            foreach ($folders as $folder) {
                $ret = inotify_add_watch($notify, $folder, IN_CLOSE_WRITE | IN_CREATE | IN_DELETE);
                if (!$ret) {
                    throw new \RuntimeException("fail to watch {$folder}");
                }
            }
            // 读取变化
            stream_set_blocking($notify, 0);
            while (true) {
                try {
                    $files = inotify_read($notify);
                } catch (\Throwable $e) {
                    break;
                }
                if (!$files) {
                    sleep($this->delay);
                    continue;
                }
                $fileChange   = false;
                $folderChange = false;
                foreach ($files as $file) {
                    $filename = $file['name'];
                    if ($file['mask'] == 1073742080) {
                        $folderChange = true;
                    }
                    $slice = explode('.', $filename);
                    $ext   = array_pop($slice);
                    $ext   = ".{$ext}";
                    if (in_array($ext, $this->ext)) {
                        $fileChange = true;
                    }
                }
                if ($fileChange || $folderChange) {
                    if ($fileChange) {
                        $log->info("notify: file changes");
                    }
                    if ($folderChange) {
                        $log->info("notify: directory changes");
                    }
                    $this->executor->restart();
                    if ($folderChange) {
                        $this->start(); // 重启
                        break;
                    }
                }
                sleep($this->delay);
            }
        });
    }

    /**
     * 停止
     */
    public function stop()
    {
        $this->log->info("monitor stop");
        $this->notify and fclose($this->notify);
    }

}
