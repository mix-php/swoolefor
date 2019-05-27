<?php

namespace Cli\Commands;

use Mix\Console\CommandLine\Flag;
use Mix\Helper\ProcessHelper;

/**
 * Class MonitorCommand
 * @package Cli\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class MonitorCommand
{

    /**
     * @var string
     */
    public $directory;

    /**
     * @var string
     */
    public $cmd;

    /**
     * @var int
     */
    public $interval = 1;

    /**
     * 主函数
     */
    public function main()
    {
        // 获取参数
        $directory = Flag::string('dir', '');
        if ($directory == '') {
            println("Option '--dir' required.");
            exit;
        }
        $cmd = Flag::string('cmd', '');
        if ($cmd == '') {
            println("Option '--cmd' required.");
            exit;
        }
        $daemon = Flag::bool(['d', 'daemon'], false);
        if ($daemon) {
            ProcessHelper::daemon();
        }
        // Swoole 判断
        if (!extension_loaded('inotify')) {
            println('Need inotify extension to run, install: http://pecl.php.net/package/inotify');
            exit;
        }
        // 赋值
        $this->directory = $directory;
        $this->cmd       = trim($cmd);
        // 命令不阻塞处理
        if (substr($this->cmd, -1, 1) != '&') {
            $this->cmd = "{$this->cmd} >/dev/null 2>&1 &";
        }
        // 执行
        $this->run();
    }

    /**
     * 执行
     * @return bool
     */
    protected function run()
    {
        // 监听全部目录
        $dirs   = static::getDirs($this->directory);
        $notify = inotify_init();
        foreach ($dirs as $dir) {
            $ret = inotify_add_watch($notify, $dir, IN_CLOSE_WRITE | IN_CREATE | IN_DELETE);
            if (!$ret) {
                die('fail to watch /dev/shm/inotify');
            }
        }
        // 读取变化
        stream_set_blocking($notify, 0);
        while (true) {
            $files = inotify_read($notify);
            if (!$files) {
                sleep($this->interval);
                continue;
            }
            $fileChange = false;
            $dirChange  = false;
            foreach ($files as $file) {
                $filename = $file['name'];
                if ($file['mask'] == 1073742080) {
                    $dirChange = true;
                }
                if (substr($filename, -4, 4) == '.php') {
                    $fileChange = true;
                }
            }
            if ($fileChange) {
                app()->log->info("monitored file changes, execute restart command [{$this->cmd}]");
                exec($this->cmd);
            }
            if ($dirChange) {
                app()->log->info("monitored directory changes, execute restart command [{$this->cmd}], restart monitor");
                exec($this->cmd);
                return $this->run();
            }
            sleep($this->interval);
        }
        return true;
    }

    /**
     * 获取全部目录
     * @param $dir
     * @return array
     */
    protected static function getDirs($path)
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
                $dirs   = array_merge($dirs, static::getDirs($full));
            }
        }
        closedir($dh);
        return $dirs;
    }

}
