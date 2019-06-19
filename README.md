## SwooleFor

监控你的 Swoole 程序文件变化并自动重启服务器 - 适用于开发

Monitor for any changes in your swoole application and automatically restart the server - perfect for development 

## 依赖 (Extension)

- [ext-swoole >= v4.4](https://github.com/swoole/swoole-src/)
- [ext-inotify](http://pecl.php.net/package/inotify)

## 下载 (Download)

- [swoolefor.phar v1.0.2](https://github.com/mix-php/swoolefor/releases/download/v1.0.2/swoolefor.phar)
- [swoolefor.phar v1.0.1](https://github.com/mix-php/swoolefor/releases/download/v1.0.1/swoolefor.phar)

## 使用 (Usage)

执行脚本命令：

```
php swoolefor.phar run --cmd="php app.php arg..."
```

如果 `disable_functions` 禁用了 `proc_open` 方法，按如下方法执行：

```
php -d disable_functions='' swoolefor.phar run --cmd="php app.php arg..."
```

启动成功：

```
   _____                     __     ______          
  / ___/      ______  ____  / /__  / ____/___  _____
  \__ \ | /| / / __ \/ __ \/ / _ \/ /_  / __ \/ ___/
 ___/ / |/ |/ / /_/ / /_/ / /  __/ __/ / /_/ / /    
/____/|__/|__/\____/\____/_/\___/_/    \____/_/  Version: 1.0.2

[info] 2019-06-19 12:33:46 <28397> [message] executor start
[info] 2019-06-19 12:33:46 <28397> [message] cmd: [php /data/bin/mix-httpd start -c /data/applications/http/config/httpd.php]
[info] 2019-06-19 12:33:46 <28397> [message] signal: 9
[info] 2019-06-19 12:33:46 <28397> [message] fork process, pid: 28398
[info] 2019-06-19 12:33:46 <28397> [message] monitor start
[info] 2019-06-19 12:33:46 <28397> [message] watch: /tmp
[info] 2019-06-19 12:33:46 <28397> [message] delay: 1s
[info] 2019-06-19 12:33:46 <28397> [message] ext: .php,.json
```

## 全部命令参数

```
php swoolefor.phar run --help
```

- `-c, --cmd`	Swoole application or other script start command
- `-d, --daemon`	Run in the background
- `--watch`	Watch code file directory
- `--delay`	File change delay processing (seconds)
- `--ext`		Monitor only changes to these extensions
- `--signal`	Send this signal to the process


## 执行脚本命令

`--cmd` 内部可以是任何命令，必须为绝对路径，必须为守护程序 (非守护程序会导致不断fork进程)

```
php swoolefor.phar run --cmd="php app.php"
```

也可使用短参数

```
php swoolefor.phar run -c "php app.php"
```

## 执行非 PHP 脚本

- node

```
php swoolefor.phar run --cmd="node app.js"
```

- python

```
php swoolefor.phar run --cmd="python app.py"
```

## 在后台执行

SwooleFor 本身可以在后台执行，这样可以脱离终端，增加 `--daemon` 即可

```
php swoolefor.phar run --cmd="node app.js" --daemon
```

也可使用短参数

```
php swoolefor.phar run --cmd="node app.js" -d
```

## 指定监控目录

`--watch` 的默认值为 `--cmd` 参数中脚本的当前目录，如果脚本是在 `bin` 目录中则会监控上一级的目录。

```
// 会自动监控 /data 目录
php swoolefor.phar run --cmd="php /data/bin/app.php"
```

指定监控其他目录

```
php swoolefor.phar run --cmd="php app.php" --watch=/tmp
```

## 推迟执行重启

当更新了很多文件时，我们并不希望程序一直频繁的重启，所以我们需要设置一个延迟执行重启的时间，只有在达到设置的时间才执行重启操作。

`--delay` 默认为 `3s`

```
php swoolefor.phar run --cmd="php app.php" --delay=5
```

## 指定观察的扩展名

`--ext` 默认为 `php,json`，当需要观察其他扩展名时可配置

```
php swoolefor.phar run --cmd="php app.php" --ext=php,json,ini
```

## 重启时发送的信号


程序重启时终止进程是通过给进程发送信号完成的，当我们需要指定信号时


`--signal` 默认为 `15`

```
php swoolefor.phar run --cmd="php app.php" --signal=1
```

常用的信号表

|  信号 |  值 |
| --- | --- |
|  SIGTERM |  15 |
|  SIGKILL |  9 |
|  SIGHUP |  1 |
|  ... |  ... |

## 支持流行的 Swoole 框架

- MixPHP: 

```
php swoolefor.phar run -c "php /data/bin/mix-httpd start -c /data/applications/http/config/httpd.php"
```

- Swoft:

```
php swoolefor.phar run -c "php /data/bin/swoft http:start"
```

- EasySwoole: 

```
php swoolefor.phar run -c "php /data/bin/easyswoole start"
```

- laravel-s

```
php swoolefor.phar run -c "php /data/bin/laravels start"
```

## License

Apache License Version 2.0, http://www.apache.org/licenses/
