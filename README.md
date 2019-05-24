## Mix InotifyCMD

监控文件系统变化，通过设置的命令自动重启服务器，可用于修改代码后自动重启各种 Swoole 常驻服务器 (仅限开发阶段使用)


## 下载

- [mix-inotifycmd v1.0.1](https://github.com/mix-php/mix-inotifycmd/releases/download/v1.0.1/mix-inotifycmd.phar)

## 使用

```
php mix-autorestart.phar monitor --dir=/data --cmd="php /data/bin/mix-httpd restart -c /data/applications/http/config/httpd.php > /dev/null &"
```

注意：命令后面必须要加 **'&'** 让命令不阻塞监控程序的执行

## License

Apache License Version 2.0, http://www.apache.org/licenses/
