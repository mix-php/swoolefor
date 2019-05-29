## Mix InotifyCMD

监控文件系统变化，通过设置的命令自动重启服务器，可用于修改代码后自动重启各种 Swoole 常驻服务器 (仅限开发阶段使用)

## 依赖

- [inotify](http://pecl.php.net/package/inotify)

## 下载

- [mix-inotifycmd v1.0.2](https://github.com/mix-php/mix-inotifycmd/releases/download/v1.0.2/mix-inotifycmd.phar)
- [mix-inotifycmd v1.0.1](https://github.com/mix-php/mix-inotifycmd/releases/download/v1.0.1/mix-inotifycmd.phar)

## 使用

查看帮助

```
C:\works\projects>php mix-inotifycmd.phar
Usage: mix-inotifycmd.phar [OPTIONS] COMMAND [SUBCOMMAND] [arg...]

Options:
  -h, --help    Print usage.
  -v, --version Print version information.

Commands:
  monitor       Monitor code changes and automatically restart the server

Run 'bootstrap.php COMMAND [SUBCOMMAND] --help' for more information on a command.

Developed with Mix PHP framework. (mixphp.cn)
```

查看 `monitor` 命令的帮助

```
C:\works\projects>php mix-inotifycmd.phar monitor -h
Usage: mix-inotifycmd.phar monitor [arg...]

Options:
  -d, --daemon  Run in the background
  --dir         File directory path to monitor code changes
  --cmd         Command to automatically restart the server

Developed with Mix PHP framework. (mixphp.cn)
```

前台启动：

```
php mix-inotifycmd.phar monitor --dir=/data --cmd="php /data/bin/mix-httpd restart -c /data/applications/http/config/httpd.php"
```

也可以后台启动：

```
php mix-inotifycmd.phar monitor --dir=/data --cmd="php /data/bin/mix-httpd restart -c /data/applications/http/config/httpd.php" -d
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
