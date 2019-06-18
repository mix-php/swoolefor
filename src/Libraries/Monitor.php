<?php

namespace Cli\Libraries;

use Mix\Core\Bean\AbstractObject;
use Mix\Log\Logger;

/**
 * Class Monitor
 * @package Cli\Libraries
 */
class Monitor extends AbstractObject
{

    /**
     * @var string
     */
    public $dir;

    /**
     * @var int
     */
    public $interval;

    /**
     * @var Executor
     */
    public $executor;

    /**
     * @var resource
     */
    protected $_notify;

    /**
     * @var bool
     */
    protected $_quit = false;

    /**
     * 通过命令获取观察目录
     * @param $cmd
     * @return string
     */
    public static function dir($cmd)
    {
        $slice = explode(' ', $cmd);
        $file  = array_shift($slice);
        $dir   = dirname($file);
        if (basename($file) == 'bin') {
            $dir = dirname($dir);
        }
        return $dir;
    }

    /**
     * 启动
     */
    public function start()
    {
        xgo(function () {
            $logger = Logger::make(app()->get("log"));
            // 监听全部目录
            $folders       = static::folders($this->dir);
            $this->_notify = $notify = inotify_init();
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
                    var_dump($e->getMessage());
                    break;
                }
                if (!$files) {
                    sleep($this->interval);
                    continue;
                }
                $fileChange   = false;
                $folderChange = false;
                foreach ($files as $file) {
                    $filename = $file['name'];
                    if ($file['mask'] == 1073742080) {
                        $folderChange = true;
                    }
                    if (substr($filename, -4, 4) == '.php') {
                        $fileChange = true;
                    }
                }
                if ($fileChange) {
                    $logger->info("notify: file changes, restart 'Executor'");
                    $this->executor->restart();
                }
                if ($folderChange) {
                    $logger->info("notify: directory changes, restart 'Executor', restart 'Watcher'");
                    $this->executor->restart();
                    $this->start(); // 重启
                    break;
                }
                sleep($this->interval);
            }
        });
    }

    /**
     * 停止
     */
    public function stop()
    {
        fclose($this->_notify);
    }

    /**
     * 获取全部文件夹
     * @param $dir
     * @return array
     */
    protected static function folders($path)
    {
        $dh = opendir($path);
        if (!$dh) {
            return [];
        }
        $dirs   = [];
        $dirs[] = $path;
        while (false !== ($file = readdir($dh))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $full = $path . '/' . $file;
            if (is_dir($full)) {
                if (substr($file, 0, 1) == '.') {
                    continue;
                }
                $dirs[] = $full;
                $dirs   = array_merge($dirs, static::folders($full));
            }
        }
        closedir($dh);
        return $dirs;
    }

}
