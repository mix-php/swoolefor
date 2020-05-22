<?php

namespace App\Helper;

/**
 * Class MonitorHelper
 * @package App\Helper
 * @author liu,jian <coder.keda@gmail.com>
 */
class MonitorHelper
{

    /**
     * 构建扩展名数组
     * @param $ext
     * @return array
     */
    public static function ext($ext)
    {
        $slice = explode(',', $ext);
        $data  = [];
        foreach ($slice as $key => $value) {
            if (substr($value, 0, 1) != '.') {
                $data[] = ".{$value}";
            }
        }
        return $data;
    }

    /**
     * 通过命令获取观察目录
     * @param $cmd
     * @return string
     */
    public static function dir($cmd)
    {
        $slice = explode(' ', $cmd);
        array_shift($slice);
        // 循环获取文件
        while (true) {
            $file = array_shift($slice);
            if (is_file($file)) {
                break;
            }
            if (empty($slice)){
                return '';
            }
        }
        $dir = dirname($file);
        if (basename($dir) == 'bin') {
            $dir = dirname($dir);
        }
        if ($dir == '\\' || $dir == '.') {
            return '';
        }
        return $dir;
    }

    /**
     * 获取路径内的全部文件夹
     * @param $dir
     * @return array
     */
    public static function folders($path)
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

    /**
     * 获取路径内的全部指定扩展名文件
     * @param $path
     * @param array $ext
     * @return array
     */
    public static function files($path, array $ext = [])
    {
        $dh = opendir($path);
        if (!$dh) {
            return [];
        }
        $files = [];
        while (false !== ($file = readdir($dh))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $full = $path . '/' . $file;
            if (is_dir($full)) {
                if (substr($file, 0, 1) == '.') {
                    continue;
                }
                $files = array_merge($files, static::files($full, $ext));
            }
            if (is_file($full)) {
                foreach ($ext as $value) {
                    if (strpos($full, $value) !== false) {
                        $files[] = $full;
                        break;
                    }
                }
            }
        }
        closedir($dh);
        return $files;
    }

}