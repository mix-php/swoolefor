## SwooleFor

监控你的 Swoole 程序文件变化并自动重启服务器 - 适用于开发

Monitor for any changes in your swoole application and automatically restart the server - perfect for development 

## 依赖 (Extension)

- [ext-swoole >= v4.4](https://github.com/swoole/swoole-src/)
- [ext-inotify](http://pecl.php.net/package/inotify)

## 下载 (Download)

- [swoolefor v1.0.1](https://github.com/mix-php/swoolefor/releases/download/v1.0.1/swoolefor.phar)

## 使用 (Usage)

执行命令：

```
php swoolefor.phar run -c "php script.php arg..."
```

如果 `disable_functions` 禁用了 `proc_open` 方法，按如下方法执行：

```
php -d disable_functions='' swoolefor.phar run -c "php script.php arg..."
```

启动成功：

```
   _____                     __     ______          
  / ___/      ______  ____  / /__  / ____/___  _____
  \__ \ | /| / / __ \/ __ \/ / _ \/ /_  / __ \/ ___/
 ___/ / |/ |/ / /_/ / /_/ / /  __/ __/ / /_/ / /    
/____/|__/|__/\____/\____/_/\___/_/    \____/_/  Version: 1.0.1

[info] 2019-06-18 17:34:32 <25699> [message] fork process, pid: 25700, cmd: [php /data/bin/mix-httpd start -c /data/applications/http/config/httpd.php]
[info] 2019-06-18 17:34:32 <25699> [message] watch directory: /data
[info] 2019-06-18 17:34:32 <25699> [message] processing interval: 3s
```

全部命令参数：

```
php swoolefor.phar run --help
```

- `-c, --cmd` Swoole application or other script start command
- `-d, --daemon` Run in the background
- `--watch-dir` Watch code file directory
- `--interval` File change processing interval (seconds)
- `--stop-signal` Program kill signal
- `--stop-wait` Force kill timeout (seconds)

支持全部流行的 Swoole 框架：

- Swoft: `php swoolefor.phar run -c "php bin/swoft http:start"`
- EasySwoole: `php swoolefor.phar run -c "php easyswoole start"`
- MixPHP: `php swoolefor.phar run -c "php /data/bin/mix-httpd start -c /data/applications/http/config/httpd.php"`


## License

Apache License Version 2.0, http://www.apache.org/licenses/
